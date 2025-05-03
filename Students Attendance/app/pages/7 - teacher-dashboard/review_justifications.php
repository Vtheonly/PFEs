<?php
session_start();
// Redirect if not logged in or not a teacher
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../2 - login/teacher-login.html'); // Adjust path if needed
    exit;
}
// Include the HTML view part
include 'review_justifications_view.php';
?>