<?php
// Auth helper functions
// handles sessions, login checks, role checks

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// get current user's role
function getUserRole() {
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'];
    }
    return null;
}

// get current user's id
function getUserId() {
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    return null;
}

// get current user's name
function getUserName() {
    if (isset($_SESSION['user_name'])) {
        return $_SESSION['user_name'];
    }
    return "Guest";
}

// redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /ClassSync/login.php");
        exit();
    }
}

// redirect if user doesn't have the right role
function requireRole($role) {
    requireLogin();
    if (getUserRole() != $role) {
        header("Location: /ClassSync/login.php");
        exit();
    }
}
?>
