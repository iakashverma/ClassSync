<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser();
$currentRole = $currentUser ? $currentUser['role'] : '';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ClassSync – Smart Classwork & Assignment Tracking System">
    <title>ClassSync <?php echo isset($pageTitle) ? '| ' . e($pageTitle) : ''; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php if ($currentUser): ?>
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo BASE_URL; ?>" class="nav-brand">
                <span class="nav-logo">⚡</span>
                <span class="nav-title">ClassSync</span>
            </a>

            <div class="nav-links" id="navLinks">
                <?php if ($currentRole === 'admin'): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/admin/dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/admin/manage_users.php" class="nav-link <?php echo $currentPage === 'manage_users' ? 'active' : ''; ?>">
                        <span class="nav-icon">👥</span> Users
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/admin/manage_subjects.php" class="nav-link <?php echo $currentPage === 'manage_subjects' ? 'active' : ''; ?>">
                        <span class="nav-icon">📚</span> Subjects
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/admin/monitor.php" class="nav-link <?php echo $currentPage === 'monitor' ? 'active' : ''; ?>">
                        <span class="nav-icon">🔍</span> Monitor
                    </a>
                <?php elseif ($currentRole === 'teacher'): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/teacher/dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/teacher/create_classwork.php" class="nav-link <?php echo $currentPage === 'create_classwork' ? 'active' : ''; ?>">
                        <span class="nav-icon">➕</span> Create
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/teacher/my_classwork.php" class="nav-link <?php echo $currentPage === 'my_classwork' ? 'active' : ''; ?>">
                        <span class="nav-icon">📋</span> My Work
                    </a>
                <?php elseif ($currentRole === 'student'): ?>
                    <a href="<?php echo BASE_URL; ?>/pages/student/dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/student/assignments.php" class="nav-link <?php echo $currentPage === 'assignments' ? 'active' : ''; ?>">
                        <span class="nav-icon">📋</span> Assignments
                    </a>
                <?php endif; ?>
            </div>

            <div class="nav-right">
                <div class="nav-user">
                    <div class="nav-avatar"><?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?></div>
                    <div class="nav-user-info">
                        <span class="nav-user-name"><?php echo e($currentUser['name']); ?></span>
                        <span class="nav-user-role"><?php echo ucfirst($currentRole); ?></span>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/pages/auth/logout.php" class="nav-link nav-logout" title="Logout">
                    🚪
                </a>
                <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <?php
    $flash = getFlash();
    if ($flash):
    ?>
    <div class="toast toast-<?php echo $flash['type']; ?>" id="flashToast">
        <span class="toast-icon">
            <?php echo $flash['type'] === 'success' ? '✅' : ($flash['type'] === 'error' ? '❌' : 'ℹ️'); ?>
        </span>
        <span class="toast-message"><?php echo e($flash['message']); ?></span>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    </div>
    <?php endif; ?>

    <main class="main-content">
