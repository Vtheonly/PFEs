<?php
$conn = new mysqli("localhost", "root", "74532180", "pfe_std_att");

if ($conn->connect_error) {
    header('Content-Type: application/json');
    die(json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");
?>