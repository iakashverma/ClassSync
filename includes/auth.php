<?php
/**
 * ClassSync - Authentication Helpers
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is logged in, redirect to login if not
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/pages/auth/login.php');
        exit;
    }
}

/**
 * Require a specific role, redirect if mismatch
 */
function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $role) {
        header('Location: ' . BASE_URL . '/pages/auth/login.php?error=unauthorized');
        exit;
    }
}

/**
 * Get current logged-in user data
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role'],
    ];
}

/**
 * Check if user is logged in (boolean)
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get redirect URL based on role
 */
function getRoleDashboard($role) {
    switch ($role) {
        case 'admin':
            return BASE_URL . '/pages/admin/dashboard.php';
        case 'teacher':
            return BASE_URL . '/pages/teacher/dashboard.php';
        case 'student':
            return BASE_URL . '/pages/student/dashboard.php';
        default:
            return BASE_URL . '/pages/auth/login.php';
    }
}
