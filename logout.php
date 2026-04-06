<?php
// logout.php - destroy session and redirect to login
require_once 'includes/auth.php';

// clear all session data
session_unset();
session_destroy();

// send back to login page
header("Location: login.php");
exit();
?>
