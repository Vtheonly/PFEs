<?php
session_start();
require_once '../../../db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(["success" => false, "error" => "Unauthorized access"]);
    exit;
}

function createAttendanceSession($teacher_id, $date, $start_time, $end_time) {
    global $conn;
    
    try {
        // First check if there's already an open session for this teacher today
        $check_sql = "SELECT session_id FROM AttendanceSessions 
                     WHERE teacher_id = ? AND date = ? AND status = 'open'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $teacher_id, $date);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            return ["success" => false, "error" => "You already have an open session for today"];
        }
        
        // Create new session
        $sql = "INSERT INTO AttendanceSessions (teacher_id, date, start_time, end_time, status) 
                VALUES (?, ?, ?, ?, 'open')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $teacher_id, $date, $start_time, $end_time);
        
        if ($stmt->execute()) {
            return [
                "success" => true, 
                "session_id" => $conn->insert_id,
                "message" => "Attendance session created successfully"
            ];
        } else {
            throw new Exception("Failed to create attendance session");
        }
    } catch (Exception $e) {
        return ["success" => false, "error" => $e->getMessage()];
    }
}

function closeAttendanceSession($session_id, $teacher_id) {
    global $conn;
    
    try {
        $sql = "UPDATE AttendanceSessions 
                SET status = 'closed' 
                WHERE session_id = ? AND teacher_id = ? AND status = 'open'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $session_id, $teacher_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            return ["success" => true, "message" => "Session closed successfully"];
        } else {
            return ["success" => false, "error" => "No open session found or unauthorized"];
        }
    } catch (Exception $e) {
        return ["success" => false, "error" => $e->getMessage()];
    }
}

function getActiveSession($teacher_id) {
    global $conn;
    
    try {
        $sql = "SELECT session_id, date, start_time, end_time 
                FROM AttendanceSessions 
                WHERE teacher_id = ? AND status = 'open' 
                AND DATE(date) = CURRENT_DATE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $session = $result->fetch_assoc();
            return ["success" => true, "session" => $session];
        } else {
            return ["success" => false, "error" => "No active session found"];
        }
    } catch (Exception $e) {
        return ["success" => false, "error" => $e->getMessage()];
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            if (empty($_POST['date']) || empty($_POST['start_time']) || empty($_POST['end_time'])) {
                echo json_encode(["success" => false, "error" => "Missing required fields"]);
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
                echo json_encode(["success" => false, "error" => "Session ID is required"]);
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
            echo json_encode(["success" => false, "error" => "Invalid action"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>