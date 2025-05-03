<?php

session_start();


include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);


    $sql = "SELECT user_id, name, role FROM Users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {

        $row = $result->fetch_assoc();


        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['role'] = $row['role'];


        if ($row['role'] == 'teacher') {
            header("Location: teacher-dashboard.php");
        } else {
            header("Location: student-dashboard.php");
        }
        exit();
    } else {

        echo "Invalid email or password.";
    }
}


$conn->close();




?>