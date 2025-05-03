<?php
session_start();
require_once '../../../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit;
}

function createAttendanceSession($teacher_id, $date, $start_time, $end_time) {
    global $conn;

    try {
        $group_sql = "SELECT `group` FROM Users WHERE user_id = ? AND role = 'teacher'";
        $group_stmt = $conn->prepare($group_sql);
        if (!$group_stmt) {
            throw new Exception("Failed to prepare group query: " . $conn->error);
        }
        $group_stmt->bind_param("i", $teacher_id);
        $group_stmt->execute();
        $group_result = $group_stmt->get_result();
        $teacher_data = $group_result->fetch_assoc();

        if (!$teacher_data || $teacher_data['group'] === null || $teacher_data['group'] == 0) {
            return ["success" => false, "error" => "Teacher is not assigned to a group. Cannot create session."];
        }
        $session_group = $teacher_data['group'];
        $group_stmt->close();


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
            $check_stmt->close();
            return ["success" => false, "error" => "You already have an open attendance session for today"];
        }
        $check_stmt->close();

        $sql = "INSERT INTO AttendanceSessions (teacher_id, date, start_time, end_time, status, session_group)
                VALUES (?, ?, ?, ?, 'open', ?)";
        $stmt = $conn->prepare($sql);
         if (!$stmt) {
            throw new Exception("Failed to prepare session insert query: " . $conn->error);
        }
        $stmt->bind_param("isssi", $teacher_id, $date, $start_time, $end_time, $session_group);

        if ($stmt->execute()) {
             $new_session_id = $conn->insert_id;
             $stmt->close();
            return [
                "success" => true,
                "session_id" => $new_session_id,
                "message" => "Attendance session created successfully for Group " . $session_group
            ];
        } else {
            $error_msg = $stmt->error;
            $stmt->close();
            throw new Exception("Failed to create attendance session: " . $error_msg);
        }
    } catch (Exception $e) {
        error_log("Error in createAttendanceSession for teacher $teacher_id: " . $e->getMessage());
        return ["success" => false, "error" => $e->getMessage()];
    }
}


function closeAttendanceSession($session_id, $teacher_id) {
    global $conn;

    try {
        $conn->begin_transaction();

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

        $affected_rows = $stmt_close->affected_rows;
        $stmt_close->close();

        if ($affected_rows <= 0) {
            $conn->rollback();
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
        return ["success" => false, "error" => $e->getMessage()];
    }
}

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
    echo json_encode(["success" => false, "error" => "Invalid request method (POST required)"]);
}

if (isset($conn) && $conn instanceof mysqli) {
     $conn->close();
}
?>