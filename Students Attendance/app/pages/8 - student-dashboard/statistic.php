<?php
require_once '../../../db_connect.php';
header('Content-Type: application/json');

// Validate that user is logged in and is a student
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access'
    ]);
    exit;
}

function getStudentAttendanceStats($student_id, $days) {
    global $conn;
    
    try {
        $sql = "SELECT 
                    COUNT(CASE WHEN sar.attendance_status = 'present' THEN 1 END) as present_count,
                    COUNT(*) as total_sessions
                FROM AttendanceSessions s
                LEFT JOIN StudentAttendanceRecords sar ON s.session_id = sar.session_id 
                    AND sar.student_id = ?
                WHERE s.date >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                    AND s.status = 'closed'";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $days);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Calculate percentage
        $percentage = $row['total_sessions'] > 0 
            ? round(($row['present_count'] / $row['total_sessions']) * 100) 
            : 0;
            
        return $percentage;
        
    } catch (Exception $e) {
        error_log("Error calculating student attendance stats: " . $e->getMessage());
        return 0;
    }
}

// Get statistics for different time periods
$student_id = $_SESSION['user_id'];

$response = [
    'success' => true,
    'stats' => [
        'week' => getStudentAttendanceStats($student_id, 7),      // Last 7 days
        'month' => getStudentAttendanceStats($student_id, 30),    // Last 30 days
        'semester' => getStudentAttendanceStats($student_id, 180)  // Last 6 months (roughly a semester)
    ]
];

echo json_encode($response);
?>