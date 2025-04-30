<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper JSON content type
header('Content-Type: application/json');

// Ensure the database connection file exists before requiring it
$db_connect_path = '../../../db_connect.php';
if (!file_exists($db_connect_path)) {
    // Log the error for server-side debugging
    error_log("Database connection file not found at: " . $db_connect_path);
    // Send a user-friendly JSON error response
    echo json_encode(["success" => false, "message" => "Server configuration error. Please contact administrator."]);
    exit; // Stop script execution
}
require_once $db_connect_path;

// Check if the connection variable $conn exists and is valid
if (!isset($conn) || $conn->connect_error) {
    error_log("Database connection failed: " . ($conn->connect_error ?? 'Unknown connection error'));
    echo json_encode(["success" => false, "message" => "Database connection error. Please try again later."]);
    exit;
}


function insertTeacher($name, $email, $password) {
    global $conn;

    try {
        // Teachers don't have a group, so we insert NULL or 0 depending on schema.
        // Assuming the 'group' column allows NULLs as per the original schema.
        $sql = "INSERT INTO Users (name, email, password, role, `group`) VALUES (?, ?, ?, 'teacher', NULL)";
        $stmt = $conn->prepare($sql);
        // Check if prepare() failed
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Teacher registered successfully"];
        } else {
            // Provide more specific error if possible (e.g., duplicate email)
            $error_message = $conn->errno === 1062 ? "Email already exists." : "Error registering teacher: " . $stmt->error;
             return ["success" => false, "message" => $error_message];
        }
    } catch (Exception $e) {
        error_log("Teacher Insert Error: " . $e->getMessage()); // Log detailed error
        return ["success" => false, "message" => "An internal error occurred during teacher registration."]; // User-friendly message
    } finally {
        // Ensure the statement is closed if it was created
        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
    }
}

// --- Updated insertStudent function ---
function insertStudent($name, $email, $password, $group) {
    global $conn;

    // **Add validation for group here before database interaction**
    if (!is_numeric($group) || $group < 1 || $group > 10) {
         return ["success" => false, "message" => "Invalid group selected."];
    }
    // Cast to integer after validation
    $group = (int)$group;

    try {
        // Added the `group` column to the INSERT statement
        // Using backticks around `group` is safer in case it's a reserved keyword
        $sql = "INSERT INTO Users (name, email, password, role, `group`) VALUES (?, ?, ?, 'student', ?)";
        $stmt = $conn->prepare($sql);
         // Check if prepare() failed
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Updated bind_param: "sssi" (string, string, string, integer)
        $stmt->bind_param("sssi", $name, $email, $password, $group);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Student registered successfully"];
        } else {
            // Provide more specific error if possible (e.g., duplicate email)
             $error_message = $conn->errno === 1062 ? "Email already exists." : "Error registering student: " . $stmt->error;
             return ["success" => false, "message" => $error_message];
        }
    } catch (Exception $e) {
        error_log("Student Insert Error: " . $e->getMessage()); // Log detailed error
        return ["success" => false, "message" => "An internal error occurred during student registration."]; // User-friendly message
    } finally {
        // Ensure the statement is closed if it was created
        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
    }
}
// --- End of updated insertStudent function ---

try {
    // Handle POST requests
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $input = file_get_contents("php://input");
        // Check if input is empty or invalid
        if (empty($input)) {
             throw new Exception('No data received');
        }

        $data = json_decode($input, true);

        // Check if JSON decoding failed
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data received: ' . json_last_error_msg());
        }

        // Validate required fields
        $required_fields = ['name', 'email', 'password', 'role'];
        // Add 'group' as required only if the role is 'student'
        if (isset($data['role']) && $data['role'] === 'student') {
            $required_fields[] = 'group';
        }

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                 // Use specific field name in error message
                throw new Exception(ucfirst($field) . ' is required');
            }
        }

        // Sanitize inputs (basic example, consider more robust sanitization/validation)
        $name = trim($data['name']);
        $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
        $password = $data['password']; // **CRITICAL: HASH THIS PASSWORD PROPERLY** See previous comments
        $role = trim($data['role']);

        // **Password Hashing (Essential Security)** - Placeholder, implement correctly
        // $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Use this instead of plain $password

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             throw new Exception('Invalid email format');
        }


        // Route to the correct insertion function based on role
        if ($role === 'teacher') {
             // Pass the plain password for now, but ideally pass the hashed password
            $result = insertTeacher($name, $email, $password);
        } else if ($role === 'student') {
            // Validate group data specifically for students before calling the function
            if (!isset($data['group']) || !is_numeric($data['group']) || $data['group'] < 1 || $data['group'] > 10) {
                 throw new Exception('Invalid or missing group for student');
            }
            $group = (int)$data['group'];
            // Pass the plain password for now, but ideally pass the hashed password
            $result = insertStudent($name, $email, $password, $group);
        } else {
            throw new Exception('Invalid role specified');
        }

        echo json_encode($result);

    } else {
        // Only allow POST method
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    // Catch any other exceptions and return a JSON error
    error_log("Signup Error: " . $e->getMessage()); // Log the error
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage() // Send the exception message back (consider if this is too revealing for production)
        // Alternatively, use a generic error message for production:
        // "message" => "An error occurred during signup. Please try again."
    ]);
} finally {
    // Ensure the database connection is closed
    if (isset($conn)) {
        $conn->close();
    }
}
?>