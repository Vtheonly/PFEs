<?php
session_start();
require_once '../../../db_connect.php'; // Adjust path

header('Content-Type: application/json');

// Check if teacher is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized: Teacher access required.']);
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Check request method and required data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['record_id']) || !isset($_POST['new_status'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request. Missing data.']);
    exit;
}

$record_id = filter_var($_POST['record_id'], FILTER_VALIDATE_INT);
$new_status = $_POST['new_status']; // Should be 'accepted' or 'rejected'

// Validate inputs
if ($record_id === false || $record_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid record ID.']);
    exit;
}
if (!in_array($new_status, ['accepted', 'rejected'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid status provided.']);
    exit;
}


try {
     $conn->begin_transaction();

    // 1. Get the teacher's group
    $group_sql = "SELECT `group` FROM Users WHERE user_id = ? AND role = 'teacher'";
    $group_stmt = $conn->prepare($group_sql);
    if (!$group_stmt) throw new Exception("DB error preparing group fetch: " . $conn->error);
    $group_stmt->bind_param("i", $teacher_id);
    $group_stmt->execute();
    $group_result = $group_stmt->get_result();
    $teacher_data = $group_result->fetch_assoc();
    $group_stmt->close();

    if (!$teacher_data || $teacher_data['group'] === null || $teacher_data['group'] <= 0) {
         throw new Exception("Teacher is not assigned to a valid group.");
    }
    $teacher_group = $teacher_data['group'];


    // 2. Security Check: Verify the record belongs to a student in the teacher's group
    $check_sql = "SELECT u.`group`
                  FROM StudentAttendanceRecords sar
                  JOIN Users u ON sar.student_id = u.user_id
                  WHERE sar.record_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) throw new Exception("DB error preparing student group check: " . $conn->error);
    $check_stmt->bind_param("i", $record_id);
    $check_stmt->execute();
    $student_result = $check_stmt->get_result();
    $student_data = $student_result->fetch_assoc();
    $check_stmt->close();

    if (!$student_data) {
         throw new Exception("Record not found.");
    }
    if ($student_data['group'] != $teacher_group) {
         throw new Exception("Unauthorized: You cannot manage justifications for students outside your group.");
    }


    // 3. Update the justification status
    // Only update if the current status is 'pending' to avoid accidental overrides? (Good practice)
    $update_sql = "UPDATE StudentAttendanceRecords
                   SET justification_status = ?
                   WHERE record_id = ?
                   AND justification_status = 'pending'
                   AND student_id IN (SELECT user_id FROM Users WHERE `group` = ?)"; // Redundant group check for safety

    $update_stmt = $conn->prepare($update_sql);
     if (!$update_stmt) throw new Exception("DB error preparing status update: " . $conn->error);

    $update_stmt->bind_param("sii", $new_status, $record_id, $teacher_group);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update justification status: " . $update_stmt->error);
    }

     if ($update_stmt->affected_rows === 0) {
         // Could mean it wasn't pending, or the record didn't exist / didn't match group (should have been caught earlier)
          // Check if it was already updated
          $verify_sql = "SELECT justification_status FROM StudentAttendanceRecords WHERE record_id = ?";
          $verify_stmt = $conn->prepare($verify_sql);
          $verify_stmt->bind_param("i", $record_id);
          $verify_stmt->execute();
          $verify_res = $verify_stmt->get_result()->fetch_assoc();
          $verify_stmt->close();
          if ($verify_res && $verify_res['justification_status'] === $new_status) {
              // Already set to the desired status, treat as success
              // $conn->commit();
              // echo json_encode(['success' => true, 'message' => 'Status was already updated.']);
              // exit;
               throw new Exception("Failed to update: Status might have already been changed or record does not match criteria.");
          } else {
             throw new Exception("Failed to update justification status. Record may not be pending or does not meet criteria.");
          }
     }

    $update_stmt->close();
    $conn->commit();

    echo json_encode(['success' => true, 'message' => "Justification status updated to '$new_status'."]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error updating justification $record_id by teacher $teacher_id: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
     if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>