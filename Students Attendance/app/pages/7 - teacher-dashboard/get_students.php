<?php
require_once '../../../db_connect.php';
header('Content-Type: application/json');

try {
    // First find the most recent closed session
    $session_query = "SELECT session_id, date FROM AttendanceSessions 
                     WHERE status = 'closed' 
                     ORDER BY date DESC, end_time DESC 
                     LIMIT 1";
    $session_result = $conn->query($session_query);
    
    if (!$session_result->num_rows) {
        throw new Exception("No closed sessions found");
    }
    
    $last_session = $session_result->fetch_assoc();
    
    // Now get all students with their attendance data
    $query = "SELECT 
        u.user_id,
        u.name,
        COALESCE(sar.attendance_status, 'Absent') as last_status,
        (
            SELECT COUNT(*) 
            FROM StudentAttendanceRecords sar2 
            WHERE sar2.student_id = u.user_id 
            AND sar2.attendance_status = 'present'
        ) as total_present,
        (
            SELECT COUNT(*) 
            FROM StudentAttendanceRecords sar3 
            WHERE sar3.student_id = u.user_id 
            AND sar3.attendance_status = 'absent'
        ) as total_absent
    FROM Users u
    LEFT JOIN StudentAttendanceRecords sar ON 
        sar.student_id = u.user_id AND 
        sar.session_id = ?
    WHERE u.role = 'student'
    ORDER BY u.user_id";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $last_session['session_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $total_present = (int)$row['total_present'];
        $total_absent = (int)$row['total_absent'];
        $total_sessions = $total_present + $total_absent;
        
        // Calculate percentage as present/total ratio
        $final_score = $total_sessions > 0 
            ? round(($total_present / $total_sessions) * 100)
            : 0;

        $students[] = [
            'id' => $row['user_id'],
            'name' => htmlspecialchars($row['name']),
            'today_status' => ucfirst($row['last_status']) . ' (' . $last_session['date'] . ')',
            'total_present' => $total_present,
            'total_absent' => $total_absent,
            'final_score' => $final_score . '%'
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $students
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
