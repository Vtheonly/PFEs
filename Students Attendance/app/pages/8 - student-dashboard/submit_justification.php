<?php

ob_start();

header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-error.log');

session_start();

$db_connect_path = '../../../db_connect.php';
if (!file_exists($db_connect_path)) {
    if (ob_get_length())
        ob_clean();
    echo json_encode(['success' => false, 'error' => 'Database configuration error.']);
    ob_end_flush();
    exit;
}
require_once $db_connect_path;

if (!isset($conn) || $conn->connect_error) {
    if (ob_get_length())
        ob_clean();
    $error_msg = isset($conn) ? "Database connection failed: " . $conn->connect_error : "Database connection variable not established.";
    error_log($error_msg);
    echo json_encode(['success' => false, 'error' => 'Database connection error. Please try again later.']);
    ob_end_flush();
    exit;
}


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    if (ob_get_length())
        ob_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Please log in as a student.']);
    ob_end_flush();
    exit;
}
$student_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['record_id']) || !isset($_POST['justification_text'])) {
    if (ob_get_length())
        ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid request. Missing data.']);
    ob_end_flush();
    exit;
}

$record_id = filter_var($_POST['record_id'], FILTER_VALIDATE_INT);
$justification_text = trim($_POST['justification_text']);

if ($record_id === false || $record_id <= 0) {
    if (ob_get_length())
        ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid record ID.']);
    ob_end_flush();
    exit;
}
if (empty($justification_text)) {
    if (ob_get_length())
        ob_clean();
    echo json_encode(['success' => false, 'error' => 'Justification text cannot be empty.']);
    ob_end_flush();
    exit;
}


$response = null;
try {
    $conn->begin_transaction();

    $check_sql = "SELECT student_id, attendance_status, justification_status
                  FROM StudentAttendanceRecords
                  WHERE record_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Database error (prepare check): " . $conn->error);
    }
    $check_stmt->bind_param("i", $record_id);
    if (!$check_stmt->execute()) {
        throw new Exception("Database error (execute check): " . $check_stmt->error);
    }
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        $check_stmt->close();
        throw new Exception("Attendance record not found.");
    }

    $record = $result->fetch_assoc();
    $check_stmt->close();

    if ($record['student_id'] != $student_id) {
        throw new Exception("Unauthorized: You cannot justify this record.");
    }
    if ($record['attendance_status'] !== 'absent') {
        throw new Exception("Cannot justify: This record is not marked as absent.");
    }
    if ($record['justification_status'] !== null) {
        throw new Exception("Cannot justify: A justification has already been submitted or processed.");
    }

    $update_sql = "UPDATE StudentAttendanceRecords
                   SET justification = ?, justification_status = 'pending'
                   WHERE record_id = ? AND student_id = ? AND attendance_status = 'absent' AND justification_status IS NULL";
    $update_stmt = $conn->prepare($update_sql);
    if (!$update_stmt) {
        throw new Exception("Database error (prepare update): " . $conn->error);
    }
    $update_stmt->bind_param("sii", $justification_text, $record_id, $student_id);

    if (!$update_stmt->execute()) {
        throw new Exception("Failed to submit justification (execute update): " . $update_stmt->error);
    }

    if ($update_stmt->affected_rows === 0) {
        throw new Exception("Failed to update the record. It might have been updated concurrently or the conditions changed.");
    }
    $update_stmt->close();

    $conn->commit();

    $response = ['success' => true, 'message' => 'Justification submitted successfully.'];

} catch (Exception $e) {
    $conn->rollback();
    error_log("Justification submission error for student $student_id, record $record_id: " . $e->getMessage());
    $response = ['success' => false, 'error' => $e->getMessage()];

} finally {
    if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
        $conn->close();
    }
}

if (ob_get_length())
    ob_clean();

if ($response === null) {
    error_log("Response variable was null before encoding in submit_justification.php");
    $response = ['success' => false, 'error' => 'An unexpected internal server error occurred.'];
}

echo json_encode($response);

ob_end_flush();
exit();

?>