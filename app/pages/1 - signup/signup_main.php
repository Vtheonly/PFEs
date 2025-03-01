<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper JSON content type
header('Content-Type: application/json');

require_once '../../../db_connect.php';

function insertTeacher($name, $email, $password) {
    global $conn;
    
    try {
        $sql = "INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, 'teacher')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Teacher registered successfully"];
        } else {
            return ["success" => false, "message" => "Error registering teacher: " . $conn->error];
        }
    } catch (Exception $e) {
        return ["success" => false, "message" => "Error: " . $e->getMessage()];
    }
}

function insertStudent($name, $email, $password) {
    global $conn;
    
    try {
        $sql = "INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, 'student')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Student registered successfully"];
        } else {
            return ["success" => false, "message" => "Error registering student: " . $conn->error];
        }
    } catch (Exception $e) {
        return ["success" => false, "message" => "Error: " . $e->getMessage()];
    }
}

try {
    // Handle POST requests
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        if ($data === null) {
            throw new Exception('Invalid JSON data received: ' . json_last_error_msg());
        }
        
        if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['role'])) {
            throw new Exception('All fields are required');
        }
        
        if ($data['role'] === 'teacher') {
            $result = insertTeacher($data['name'], $data['email'], $data['password']);
        } else if ($data['role'] === 'student') {
            $result = insertStudent($data['name'], $data['email'], $data['password']);
        } else {
            throw new Exception('Invalid role specified');
        }
        
        echo json_encode($result);
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>