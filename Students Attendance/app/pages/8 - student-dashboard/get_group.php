<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

header('Content-Type: application/json');

require_once '../../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return current group
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        try {
            $stmt = $conn->prepare("SELECT `group` FROM Users WHERE user_id = ? AND role = 'student'");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'group' => $row['group']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Student not found or invalid user type']);
        }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
    }
}

$conn->close();
?>