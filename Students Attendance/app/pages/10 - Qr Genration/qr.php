<?php
require_once "../../../db_connect.php"; 
session_start();


if (!isset($_SESSION['user_id'])) {
    
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") { 
         header('Content-Type: application/json');
         echo json_encode(["success" => false, "error" => "Not logged in"]);
    } else {
        
         header('Location: ../2 - login/login.html'); 
    }
    exit;
}



function generateRandomCode($length = 8) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, $max)]; 
    }
    return $code;
}

function getActiveSession($teacher_id) {
    
    
    
    global $conn;

    try {
        $sql = "SELECT session_id, date, start_time, end_time, session_group
                FROM AttendanceSessions
                WHERE teacher_id = ? AND status = 'open'
                AND DATE(date) = CURRENT_DATE
                ORDER BY start_time DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
         if (!$stmt) throw new Exception("DB error preparing active session check: ".$conn->error);
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
             $session_data = $result->fetch_assoc();
             $stmt->close();
             return $session_data;
        }
         $stmt->close();
        return null; 
    } catch (Exception $e) {
        error_log("Error getting active session for teacher $teacher_id: " . $e->getMessage());
        return null; 
    }
}

function createNewQRCode($session_id) {
    
    global $conn;

    try {
        
        $conn->begin_transaction();

        
        $verify_sql = "SELECT session_id FROM AttendanceSessions
                      WHERE session_id = ? AND status = 'open'";
        $verify_stmt = $conn->prepare($verify_sql);
         if (!$verify_stmt) throw new Exception("DB error preparing session verify: ".$conn->error);
        $verify_stmt->bind_param("i", $session_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();

        if ($verify_result->num_rows === 0) {
            $verify_stmt->close();
            throw new Exception("Cannot generate QR code: Invalid or closed session ID ($session_id)");
        }
        $verify_stmt->close();

        
        $max_retries = 5;
        $retry_count = 0;
        $code = null;
        do {
            $code = generateRandomCode();
            $check_stmt = $conn->prepare("SELECT code_value FROM QRCodes WHERE code_value = ?");
             if (!$check_stmt) throw new Exception("DB error preparing code uniqueness check: ".$conn->error);
            $check_stmt->bind_param("s", $code);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $is_unique = ($check_result->num_rows === 0);
            $check_stmt->close();
            $retry_count++;
        } while (!$is_unique && $retry_count < $max_retries);

        if (!$is_unique) {
            throw new Exception("Failed to generate a unique QR code after $max_retries attempts.");
        }

        
        
        $update_stmt = $conn->prepare("UPDATE QRCodes SET is_used = TRUE WHERE session_id = ? AND is_used = FALSE");
         if (!$update_stmt) throw new Exception("DB error preparing old QR update: ".$conn->error);
        $update_stmt->bind_param("i", $session_id);
        $update_stmt->execute(); 
        $update_stmt->close();

        
        $insert_sql = "INSERT INTO QRCodes (session_id, code_value, is_used) VALUES (?, ?, FALSE)";
        $insert_stmt = $conn->prepare($insert_sql);
         if (!$insert_stmt) throw new Exception("DB error preparing new QR insert: ".$conn->error);
        $insert_stmt->bind_param("is", $session_id, $code);

        if (!$insert_stmt->execute()) {
             $error_msg = $insert_stmt->error;
             $insert_stmt->close();
            throw new Exception("Failed to create QR code record: " . $error_msg);
        }
        $insert_stmt->close();

        
        $conn->commit();
        return $code; 

    } catch (Exception $e) {
        $conn->rollback(); 
        error_log("Error in createNewQRCode for session $session_id: " . $e->getMessage());
        throw $e; 
    }
}

function validateQRCode($code, $student_id) {
    
    global $conn;

    
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
         return ["success" => false, "error" => "Unauthorized: Only students can validate attendance."];
    }


    try {
        $conn->begin_transaction();

        error_log("Validating QR code: '$code' for student: $student_id");

        
        
        $student_group_sql = "SELECT `group` FROM Users WHERE user_id = ? AND role = 'student'";
        $student_group_stmt = $conn->prepare($student_group_sql);
        if (!$student_group_stmt) throw new Exception("DB error preparing student group fetch: ".$conn->error);
        $student_group_stmt->bind_param("i", $student_id);
        $student_group_stmt->execute();
        $student_group_result = $student_group_stmt->get_result();
        $student_data = $student_group_result->fetch_assoc();
        $student_group_stmt->close(); 

        if (!$student_data) {
             throw new Exception("Student record not found.");
        }
         
        if ($student_data['group'] === null || $student_data['group'] == 0) { 
             throw new Exception("You are not assigned to a group. Cannot mark attendance.");
        }
        $student_group = $student_data['group'];
        error_log("Student $student_id belongs to group: $student_group");
        


        
        $find_code_sql = "SELECT qc.qr_code_id, qc.session_id, sess.status as session_status, sess.session_group
                         FROM QRCodes qc
                         JOIN AttendanceSessions sess ON sess.session_id = qc.session_id
                         WHERE qc.code_value = ?
                         AND sess.status = 'open'
                         AND qc.is_used = FALSE";
        $find_stmt = $conn->prepare($find_code_sql);
        if (!$find_stmt) throw new Exception("DB error preparing QR code fetch: ".$conn->error);
        $find_stmt->bind_param("s", $code);
        $find_stmt->execute();
        $find_result = $find_stmt->get_result();

        if ($find_result->num_rows === 0) {
             $find_stmt->close(); 
            
            $check_code_sql = "SELECT qc.is_used, sess.status as session_status
                             FROM QRCodes qc
                             LEFT JOIN AttendanceSessions sess ON sess.session_id = qc.session_id
                             WHERE qc.code_value = ?";
            $check_stmt = $conn->prepare($check_code_sql);
             if (!$check_stmt) throw new Exception("DB error preparing code check: ".$conn->error);
            $check_stmt->bind_param("s", $code);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows === 0) {
                 $check_stmt->close();
                throw new Exception("Invalid QR code ('$code').");
            }

            $check_data = $check_result->fetch_assoc();
             $check_stmt->close();
            if ($check_data['session_status'] === null) {
                
                throw new Exception("Data inconsistency: QR code exists but session does not.");
            }
            if ($check_data['session_status'] !== 'open') {
                throw new Exception("Attendance session is closed.");
            }
            if ($check_data['is_used']) {
                throw new Exception("QR code has already been used.");
            }
            
            
            
            throw new Exception("QR code ('$code') is not currently valid.");
        }

        $qr_data = $find_result->fetch_assoc();
        $find_stmt->close(); 
        error_log("Found QR code data: " . print_r($qr_data, true));

        
        $session_group_for_qr = $qr_data['session_group'];
        error_log("QR code belongs to session for group: $session_group_for_qr");

        if ($session_group_for_qr != $student_group) {
            
            throw new Exception("This QR code is not valid for your group (Your group: $student_group, Code's group: $session_group_for_qr).");
        }
        

        
        $check_att_sql = "SELECT record_id FROM StudentAttendanceRecords
                         WHERE session_id = ? AND student_id = ?";
        $check_att_stmt = $conn->prepare($check_att_sql);
         if (!$check_att_stmt) throw new Exception("DB error preparing attendance check: ".$conn->error);
        $check_att_stmt->bind_param("ii", $qr_data['session_id'], $student_id);
        $check_att_stmt->execute();
        $check_att_result = $check_att_stmt->get_result();

        if ($check_att_result->num_rows > 0) {
             $check_att_stmt->close();
            throw new Exception("You have already marked attendance for this session.");
        }
        $check_att_stmt->close();

        
        $update_sql = "UPDATE QRCodes SET is_used = TRUE WHERE qr_code_id = ?";
        $update_stmt = $conn->prepare($update_sql);
         if (!$update_stmt) throw new Exception("DB error preparing QR update: ".$conn->error);
        $update_stmt->bind_param("i", $qr_data['qr_code_id']); 
        if (!$update_stmt->execute()) {
             $error_msg = $update_stmt->error;
             $update_stmt->close();
            throw new Exception("Failed to update QR code status: " . $error_msg);
        }
         if ($update_stmt->affected_rows === 0) {
             
             $update_stmt->close();
             throw new Exception("Failed to mark QR code as used (unexpected error).");
         }
         $update_stmt->close();


        
        $record_sql = "INSERT INTO StudentAttendanceRecords
                      (session_id, student_id, attendance_status, timestamp)
                      VALUES (?, ?, 'present', NOW())"; 
        $record_stmt = $conn->prepare($record_sql);
         if (!$record_stmt) throw new Exception("DB error preparing record insert: ".$conn->error);
        $record_stmt->bind_param("ii", $qr_data['session_id'], $student_id);
        if (!$record_stmt->execute()) {
             $error_msg = $record_stmt->error;
             $record_stmt->close();
             
             if ($conn->errno === 1062) { 
                 throw new Exception("You have already marked attendance for this session (concurrent check).");
             }
            throw new Exception("Failed to create attendance record: " . $error_msg);
        }
        $record_stmt->close();

        
        
        
        $new_code = null;
        try {
            $new_code = createNewQRCode($qr_data['session_id']);
        } catch (Exception $regen_e) {
            
            
            error_log("Failed to regenerate new QR code after validation for session {$qr_data['session_id']}: " . $regen_e->getMessage());
            
        }
        


        
        $conn->commit();
        error_log("Successfully validated and marked attendance for student $student_id in group $student_group");
        return [
            "success" => true,
            "message" => "Attendance marked successfully for Group " . $student_group,
            
        ];

    } catch (Exception $e) {
        
        $conn->rollback();
        error_log("QR validation error for student $student_id, code '$code': " . $e->getMessage());
        
        return ["success" => false, "error" => $e->getMessage()];
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    $action = $_POST["action"] ?? "";
    $response = []; 

    
    if ($action === "generate") {
         
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
             echo json_encode(["success" => false, "error" => "Unauthorized: Only teachers can generate QR codes."]);
             exit;
        }
        try {
            
            $activeSession = getActiveSession($_SESSION['user_id']);

            if (!$activeSession) {
                $response = [
                    "success" => false,
                    "error" => "No active session found for your group. Please start a session first."
                ];
            } else {
                
                $code = createNewQRCode($activeSession['session_id']);
                $response = [
                    "success" => true,
                    "code" => $code,
                    "session" => $activeSession 
                ];
            }
        } catch (Exception $e) {
            error_log("QR Code generation error for teacher {$_SESSION['user_id']}: " . $e->getMessage());
            $response = [
                "success" => false,
                "error" => "Failed to generate QR code: " . $e->getMessage() 
            ];
        }
        echo json_encode($response);
        exit;
    }
    
    elseif ($action === "validate") {
         
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
             echo json_encode(["success" => false, "error" => "Unauthorized: Only students can validate attendance."]);
             exit;
        }
        try {
            if (empty($_POST['code'])) {
                throw new Exception("QR code value is required");
            }
            $submitted_code = trim($_POST['code']); 

            error_log("Starting QR validation process for student: " . $_SESSION['user_id'] . " with code: " . $submitted_code);
            $result = validateQRCode($submitted_code, $_SESSION['user_id']);
            echo json_encode($result);

        } catch (Exception $e) {
            
            error_log("QR Code validation setup error for student {$_SESSION['user_id']}: " . $e->getMessage());
            echo json_encode([
                "success" => false,
                "error" => $e->getMessage() 
            ]);
        }
        exit;
    }
    
    else {
        echo json_encode(["success" => false, "error" => "Invalid action specified."]);
        exit;
    }

} else {
    
     header('Location: ../2 - login/login.html'); 
     exit;
}


if (isset($conn) && $conn instanceof mysqli) {
     $conn->close();
}
?>