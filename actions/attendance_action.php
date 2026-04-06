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
// MARK ATTENDANCE
// ========================
if ($action === 'mark_attendance') {
    if ($role !== 'teacher' && $role !== 'admin') {
        header("Location: /ClassSync/login.php?error=unauthorized");
        exit();
    }

    $date = $_POST['date'] ?? date('Y-m-d');
    $attendance = $_POST['attendance'] ?? [];

    // Get all students
    $students = $conn->query("SELECT id FROM users WHERE role = 'student'");

    while ($student = $students->fetch_assoc()) {
        $student_id = $student['id'];
        $status = isset($attendance[$student_id]) ? $attendance[$student_id] : 'absent';

        // Use INSERT ... ON DUPLICATE KEY UPDATE since we have UNIQUE(student_id, date)
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status, marked_by) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status=VALUES(status), marked_by=VALUES(marked_by)");
        $stmt->bind_param("issi", $student_id, $date, $status, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: /ClassSync/teacher/attendance.php?success=marked&date=" . $date);
    exit();
}

header("Location: /ClassSync/");
exit();
?>
