<?php
session_start();
require_once '../../../db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['content']) || empty(trim($data['content']))) {
    echo json_encode([
        'success' => false,
        'error' => 'Announcement content is required'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO TeacherAnnouncements (teacher_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $_SESSION['user_id'], $data['content']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Announcement posted successfully'
        ]);
    } else {
        throw new Exception("Failed to save announcement");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();