<?php
require_once "../../../db_connect.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

function generateRandomCode($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $length > $i; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function getActiveSession($teacher_id) {
    global $conn;
    
    try {
        $sql = "SELECT session_id, date, start_time, end_time 
                FROM AttendanceSessions 
                WHERE teacher_id = ? AND status = 'open' 
                AND DATE(date) = CURRENT_DATE
                ORDER BY start_time DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    } catch (Exception $e) {
        error_log("Error getting active session: " . $e->getMessage());
        return null;
    }
}

function createNewQRCode($session_id) {
    global $conn;
    
    try {
        // Start transaction
        $conn->begin_transaction();

        // First verify the session exists and is open
        $verify_sql = "SELECT session_id FROM AttendanceSessions 
                      WHERE session_id = ? AND status = 'open'";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("i", $session_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0) {
            throw new Exception("Invalid or closed session");
        }

        // Generate unique code
        do {
            $code = generateRandomCode();
            $check_stmt = $conn->prepare("SELECT code_value FROM QRCodes WHERE code_value = ?");
            $check_stmt->bind_param("s", $code);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
        } while ($check_result->num_rows > 0);

        // Mark existing codes for this session as used
        $update_stmt = $conn->prepare("UPDATE QRCodes SET is_used = TRUE WHERE session_id = ?");
        $update_stmt->bind_param("i", $session_id);
        $update_stmt->execute();

        // Insert new QR code
        $insert_sql = "INSERT INTO QRCodes (session_id, code_value, is_used) VALUES (?, ?, FALSE)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("is", $session_id, $code);
        
        if (!$insert_stmt->execute()) {
            throw new Exception("Failed to create QR code record");
        }

        // Commit transaction
        $conn->commit();
        return $code;
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

function validateQRCode($code, $student_id) {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        error_log("Validating QR code: $code for student: $student_id");

        // First, try to find the QR code regardless of status to give better error messages
        $find_code_sql = "SELECT qc.*, sess.session_id, sess.status as session_status 
                         FROM QRCodes qc
                         JOIN AttendanceSessions sess ON sess.session_id = qc.session_id 
                         WHERE qc.code_value = ?
                         AND sess.status = 'open'
                         AND qc.is_used = FALSE";
        $find_stmt = $conn->prepare($find_code_sql);
        $find_stmt->bind_param("s", $code);
        $find_stmt->execute();
        $find_result = $find_stmt->get_result();
        
        if ($find_result->num_rows === 0) {
            // Try to find the code to give more specific error message
            $check_code_sql = "SELECT qc.is_used, sess.status as session_status 
                             FROM QRCodes qc
                             JOIN AttendanceSessions sess ON sess.session_id = qc.session_id 
                             WHERE qc.code_value = ?";
            $check_stmt = $conn->prepare($check_code_sql);
            $check_stmt->bind_param("s", $code);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                throw new Exception("Invalid QR code");
            }
            
            $check_data = $check_result->fetch_assoc();
            if ($check_data['session_status'] !== 'open') {
                throw new Exception("Attendance session is closed");
            }
            if ($check_data['is_used']) {
                throw new Exception("QR code has already been used");
            }
            throw new Exception("QR code is not valid");
        }

        $qr_data = $find_result->fetch_assoc();
        error_log("Found QR code data: " . print_r($qr_data, true));

        // Check if student already marked attendance for this session
        $check_sql = "SELECT record_id FROM StudentAttendanceRecords 
                     WHERE session_id = ? AND student_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $qr_data['session_id'], $student_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            throw new Exception("You have already marked attendance for this session");
        }

        // Mark QR code as used
        $update_sql = "UPDATE QRCodes SET is_used = TRUE WHERE code_value = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("s", $code);
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update QR code status: " . $update_stmt->error);
        }

        // Create attendance record
        $record_sql = "INSERT INTO StudentAttendanceRecords 
                      (session_id, student_id, attendance_status) 
                      VALUES (?, ?, 'present')";
        $record_stmt = $conn->prepare($record_sql);
        $record_stmt->bind_param("ii", $qr_data['session_id'], $student_id);
        if (!$record_stmt->execute()) {
            throw new Exception("Failed to create attendance record: " . $record_stmt->error);
        }

        // Generate new QR code
        $new_code = createNewQRCode($qr_data['session_id']);

        $conn->commit();
        error_log("Successfully validated and marked attendance");
        return [
            "success" => true, 
            "message" => "Attendance marked successfully",
            "new_code" => $new_code
        ];
        
    } catch (Exception $e) {
        error_log("QR validation error: " . $e->getMessage());
        $conn->rollback();
        throw $e;
    }
}

// Handle requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    $action = $_POST["action"] ?? "";
    
    if ($action === "generate") {
        try {
            // Get active session
            $activeSession = getActiveSession($_SESSION['user_id']);
            
            if (!$activeSession) {
                echo json_encode([
                    "success" => false, 
                    "error" => "No active session found. Please start a session."
                ]);
                exit;
            }

            // Generate new QR code
            $code = createNewQRCode($activeSession['session_id']);
            
            echo json_encode([
                "success" => true,
                "code" => $code,
                "session" => $activeSession
            ]);
            
        } catch (Exception $e) {
            error_log("QR Code generation error: " . $e->getMessage());
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
        exit;
    } elseif ($action === "validate") {
        try {
            // Allow both student and teacher roles for testing
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Not logged in");
            }

            if (empty($_POST['code'])) {
                throw new Exception("QR code is required");
            }

            error_log("Starting QR validation process for user: " . $_SESSION['user_id']);
            $result = validateQRCode($_POST['code'], $_SESSION['user_id']);
            echo json_encode($result);
            
        } catch (Exception $e) {
            error_log("QR Code validation error: " . $e->getMessage());
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage()
            ]);
        }
        exit;
    }
}
?>