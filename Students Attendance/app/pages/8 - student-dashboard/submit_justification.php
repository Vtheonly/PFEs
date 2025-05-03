<?php
// --- START OF FILE submit_justification.php ---

// Start output buffering IMMEDIATELY
ob_start();

// Set header IMMEDIATELY
header('Content-Type: application/json');

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep errors hidden from output
ini_set('log_errors', 1);
// Ensure the log path is writable by the web server
ini_set('error_log', '/tmp/php-error.log'); // Make sure this path is correct and writable

// Start session and include database connection
session_start();

// Use require_once and check for file existence for robustness
$db_connect_path = '../../../db_connect.php';
if (!file_exists($db_connect_path)) {
    // Clean buffer before outputting error
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database configuration error.']);
    ob_end_flush();
    exit;
}
require_once $db_connect_path;

// Check if DB connection was successful (assuming $conn is set in db_connect.php)
if (!isset($conn) || $conn->connect_error) {
     if (ob_get_length()) ob_clean();
     // Provide a generic message if $conn->connect_error isn't set but $conn is missing
     $error_msg = isset($conn) ? "Database connection failed: " . $conn->connect_error : "Database connection variable not established.";
     error_log($error_msg); // Log the specific error
     echo json_encode(['success' => false, 'error' => 'Database connection error. Please try again later.']);
     ob_end_flush();
     exit;
}


// Check user authentication
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Please log in as a student.']);
    ob_end_flush();
    exit;
}
$student_id = $_SESSION['user_id'];


// Validate request method and input
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['record_id']) || !isset($_POST['justification_text'])) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid request. Missing data.']);
    ob_end_flush();
    exit;
}

$record_id = filter_var($_POST['record_id'], FILTER_VALIDATE_INT);
$justification_text = trim($_POST['justification_text']); // Trim whitespace

// Validate inputs
if ($record_id === false || $record_id <= 0) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid record ID.']);
    ob_end_flush();
    exit;
}
if (empty($justification_text)) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'error' => 'Justification text cannot be empty.']);
    ob_end_flush();
    exit;
}


// --- Database Interaction ---
$response = null; // Variable to hold the final response
try {
    $conn->begin_transaction();

    // Check if the record exists, belongs to the student, and is eligible for justification
    $check_sql = "SELECT student_id, attendance_status, justification_status
                  FROM StudentAttendanceRecords
                  WHERE record_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
         throw new Exception("Database error (prepare check): " . $conn->error);
    }
    $check_stmt->bind_param("i", $record_id);
    if (!$check_stmt->execute()) { // Check execute success
        throw new Exception("Database error (execute check): " . $check_stmt->error);
    }
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        $check_stmt->close();
        throw new Exception("Attendance record not found.");
    }

    $record = $result->fetch_assoc();
    $check_stmt->close();

    // Authorization and Logic Checks
    if ($record['student_id'] != $student_id) {
        throw new Exception("Unauthorized: You cannot justify this record.");
    }
    if ($record['attendance_status'] !== 'absent') {
        throw new Exception("Cannot justify: This record is not marked as absent.");
    }
    if ($record['justification_status'] !== null) {
        throw new Exception("Cannot justify: A justification has already been submitted or processed.");
    }

    // Update the record
    // Added WHERE conditions again for safety (prevents race conditions if status changed between check and update)
    $update_sql = "UPDATE StudentAttendanceRecords
                   SET justification = ?, justification_status = 'pending'
                   WHERE record_id = ? AND student_id = ? AND attendance_status = 'absent' AND justification_status IS NULL";
    $update_stmt = $conn->prepare($update_sql);
     if (!$update_stmt) {
         throw new Exception("Database error (prepare update): " . $conn->error);
    }
    // Use the sanitized justification text
    $update_stmt->bind_param("sii", $justification_text, $record_id, $student_id);

    if (!$update_stmt->execute()) { // Check execute success
        throw new Exception("Failed to submit justification (execute update): " . $update_stmt->error);
    }

    // Check if the update actually affected a row
    if ($update_stmt->affected_rows === 0) {
        // This might happen if the record's status changed between the SELECT and UPDATE (race condition)
         throw new Exception("Failed to update the record. It might have been updated concurrently or the conditions changed.");
    }
    $update_stmt->close();

    // If all successful, commit the transaction
    $conn->commit();

    // Set success response
    $response = ['success' => true, 'message' => 'Justification submitted successfully.'];

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    // Log the detailed error
    error_log("Justification submission error for student $student_id, record $record_id: " . $e->getMessage());
    // Set error response
    $response = ['success' => false, 'error' => $e->getMessage()]; // Send specific error back for now
    // For production, you might want a generic error:
    // $response = ['success' => false, 'error' => 'An error occurred while submitting justification.'];

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
    // This should ideally not happen if try/catch is comprehensive
    error_log("Response variable was null before encoding in submit_justification.php");
    $response = ['success' => false, 'error' => 'An unexpected internal server error occurred.'];
}

echo json_encode($response);

// Flush the output buffer
ob_end_flush();
exit(); // Explicitly exit

?>
// --- END OF FILE submit_justification.php ---