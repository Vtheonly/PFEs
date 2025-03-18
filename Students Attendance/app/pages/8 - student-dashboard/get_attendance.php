<?php
session_start();
require_once '../../../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$student_id = $_SESSION['user_id'];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5; // Default to 5 records

$query = "SELECT 
    a.timestamp,
    a.attendance_status,
    s.date,
    s.start_time,
    s.end_time
FROM StudentAttendanceRecords a
JOIN AttendanceSessions s ON a.session_id = s.session_id
WHERE a.student_id = ?
ORDER BY s.date DESC, s.start_time DESC
LIMIT ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $student_id, $limit);
$stmt->execute();
$result = $stmt->get_result();

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = [
        'date' => $row['date'],
        'time' => $row['start_time'],
        'status' => $row['attendance_status']
    ];
}

echo json_encode(['success' => true, 'records' => $records]);
$conn->close();
