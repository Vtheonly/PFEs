<?php
$conn = new mysqli("stdatndnc", "root", "74532180", "pfe_std_att");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>