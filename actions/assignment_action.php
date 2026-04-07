<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

if (!isLoggedIn()) {
    header("Location: /ClassSync/login.php?error=session");
    exit();
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// ========================
// CREATE ASSIGNMENT
// ========================
if ($action === 'create_assignment') {
    if ($role !== 'teacher' && $role !== 'admin') {
        header("Location: /ClassSync/login.php?error=unauthorized");
        exit();
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline = $_POST['deadline'] ?? '';
    
    // Fetch target class mapping from DB
    $class_assignment_id = intval($_POST['class_assignment_id'] ?? 0);
    $ca_query = $conn->query("SELECT course_id, year, section_id, subject_id FROM class_assignments WHERE id = $class_assignment_id");
    $class_data = $ca_query->fetch_assoc();
    
    $subject_id = $class_data['subject_id'] ?? 0;
    $course_id = $class_data['course_id'] ?? 0;
    $year = $class_data['year'] ?? '';
    $section_id = $class_data['section_id'] ?? 0;

    if (empty($title) || empty($deadline) || empty($subject_id)) {
        header("Location: /ClassSync/teacher/assignments.php?error=required");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO assignments (title, description, deadline, teacher_id, subject_id, course_id, year, section_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiissi", $title, $description, $deadline, $user_id, $subject_id, $course_id, $year, $section_id);

    if ($stmt->execute()) {
        // Create notification for targeted students only
        $students = $conn->query("SELECT id FROM users WHERE role = 'student' AND course_id = '$course_id' AND year = '$year' AND section_id = '$section_id'");
        while ($student = $students->fetch_assoc()) {
            $msg = "New assignment: $title (Deadline: $deadline)";
            $notif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'assignment')");
            $notif->bind_param("is", $student['id'], $msg);
            $notif->execute();
            $notif->close();
        }
        header("Location: /ClassSync/teacher/assignments.php?success=created");
    } else {
        header("Location: /ClassSync/teacher/assignments.php?error=failed");
    }

    $stmt->close();
    exit();
}

// ========================
// DELETE ASSIGNMENT
// ========================
if ($action === 'delete_assignment') {
    if ($role !== 'teacher' && $role !== 'admin') {
        header("Location: /ClassSync/login.php?error=unauthorized");
        exit();
    }

    $assignment_id = intval($_POST['assignment_id'] ?? 0);

    if ($role === 'admin') {
        $stmt = $conn->prepare("DELETE FROM assignments WHERE id=?");
        $stmt->bind_param("i", $assignment_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM assignments WHERE id=? AND teacher_id=?");
        $stmt->bind_param("ii", $assignment_id, $user_id);
    }

    $stmt->execute();
    $stmt->close();

    $redirect = ($role === 'admin') ? '/ClassSync/admin/manage_assignments.php' : '/ClassSync/teacher/assignments.php';
    header("Location: $redirect?success=deleted");
    exit();
}

// ========================
// SUBMIT ASSIGNMENT (Student)
// ========================
if ($action === 'submit_assignment') {
    if ($role !== 'student') {
        header("Location: /ClassSync/login.php?error=unauthorized");
        exit();
    }

    $assignment_id = intval($_POST['assignment_id'] ?? 0);

    // Validate file upload
    if (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] !== UPLOAD_ERR_OK) {
        header("Location: /ClassSync/student/assignments.php?error=nofile");
        exit();
    }

    $file = $_FILES['submission_file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];

    // Get file extension
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Only allow PDF, DOC, DOCX
    $allowed = ['pdf', 'doc', 'docx'];
    if (!in_array($ext, $allowed)) {
        header("Location: /ClassSync/student/assignments.php?error=filetype");
        exit();
    }

    // Max 10MB
    if ($file_size > 10 * 1024 * 1024) {
        header("Location: /ClassSync/student/assignments.php?error=filesize");
        exit();
    }

    // Check deadline
    $assign = $conn->prepare("SELECT deadline FROM assignments WHERE id = ?");
    $assign->bind_param("i", $assignment_id);
    $assign->execute();
    $assign_result = $assign->get_result();

    $status = 'submitted';
    if ($assign_result->num_rows > 0) {
        $assignment = $assign_result->fetch_assoc();
        if (date('Y-m-d') > $assignment['deadline']) {
            $status = 'late';
        }
    }
    $assign->close();

    // Generate unique filename: studentId_assignmentId_timestamp.ext
    $new_filename = $user_id . '_' . $assignment_id . '_' . time() . '.' . $ext;
    $upload_dir = dirname(__DIR__) . '/uploads/submissions/';
    $upload_path = $upload_dir . $new_filename;

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($file_tmp, $upload_path)) {
        $file_path = 'uploads/submissions/' . $new_filename;
        $original_name = $file_name;

        // Delete old file if resubmitting
        $old = $conn->prepare("SELECT file_path FROM submissions WHERE assignment_id = ? AND student_id = ?");
        $old->bind_param("ii", $assignment_id, $user_id);
        $old->execute();
        $old_result = $old->get_result();
        if ($old_result->num_rows > 0) {
            $old_row = $old_result->fetch_assoc();
            if (!empty($old_row['file_path'])) {
                $old_file = dirname(__DIR__) . '/' . $old_row['file_path'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
        }
        $old->close();

        // Insert or update submission
        $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, file_path, submission_text, status, submitted_at) VALUES (?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE file_path=VALUES(file_path), submission_text=VALUES(submission_text), status=VALUES(status), submitted_at=NOW()");
        $stmt->bind_param("iisss", $assignment_id, $user_id, $file_path, $original_name, $status);

        if ($stmt->execute()) {
            header("Location: /ClassSync/student/assignments.php?success=submitted");
        } else {
            header("Location: /ClassSync/student/assignments.php?error=failed");
        }
        $stmt->close();
    } else {
        header("Location: /ClassSync/student/assignments.php?error=upload");
    }

    exit();
}

header("Location: /ClassSync/");
exit();
?>
