<?php
/**
 * ClassSync - Logout
 */
require_once __DIR__ . '/../../config/database.php';

session_destroy();
header('Location: ' . BASE_URL . '/pages/auth/login.php');
exit;
