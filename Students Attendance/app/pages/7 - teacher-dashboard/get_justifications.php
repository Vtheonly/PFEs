<?php
// --- START OF FILE get_justifications.php ---

// Start output buffering IMMEDIATELY
ob_start();

// Set header IMMEDIATELY
header('Content-Type: application/json');

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep errors hidden from output
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-error.log'); // Ensure this path is correct and writable

// Start session and include database connection
session_start();

// Use require_once and check for file existence
$db_connect_path = '../../../db_connect.php';
if (!file_exists($db_connect_path)) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database configuration error.']);
    ob_end_flush();
    exit;
}
require_once $db_connect_path;

// Check if DB connection was successful
if (!isset($conn) || $conn->connect_error) {
     if (ob_get_length()) ob_clean();
     $error_msg = isset($conn) ? "Database connection failed: " . $conn->connect_error : "Database connection variable not established.";
     error_log("get_justifications.php: " . $error_msg);
     echo json_encode(['success' => false, 'error' => 'Database connection error. Please try again later.']);
     ob_end_flush();
     exit;
}

// Check if teacher is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Teacher access required.']);
    ob_end_flush();
    exit;
}

$teacher_id = $_SESSION['user_id'];
$response = null; // Initialize response variable

try {
    // 1. Get the teacher's group
    $group_sql = "SELECT `group` FROM Users WHERE user_id = ? AND role = 'teacher'";
    $group_stmt = $conn->prepare($group_sql);
    if (!$group_stmt) throw new Exception("DB error preparing group fetch: " . $conn->error);

    $group_stmt->bind_param("i", $teacher_id);
    if (!$group_stmt->execute()) throw new Exception("DB error executing group fetch: " . $group_stmt->error); // Check execute

    $group_result = $group_stmt->get_result();
    $teacher_data = $group_result->fetch_assoc();
    $group_stmt->close();

    // Use a more specific check for invalid group (null or <= 0)
    if (!$teacher_data || !isset($teacher_data['group']) || $teacher_data['group'] === null || $teacher_data['group'] <= 0) {
         throw new Exception("Teacher is not assigned to a valid group.");
    }
    $teacher_group = $teacher_data['group'];


    // 2. Fetch pending justifications ONLY for students in the teacher's group
    $fetch_sql = "SELECT
                    sar.record_id,
                    u.name AS student_name,
                    s.date AS absence_date,
                    sar.justification
                  FROM StudentAttendanceRecords sar
                  JOIN Users u ON sar.student_id = u.user_id
                  JOIN AttendanceSessions s ON sar.session_id = s.session_id -- Needed for date context
                  WHERE sar.justification_status = 'pending'
                    AND u.role = 'student'
                    AND u.`group` = ?  -- Filter by teacher's group
                  ORDER BY s.date ASC, u.name ASC"; // Show oldest first

    $fetch_stmt = $conn->prepare($fetch_sql);
     if (!$fetch_stmt) throw new Exception("DB error preparing justification fetch: " . $conn->error);

    $fetch_stmt->bind_param("i", $teacher_group);
     if (!$fetch_stmt->execute()) throw new Exception("DB error executing justification fetch: " . $fetch_stmt->error); // Check execute

    $result = $fetch_stmt->get_result();

    $justifications = [];
    while ($row = $result->fetch_assoc()) {
        // Sanitize justification text and name before adding to array
         $row['justification'] = htmlspecialchars($row['justification'] ?? '', ENT_QUOTES, 'UTF-8');
         $row['student_name'] = htmlspecialchars($row['student_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $justifications[] = $row;
    }
    $fetch_stmt->close();

    // Set success response
    $response = ['success' => true, 'justifications' => $justifications];

} catch (Exception $e) {
    // Log the detailed error
    error_log("Error fetching justifications for teacher $teacher_id: " . $e->getMessage());
    // Set error response
    $response = ['success' => false, 'error' => $e->getMessage()];
    // For production, use a generic error:
    // $response = ['success' => false, 'error' => 'An error occurred while fetching justifications.'];

} finally {
     // Ensure the database connection is closed if it's still open and valid
    if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
        $conn->close();
    }
}

// --- Final Output ---
// Clean the buffer *just before* outputting the final JSON
if (ob_get_length()) ob_clean();

// Ensure $response is set before encoding
if ($response === null) {
    error_log("Response variable was null before encoding in get_justifications.php");
    $response = ['success' => false, 'error' => 'An unexpected internal server error occurred.'];
}

echo json_encode($response);

// Flush the output buffer
ob_end_flush();
exit(); // Explicitly exit

?>
