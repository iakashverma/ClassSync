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

    $subject = trim($_POST['subject'] ?? '');
    $topic = trim($_POST['topic'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $homework = trim($_POST['homework'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');

    if (empty($subject) || empty($topic)) {
        header("Location: /ClassSync/teacher/reports.php?error=required");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO reports (teacher_id, subject, topic, description, homework, date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $subject, $topic, $description, $homework, $date);

    if ($stmt->execute()) {
        // Create notification for all students
        $students = $conn->query("SELECT id FROM users WHERE role = 'student'");
        while ($student = $students->fetch_assoc()) {
            $msg = "New report uploaded: $topic ($subject)";
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
    $subject = trim($_POST['subject'] ?? '');
    $topic = trim($_POST['topic'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $homework = trim($_POST['homework'] ?? '');
    $date = $_POST['date'] ?? date('Y-m-d');

    $stmt = $conn->prepare("UPDATE reports SET subject=?, topic=?, description=?, homework=?, date=? WHERE report_id=? AND teacher_id=?");
    $stmt->bind_param("sssssii", $subject, $topic, $description, $homework, $date, $report_id, $user_id);

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
