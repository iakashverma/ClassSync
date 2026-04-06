<?php
/**
 * ClassSync - Entry Point
 * Redirects to appropriate dashboard based on role, or to login
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    header('Location: ' . getRoleDashboard($role));
} else {
    header('Location: ' . BASE_URL . '/pages/auth/login.php');
}
exit;
