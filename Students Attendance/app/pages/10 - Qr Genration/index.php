<?php
session_start();
require_once '../../../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../2 - login/teacher-login.html');
    exit;
}

include 'qr.html';
?>