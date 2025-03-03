<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
error_log("Login attempt started");

if (!file_exists('../../../db_connect.php')) {
    error_log("db_connect.php not found");
    die(json_encode(["success" => false, "message" => "Configuration error"]));
}
require_once '../../../db_connect.php';

if (!isset($conn)) {
    error_log("Database connection not established");
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

function validateStudentLogin($email, $password) {
    global $conn;
    error_log("Validating student login for email: " . $email);
    
    try {
        $stmt = $conn->prepare("SELECT user_id, name, password FROM Users WHERE email = ? AND role = 'student'");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return ["success" => false, "message" => "Database error"];
        }
        
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return ["success" => false, "message" => "Database error"];
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();
            
            if ($password === $student['password']) {
                $_SESSION['user_id'] = $student['user_id'];
                $_SESSION['name'] = $student['name'];
                $_SESSION['role'] = 'student';
                return ["success" => true, "message" => "Login successful"];
            }
        }
        return ["success" => false, "message" => "Invalid email or password"];
    } catch (Exception $e) {
        error_log("Student login error: " . $e->getMessage());
        return ["success" => false, "message" => "An error occurred during login"];
    }
}

function validateTeacherLogin($email, $password) {
    global $conn;
    error_log("Teacher login attempt - Email: " . $email);
    error_log("Submitted password length: " . strlen($password));
    
    try {
        $stmt = $conn->prepare("SELECT user_id, name, password, role FROM Users WHERE email = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return ["success" => false, "message" => "Database error"];
        }
        
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return ["success" => false, "message" => "Database error"];
        }
        
        $result = $stmt->get_result();
        error_log("Query result rows: " . $result->num_rows);
        
        if ($result->num_rows === 1) {
            $teacher = $result->fetch_assoc();
            error_log("User found - Role: " . $teacher['role']);
            error_log("Stored password length: " . strlen($teacher['password']));
            error_log("Password comparison: " . var_export($password === $teacher['password'], true));
            error_log("Raw password check - Stored: '" . $teacher['password'] . "', Submitted: '" . $password . "'");
            
            // Check role first
            if ($teacher['role'] !== 'teacher') {
                error_log("User found but not a teacher");
                return ["success" => false, "message" => "Invalid email or password"];
            }
            
            // Then check password
            if ($password === $teacher['password']) {
                $_SESSION['user_id'] = $teacher['user_id'];
                $_SESSION['name'] = $teacher['name'];
                $_SESSION['role'] = 'teacher';
                error_log("Teacher login successful - User ID: " . $teacher['user_id']);
                return ["success" => true, "message" => "Login successful"];
            }
        }
        error_log("Invalid login attempt");
        return ["success" => false, "message" => "Invalid email or password"];
    } catch (Exception $e) {
        error_log("Teacher login error: " . $e->getMessage());
        return ["success" => false, "message" => "An error occurred during login"];
    }
}

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST data received: " . print_r($_POST, true));
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    if (empty($email) || empty($password) || empty($role)) {
        error_log("Email, password, or role is empty");
        echo json_encode(["success" => false, "message" => "Email, password, and role are required"]);
        exit();
    }
    
    // Use the appropriate validation function based on submitted role
    $result = ($role === 'teacher') ? 
        validateTeacherLogin($email, $password) : 
        validateStudentLogin($email, $password);
    
    error_log("Login result: " . print_r($result, true));
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
} else {
    error_log("Non-POST request received");
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}
?>