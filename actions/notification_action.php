<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

if (!isLoggedIn()) {
    header("Location: /ClassSync/login.php?error=session");
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

// ========================
// MARK NOTIFICATION AS READ
// ========================
if ($action === 'mark_read') {
    $notif_id = intval($_POST['notification_id'] ?? $_GET['notification_id'] ?? 0);

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notif_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/ClassSync/'));
    exit();
}

// ========================
// MARK ALL AS READ
// ========================
if ($action === 'mark_all_read') {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/ClassSync/'));
    exit();
}

header("Location: /ClassSync/");
exit();
?>
