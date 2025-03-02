<?php
session_start();
require_once '../../../db_connect.php';

$response = array();

if (!isset($_SESSION['user_id'])) {
    $response['error'] = 'Not logged in';
    echo json_encode($response);
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Get list of users who have conversations with current user
$users_query = "SELECT DISTINCT u.user_id, u.name, u.role 
                FROM Users u 
                WHERE u.user_id IN (
                    SELECT sender_id FROM Messages WHERE receiver_id = ?
                    UNION
                    SELECT receiver_id FROM Messages WHERE sender_id = ?
                )";

$stmt = $conn->prepare($users_query);
$stmt->bind_param("ii", $current_user_id, $current_user_id);
$stmt->execute();
$users_result = $stmt->get_result();

$users = array();
while ($row = $users_result->fetch_assoc()) {
    $users[] = array(
        'id' => $row['user_id'],
        'name' => $row['name'],
        'role' => $row['role']
    );
}

// If a specific user conversation is requested
if (isset($_GET['user_id'])) {
    $other_user_id = $_GET['user_id'];
    
    $messages_query = "SELECT m.*, u.name as sender_name 
                      FROM Messages m 
                      JOIN Users u ON m.sender_id = u.user_id 
                      WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                         OR (m.sender_id = ? AND m.receiver_id = ?) 
                      ORDER BY m.timestamp ASC";
    
    $stmt = $conn->prepare($messages_query);
    $stmt->bind_param("iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
    $stmt->execute();
    $messages_result = $stmt->get_result();
    
    $messages = array();
    while ($row = $messages_result->fetch_assoc()) {
        $messages[] = array(
            'sender' => $row['sender_id'] == $current_user_id ? 'me' : 'them',
            'text' => $row['message_content'],
            'timestamp' => date('h:i A', strtotime($row['timestamp'])),
            'sender_name' => $row['sender_name']
        );
    }
    
    $response['messages'] = $messages;
}

$response['users'] = $users;
$response['current_user_id'] = $current_user_id;

header('Content-Type: application/json');
echo json_encode($response);
?>