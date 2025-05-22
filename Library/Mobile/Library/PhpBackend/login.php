<?php
header("Content-Type: application/json");

// Database Configuration
$db_host = "localhost";     // Or your MySQL server IP/hostname
$db_user = "your_db_user";  // Replace with your MySQL username
$db_pass = "your_db_password"; // Replace with your MySQL password
$db_name = "library_db";    // Replace with your database name

// Response Array
$response = array();

// Check if username and password are set
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Check connection
    if ($conn->connect_error) {
        $response['status'] = "error";
        $response['message'] = "Connection failed: " . $conn->connect_error;
        echo json_encode($response);
        exit();
    }

    // IMPORTANT: Use Prepared Statements to prevent SQL Injection
    // In a real app, you should store hashed passwords using password_hash()
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ? AND password = ?");
    
    if ($stmt === false) {
        $response['status'] = "error";
        $response['message'] = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        echo json_encode($response);
        $conn->close();
        exit();
    }

    // Bind parameters
    $stmt->bind_param("ss", $username, $password);

    // Execute the query
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found
            $user = $result->fetch_assoc();
            
            // Login successful
            $response['status'] = "success";
            $response['message'] = "Login successful!";
            $response['user_id'] = $user['id'];
            $response['username'] = $user['username'];
        } else {
            // User not found or password incorrect
            $response['status'] = "error";
            $response['message'] = "Invalid username or password.";
        }
    } else {
        $response['status'] = "error";
        $response['message'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    $response['status'] = "error";
    $response['message'] = "Username and password are required.";
}

// Send JSON response back to the Android app
echo json_encode($response);
?> 