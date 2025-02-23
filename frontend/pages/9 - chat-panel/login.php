<?php
// Start the session to store user data
session_start();

// Include database connection (assumes db.php exists)
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input to prevent SQL injection
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    // Query the database for a matching user
    $sql = "SELECT user_id, name, role FROM Users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // User found, fetch their data
        $row = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['role'] = $row['role'];

        // Redirect based on role
        if ($row['role'] == 'teacher') {
            header("Location: teacher-dashboard.php");
        } else {
            header("Location: student-dashboard.php");
        }
        exit();
    } else {
        // No user found, display error
        echo "Invalid email or password.";
    }
}

// Close the database connection
$conn->close();
?>