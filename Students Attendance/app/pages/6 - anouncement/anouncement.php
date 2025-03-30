<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../2 - login/login.html');
    exit;
}

require_once '../../../db_connect.php';

// Query to get announcements with teacher names
$query = "SELECT a.*, u.name as teacher_name 
          FROM TeacherAnnouncements a
          JOIN Users u ON a.teacher_id = u.user_id 
          ORDER BY a.created_at DESC";

$result = $conn->query($query);

if (!$result) {
    die("Error fetching announcements: " . $conn->error);
}

$announcements = [];
while ($row = $result->fetch_assoc()) {
    // Convert timestamps to readable format
    $row['created_at'] = date('F j, Y g:i A', strtotime($row['created_at']));
    $announcements[] = $row;
}

// Include the view file
include 'anouncement_view.php';
?>