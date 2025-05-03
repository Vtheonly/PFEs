<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$db_connect_path = '../../../db_connect.php';
if (!file_exists($db_connect_path)) {
    error_log("Database connection file not found at: " . $db_connect_path);
    echo json_encode(["success" => false, "message" => "Server configuration error. Please contact administrator."]);
    exit;
}
require_once $db_connect_path;

if (!isset($conn) || $conn->connect_error) {
    error_log("Database connection failed: " . ($conn->connect_error ?? 'Unknown connection error'));
    echo json_encode(["success" => false, "message" => "Database connection error. Please try again later."]);
    exit;
}

function insertTeacher($name, $email, $password)
{
    global $conn;

    try {
        $sql = "INSERT INTO Users (name, email, password, role, `group`) VALUES (?, ?, ?, 'teacher', NULL)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Teacher registered successfully"];
        } else {
            $error_message = $conn->errno === 1062 ? "Email already exists." : "Error registering teacher: " . $stmt->error;
            return ["success" => false, "message" => $error_message];
        }
    } catch (Exception $e) {
        error_log("Teacher Insert Error: " . $e->getMessage());
        return ["success" => false, "message" => "An internal error occurred during teacher registration."];
    } finally {
        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
    }
}

function insertStudent($name, $email, $password, $group)
{
    global $conn;

    if (!is_numeric($group) || $group < 1 || $group > 10) {
        return ["success" => false, "message" => "Invalid group selected."];
    }
    $group = (int) $group;

    try {
        $sql = "INSERT INTO Users (name, email, password, role, `group`) VALUES (?, ?, ?, 'student', ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sssi", $name, $email, $password, $group);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Student registered successfully"];
        } else {
            $error_message = $conn->errno === 1062 ? "Email already exists." : "Error registering student: " . $stmt->error;
            return ["success" => false, "message" => $error_message];
        }
    } catch (Exception $e) {
        error_log("Student Insert Error: " . $e->getMessage());
        return ["success" => false, "message" => "An internal error occurred during student registration."];
    } finally {
        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
    }
}

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $input = file_get_contents("php://input");
        if (empty($input)) {
            throw new Exception('No data received');
        }

        $data = json_decode($input, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data received: ' . json_last_error_msg());
        }

        $required_fields = ['name', 'email', 'password', 'role'];
        if (isset($data['role']) && $data['role'] === 'student') {
            $required_fields[] = 'group';
        }

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                throw new Exception(ucfirst($field) . ' is required');
            }
        }

        $name = trim($data['name']);
        $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
        $password = $data['password'];
        $role = trim($data['role']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        if ($role === 'teacher') {
            $result = insertTeacher($name, $email, $password);
        } else if ($role === 'student') {
            if (!isset($data['group']) || !is_numeric($data['group']) || $data['group'] < 1 || $data['group'] > 10) {
                throw new Exception('Invalid or missing group for student');
            }
            $group = (int) $data['group'];
            $result = insertStudent($name, $email, $password, $group);
        } else {
            throw new Exception('Invalid role specified');
        }

        echo json_encode($result);

    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    error_log("Signup Error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>