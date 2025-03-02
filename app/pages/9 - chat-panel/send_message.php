<?php
session_start();
require_once '../../../db_connect.php';

$response = array();

if (!isset($_SESSION['user_id'])) {
    $response['success'] = false;
    $response['error'] = 'Not logged in';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['receiver_id']) && isset($data['message'])) {
        $sender_id = $_SESSION['user_id'];
        $receiver_id = $data['receiver_id'];
        $message = $data['message'];
        
        $stmt = $conn->prepare("INSERT INTO Messages (sender_id, receiver_id, message_content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message_id'] = $conn->insert_id;
        } else {
            $response['success'] = false;
            $response['error'] = 'Failed to send message';
        }
    } else {
        $response['success'] = false;
        $response['error'] = 'Missing required data';
    }
} else {
    $response['success'] = false;
    $response['error'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
?>