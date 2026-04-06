<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check user role
function checkRole($required_role) {
    if (!isLoggedIn()) {
        header("Location: /ClassSync/login.php");
        exit();
    }
    if ($_SESSION['role'] !== $required_role && $_SESSION['role'] !== 'admin') {
        header("Location: /ClassSync/login.php?error=unauthorized");
        exit();
    }
}

// Get current user info
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'reg_no' => $_SESSION['reg_no'] ?? null
    ];
}

// Redirect if already logged in
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        $role = $_SESSION['role'];
        switch ($role) {
            case 'admin':
                header("Location: /ClassSync/admin/");
                break;
            case 'teacher':
                header("Location: /ClassSync/teacher/");
                break;
            case 'student':
                header("Location: /ClassSync/student/");
                break;
            default:
                header("Location: /ClassSync/");
        }
        exit();
    }
}
?>
