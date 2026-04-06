<?php
// index.php - Entry point
// redirects to login or dashboard based on session
require_once 'includes/auth.php';

if (isLoggedIn()) {
    $role = getUserRole();
    
    // send user to their dashboard
    if ($role == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($role == 'teacher') {
        header("Location: teacher/dashboard.php");
    } elseif ($role == 'student') {
        header("Location: student/dashboard.php");
    }
    exit();
} else {
    // not logged in, go to login
    header("Location: login.php");
    exit();
}
?>
