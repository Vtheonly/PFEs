<?php
require_once '../../../db_connect.php';
header('Content-Type: application/json');

// Validate that user is logged in and is a teacher
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access'
    ]);
    exit;
}

function getAttendanceStats($teacher_id, $days) {
    global $conn;
    
    try {
        $sql = "SELECT 
                    COUNT(DISTINCT CASE WHEN sar.attendance_status = 'present' THEN sar.student_id END) as present_count,
                    COUNT(DISTINCT sar.student_id) as total_students,
                    DATE(s.date) as attendance_date
                FROM AttendanceSessions s
                LEFT JOIN StudentAttendanceRecords sar ON s.session_id = sar.session_id
                WHERE s.teacher_id = ?
                    AND s.date >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
                    AND s.status = 'closed'
                GROUP BY DATE(s.date)";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $teacher_id, $days);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $total_present = 0;
        $total_attendance = 0;
        
        while ($row = $result->fetch_assoc()) {
            $total_present += $row['present_count'];
            $total_attendance += $row['total_students'];
        }
        
        // Calculate percentage
        $percentage = $total_attendance > 0 
            ? round(($total_present / $total_attendance) * 100) 
            : 0;
            
        return $percentage;
        
    } catch (Exception $e) {
        error_log("Error calculating attendance stats: " . $e->getMessage());
        return 0;
    }
}

// Get statistics for different time periods
$teacher_id = $_SESSION['user_id'];

$response = [
    'success' => true,
    'stats' => [
        'week' => getAttendanceStats($teacher_id, 7),      // Last 7 days
        'month' => getAttendanceStats($teacher_id, 30),    // Last 30 days
        'semester' => getAttendanceStats($teacher_id, 180)  // Last 6 months (roughly a semester)
    ]
];

echo json_encode($response);
?>