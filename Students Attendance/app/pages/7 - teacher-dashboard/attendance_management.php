<?php
session_start();
require_once '../../../db_connect.php'; // Ensure this path is correct

header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit;
}

function createAttendanceSession($teacher_id, $date, $start_time, $end_time) {
    global $conn;

    try {
        // --- START NEW CODE ---
        // Fetch the teacher's assigned group
        $group_sql = "SELECT `group` FROM Users WHERE user_id = ? AND role = 'teacher'";
        $group_stmt = $conn->prepare($group_sql);
        if (!$group_stmt) {
            throw new Exception("Failed to prepare group query: " . $conn->error);
        }
        $group_stmt->bind_param("i", $teacher_id);
        $group_stmt->execute();
        $group_result = $group_stmt->get_result();
        $teacher_data = $group_result->fetch_assoc();

        if (!$teacher_data || $teacher_data['group'] === null || $teacher_data['group'] == 0) { // Also check for 0 if that means unassigned
            return ["success" => false, "error" => "Teacher is not assigned to a group. Cannot create session."];
        }
        $session_group = $teacher_data['group'];
        $group_stmt->close(); // Close the statement
        // --- END NEW CODE ---


        // First check if there's already an open session for this teacher today
        // Consider if the check should be group-specific too, but usually one session per teacher per day is fine.
        $check_sql = "SELECT session_id FROM AttendanceSessions
                     WHERE teacher_id = ? AND date = ? AND status = 'open'";
        $check_stmt = $conn->prepare($check_sql);
         if (!$check_stmt) {
            throw new Exception("Failed to prepare session check query: " . $conn->error);
        }
        $check_stmt->bind_param("is", $teacher_id, $date);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $check_stmt->close(); // Close the statement
            return ["success" => false, "error" => "You already have an open attendance session for today"];
        }
        $check_stmt->close(); // Close the statement

        // Create new session - **MODIFIED INSERT**
        $sql = "INSERT INTO AttendanceSessions (teacher_id, date, start_time, end_time, status, session_group)
                VALUES (?, ?, ?, ?, 'open', ?)"; // Added session_group
        $stmt = $conn->prepare($sql);
         if (!$stmt) {
            throw new Exception("Failed to prepare session insert query: " . $conn->error);
        }
        // **MODIFIED BIND_PARAM** - added 'i' for session_group
        $stmt->bind_param("isssi", $teacher_id, $date, $start_time, $end_time, $session_group);

        if ($stmt->execute()) {
             $new_session_id = $conn->insert_id;
             $stmt->close(); // Close the statement
            return [
                "success" => true,
                "session_id" => $new_session_id,
                "message" => "Attendance session created successfully for Group " . $session_group // Added group info
            ];
        } else {
            $error_msg = $stmt->error;
            $stmt->close(); // Close the statement
            throw new Exception("Failed to create attendance session: " . $error_msg);
        }
    } catch (Exception $e) {
        // Log the detailed error for debugging
        error_log("Error in createAttendanceSession for teacher $teacher_id: " . $e->getMessage());
        // Return a more specific or generic error to the user
        return ["success" => false, "error" => $e->getMessage()]; // Or "Internal server error creating session."
    }
}


function closeAttendanceSession($session_id, $teacher_id) {
    global $conn;

    try {
        // Start a transaction to ensure all operations succeed or fail together
        $conn->begin_transaction();

        // First, update the session status to closed
        $sql_close = "UPDATE AttendanceSessions
                SET status = 'closed'
                WHERE session_id = ? AND teacher_id = ? AND status = 'open'";
        $stmt_close = $conn->prepare($sql_close);
        if (!$stmt_close) {
             $conn->rollback();
             throw new Exception("DB Error preparing session close: " . $conn->error);
        }
        $stmt_close->bind_param("ii", $session_id, $teacher_id);
        $stmt_close->execute();

        // Check if the session was found and updated
        $affected_rows = $stmt_close->affected_rows;
        $stmt_close->close(); // Close statement early

        if ($affected_rows <= 0) {
            $conn->rollback();
            // Check if the session exists but is already closed or belongs to another teacher
            $check_exists_sql = "SELECT status, teacher_id FROM AttendanceSessions WHERE session_id = ?";
            $check_exists_stmt = $conn->prepare($check_exists_sql);
            $check_exists_stmt->bind_param("i", $session_id);
            $check_exists_stmt->execute();
            $check_exists_result = $check_exists_stmt->get_result();
            if ($check_exists_result->num_rows > 0) {
                $existing_session = $check_exists_result->fetch_assoc();
                if ($existing_session['teacher_id'] != $teacher_id) {
                    return ["success" => false, "error" => "Unauthorized: You did not create this session."];
                } elseif ($existing_session['status'] == 'closed') {
                    return ["success" => false, "error" => "Session is already closed."];
                }
            }
            return ["success" => false, "error" => "No open session found with that ID or unauthorized"];
        }

        // Get the group for the session to mark only students of that group absent
        $group_sql = "SELECT session_group FROM AttendanceSessions WHERE session_id = ?";
        $group_stmt = $conn->prepare($group_sql);
         if (!$group_stmt) {
             $conn->rollback();
             throw new Exception("DB Error preparing group fetch: " . $conn->error);
         }
        $group_stmt->bind_param("i", $session_id);
        $group_stmt->execute();
        $group_result = $group_stmt->get_result();
        $session_data = $group_result->fetch_assoc();
        $session_group = $session_data['session_group'];
        $group_stmt->close();

        if (!$session_group) {
             $conn->rollback();
             throw new Exception("Could not determine group for the session.");
        }


        // Now mark all students IN THAT GROUP who haven't marked attendance as absent
        $sql_absent = "INSERT INTO StudentAttendanceRecords (session_id, student_id, attendance_status)
                SELECT
                    ?,
                    u.user_id,
                    'absent'
                FROM
                    Users u
                WHERE
                    u.role = 'student'
                    AND u.`group` = ?  -- Only students from the session's group
                    AND NOT EXISTS (
                        SELECT 1
                        FROM StudentAttendanceRecords sar
                        WHERE sar.session_id = ?
                        AND sar.student_id = u.user_id
                    )";

        $stmt_absent = $conn->prepare($sql_absent);
         if (!$stmt_absent) {
             $conn->rollback();
             throw new Exception("DB Error preparing absent marking: " . $conn->error);
         }
        // Bind session_id twice, and the group number
        $stmt_absent->bind_param("iii", $session_id, $session_group, $session_id);
        $stmt_absent->execute();

        // Get the number of students marked as absent
        $absent_count = $stmt_absent->affected_rows;
        $stmt_absent->close();

        // Commit the transaction
        $conn->commit();

        return [
            "success" => true,
            "message" => "Session closed successfully. $absent_count students from Group $session_group marked as absent."
        ];

    } catch (Exception $e) {
        // Roll back the transaction in case of an error
        $conn->rollback();
        error_log("Error closing session $session_id by teacher $teacher_id: " . $e->getMessage());
        return ["success" => false, "error" => $e->getMessage()]; // Or "Failed to close session."
    }
}


function getActiveSession($teacher_id) {
    global $conn;

    try {
        // Fetches the *most recent* open session for the teacher on the current date
        $sql = "SELECT session_id, date, start_time, end_time, session_group
                FROM AttendanceSessions
                WHERE teacher_id = ? AND status = 'open'
                AND DATE(date) = CURRENT_DATE
                ORDER BY start_time DESC
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("DB Error preparing active session fetch: " . $conn->error);
        }
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $session = $result->fetch_assoc();
            $stmt->close();
            return ["success" => true, "session" => $session];
        } else {
            $stmt->close();
            // It's not really an error if none is found, just a state.
            return ["success" => false, "message" => "No active session found for today"];
        }
    } catch (Exception $e) {
        error_log("Error getting active session for teacher $teacher_id: " . $e->getMessage());
        return ["success" => false, "error" => $e->getMessage()]; // Or "Failed to check for active session."
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            if (empty($_POST['date']) || empty($_POST['start_time']) || empty($_POST['end_time'])) {
                echo json_encode(["success" => false, "error" => "Missing required fields (date, start time, end time)"]);
                exit;
            }

            $result = createAttendanceSession(
                $_SESSION['user_id'],
                $_POST['date'],
                $_POST['start_time'],
                $_POST['end_time']
            );
            echo json_encode($result);
            break;

        case 'close':
            if (empty($_POST['session_id'])) {
                echo json_encode(["success" => false, "error" => "Session ID is required to close"]);
                exit;
            }

            $result = closeAttendanceSession($_POST['session_id'], $_SESSION['user_id']);
            echo json_encode($result);
            break;

        case 'get_active':
            $result = getActiveSession($_SESSION['user_id']);
            echo json_encode($result);
            break;

        default:
            echo json_encode(["success" => false, "error" => "Invalid action specified"]);
    }
} else {
    // Only allow POST requests for actions
    echo json_encode(["success" => false, "error" => "Invalid request method (POST required)"]);
}

// Close the connection at the very end if it's still open
if (isset($conn) && $conn instanceof mysqli) {
     $conn->close();
}
?>