<?php
header("Content-Type: application/json");

// Database Configuration
$db_host = "localhost";     // Or your MySQL server IP/hostname
$db_user = "your_db_user";  // Replace with your MySQL username
$db_pass = "your_db_password"; // Replace with your MySQL password
$db_name = "library_db";    // Replace with your database name

// Response Array
$response = array();

// Check if required fields are set
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Validate input
    if (empty($username) || empty($password) || empty($email)) {
        $response['status'] = "error";
        $response['message'] = "All fields are required.";
        echo json_encode($response);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['status'] = "error";
        $response['message'] = "Invalid email format.";
        echo json_encode($response);
        exit();
    }

    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Check connection
    if ($conn->connect_error) {
        $response['status'] = "error";
        $response['message'] = "Connection failed: " . $conn->connect_error;
        echo json_encode($response);
        exit();
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    
    if ($stmt === false) {
        $response['status'] = "error";
        $response['message'] = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        echo json_encode($response);
        $conn->close();
        exit();
    }

    // Bind parameters
    $stmt->bind_param("ss", $username, $email);

    // Execute the query
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Username or email already exists
            $response['status'] = "error";
            $response['message'] = "Username or email already exists.";
            echo json_encode($response);
            $stmt->close();
            $conn->close();
            exit();
        }
    } else {
        $response['status'] = "error";
        $response['message'] = "Check failed: (" . $stmt->errno . ") " . $stmt->error;
        echo json_encode($response);
        $stmt->close();
        $conn->close();
        exit();
    }

    $stmt->close();

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    
    if ($stmt === false) {
        $response['status'] = "error";
        $response['message'] = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        echo json_encode($response);
        $conn->close();
        exit();
    }

    // Bind parameters
    $stmt->bind_param("sss", $username, $hashed_password, $email);

    // Execute the query
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        
        // Registration successful
        $response['status'] = "success";
        $response['message'] = "Registration successful!";
        $response['user_id'] = $user_id;
        $response['username'] = $username;
    } else {
        $response['status'] = "error";
        $response['message'] = "Registration failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    $response['status'] = "error";
    $response['message'] = "Username, password, and email are required.";
}

// Send JSON response back to the Android app
echo json_encode($response);
?> 