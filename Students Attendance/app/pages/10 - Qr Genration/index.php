<?php
session_start();
require_once '../../../db_connect.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../2 - login/teacher-login.html');
    exit;
}

// Include the QR generation HTML content
include 'qr.html';
?>