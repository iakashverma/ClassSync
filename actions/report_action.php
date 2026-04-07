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
// ADD REPORT
// ========================
if ($action === 'add_report') {
    if ($role !== 'teacher' && $role !== 'admin') {
        header("Location: /ClassSync/login.php?error=unauthorized");
        exit();
    }

    // Fetch target class mapping from DB
    $class_assignment_id = intval($_POST['class_assignment_id'] ?? 0);
    $ca_query = $conn->query("SELECT course_id, year, section_id, subject_id FROM class_assignments WHERE id = $class_assignment_id");
    $class_data = $ca_query->fetch_assoc();
    
    $subject_id = $class_data['subject_id'] ?? 0;
    $course_id = $class_data['course_id'] ?? 0;
    $year = $class_data['year'] ?? '';
    $section_id = $class_data['section_id'] ?? 0;

    $topic = trim($_POST['topic'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $homework = trim($_POST['homework'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');

    if (empty($subject_id) || empty($topic)) {
        header("Location: /ClassSync/teacher/reports.php?error=required");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO reports (teacher_id, subject_id, course_id, year, section_id, topic, description, homework, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisissss", $user_id, $subject_id, $course_id, $year, $section_id, $topic, $description, $homework, $date);

    if ($stmt->execute()) {
        // Create notification for mapped students
        $students = $conn->query("SELECT id FROM users WHERE role = 'student' AND course_id='$course_id' AND year='$year' AND section_id='$section_id'");
        while ($student = $students->fetch_assoc()) {
            $msg = "New report uploaded: $topic";
            $notif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'report')");
            $notif->bind_param("is", $student['id'], $msg);
            $notif->execute();
            $notif->close();
        }
        header("Location: /ClassSync/teacher/reports.php?success=added");
    } else {
        header("Location: /ClassSync/teacher/reports.php?error=failed");
    }

    $stmt->close();
    exit();
}

// ========================
// EDIT REPORT
// ========================
if ($action === 'edit_report') {
    if ($role !== 'teacher' && $role !== 'admin') {
        header("Location: /ClassSync/login.php?error=unauthorized");
        exit();
    }

    $report_id = intval($_POST['report_id'] ?? 0);
    // Fetch target class mapping from DB
    $class_assignment_id = intval($_POST['class_assignment_id'] ?? 0);
    $ca_query = $conn->query("SELECT course_id, year, section_id, subject_id FROM class_assignments WHERE id = $class_assignment_id");
    $class_data = $ca_query->fetch_assoc();
    
    $subject_id = $class_data['subject_id'] ?? 0;
    $course_id = $class_data['course_id'] ?? 0;
    $year = $class_data['year'] ?? '';
    $section_id = $class_data['section_id'] ?? 0;

    $topic = trim($_POST['topic'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $homework = trim($_POST['homework'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');

    $stmt = $conn->prepare("UPDATE reports SET subject_id=?, course_id=?, year=?, section_id=?, topic=?, description=?, homework=?, date=? WHERE report_id=? AND teacher_id=?");
    $stmt->bind_param("iisissssii", $subject_id, $course_id, $year, $section_id, $topic, $description, $homework, $date, $report_id, $user_id);

    if ($stmt->execute()) {
        header("Location: /ClassSync/teacher/reports.php?success=updated");
    } else {
        header("Location: /ClassSync/teacher/reports.php?error=failed");
    }

    $stmt->close();
    exit();
}

// ========================
// DELETE REPORT
// ========================
if ($action === 'delete_report') {
    if ($role !== 'teacher' && $role !== 'admin') {
        header("Location: /ClassSync/login.php?error=unauthorized");
        exit();
    }

    $report_id = intval($_POST['report_id'] ?? 0);

    if ($role === 'admin') {
        $stmt = $conn->prepare("DELETE FROM reports WHERE report_id=?");
        $stmt->bind_param("i", $report_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM reports WHERE report_id=? AND teacher_id=?");
        $stmt->bind_param("ii", $report_id, $user_id);
    }

    $stmt->execute();
    $stmt->close();

    $redirect = ($role === 'admin') ? '/ClassSync/admin/manage_reports.php' : '/ClassSync/teacher/reports.php';
    header("Location: $redirect?success=deleted");
    exit();
}

header("Location: /ClassSync/");
exit();
?>
