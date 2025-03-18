<?php
session_start();
require_once '../../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return current username
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT name FROM Users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'username' => $row['name']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'User not found']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
    }
}

$conn->close();
?>
