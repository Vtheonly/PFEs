<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../2 - login/login.html');
    exit;
}

require_once '../../../db_connect.php';

// Debug: Print connection info
echo "<!-- Debug: Checking database connection -->";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "<!-- Debug: Database connected successfully -->";

// Query to get announcements
$query = "SELECT a.*, u.name as teacher_name 
          FROM TeacherAnnouncements a
          JOIN Users u ON a.teacher_id = u.user_id 
          ORDER BY a.created_at DESC";

echo "<!-- Debug: About to execute query: " . htmlspecialchars($query) . " -->";

$result = $conn->query($query);

if (!$result) {
    echo "<!-- Debug: Query failed: " . htmlspecialchars($conn->error) . " -->";
    die("Query failed: " . $conn->error);
}

echo "<!-- Debug: Query executed successfully -->";

$announcements = [];
$rowCount = 0;

while ($row = $result->fetch_assoc()) {
    $rowCount++;
    echo "<!-- Debug: Processing row " . $rowCount . ": " . htmlspecialchars(json_encode($row)) . " -->";
    $announcements[] = $row;
}

echo "<!-- Debug: Processed " . $rowCount . " rows -->";

// Convert timestamps to readable format
foreach ($announcements as &$announcement) {
    $announcement['created_at'] = date('F j, Y g:i A', strtotime($announcement['created_at']));
}

// Include the view file
include 'anouncement_view.php';
?>