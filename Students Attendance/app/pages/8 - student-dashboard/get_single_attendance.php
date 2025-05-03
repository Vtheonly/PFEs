<?php

error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once '../../../db_connect.php';


header('Content-Type: application/json');




if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {

    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in as a student.']);

    exit;
}
$student_id = $_SESSION['user_id'];


if (!isset($_GET['record_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing record ID parameter.']);
    exit;
}

$record_id = filter_var($_GET['record_id'], FILTER_VALIDATE_INT);
if ($record_id === false || $record_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid record ID format.']);
    exit;
}



$record = null;

try {


    $query = "SELECT
                a.record_id,
                a.attendance_status,
                a.justification_status,
                a.justification,
                s.date,
                s.start_time,
                s.end_time
              FROM StudentAttendanceRecords a
              JOIN AttendanceSessions s ON a.session_id = s.session_id
              WHERE a.record_id = ? AND a.student_id = ?";

    $stmt = $conn->prepare($query);


    if (!$stmt) {

        error_log("DB Prepare Error in get_single_attendance: " . $conn->error);
        throw new Exception("Database query preparation failed.");
    }


    $stmt->bind_param("ii", $record_id, $student_id);


    if (!$stmt->execute()) {
        error_log("DB Execute Error in get_single_attendance: " . $stmt->error);
        throw new Exception("Database query execution failed.");
    }


    $result = $stmt->get_result();


    $record = $result->fetch_assoc();


    $stmt->close();


    $conn->close();



    if ($record) {

        echo json_encode(['success' => true, 'record' => $record]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Attendance record not found or access denied.']);
    }

    exit;

} catch (Exception $e) {

    error_log("Error fetching single attendance record $record_id for student $student_id: " . $e->getMessage());

    if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
        $conn->close();
    }


    echo json_encode(['success' => false, 'error' => 'An unexpected error occurred while fetching record details.']);
    exit;
}


?>