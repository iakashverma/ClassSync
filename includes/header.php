<?php
// Common header file
// includes navbar and sidebar based on user role
require_once __DIR__ . '/auth.php';

$currentRole = getUserRole();
$currentName = getUserName();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ClassSync - Daily College Classwork & Assignment Tracking System">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ClassSync' : 'ClassSync'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/ClassSync/assets/css/style.css">
</head>
<body>

<?php if (isLoggedIn()): ?>
<!-- Sidebar Navigation -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-graduation-cap"></i>
            <span>ClassSync</span>
        </div>
    </div>
    
    <div class="sidebar-user">
        <div class="user-avatar">
            <?php echo strtoupper(substr($currentName, 0, 1)); ?>
        </div>
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($currentName); ?></span>
            <span class="user-role"><?php echo ucfirst($currentRole); ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($currentRole == 'admin'): ?>
            <a href="/ClassSync/admin/dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="/ClassSync/admin/manage_users.php" class="nav-link"><i class="fas fa-users"></i> Manage Users</a>
            <a href="/ClassSync/admin/manage_subjects.php" class="nav-link"><i class="fas fa-book"></i> Manage Subjects</a>
            <a href="/ClassSync/admin/view_assignments.php" class="nav-link"><i class="fas fa-tasks"></i> All Assignments</a>
        <?php elseif ($currentRole == 'teacher'): ?>
            <a href="/ClassSync/teacher/dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="/ClassSync/teacher/create_assignment.php" class="nav-link"><i class="fas fa-plus-circle"></i> Create Assignment</a>
            <a href="/ClassSync/teacher/my_assignments.php" class="nav-link"><i class="fas fa-clipboard-list"></i> My Assignments</a>
            <a href="/ClassSync/teacher/upload_material.php" class="nav-link"><i class="fas fa-file-upload"></i> Upload Material</a>
            <a href="/ClassSync/teacher/manage_materials.php" class="nav-link"><i class="fas fa-book-open"></i> My Materials</a>
            <a href="/ClassSync/teacher/add_video.php" class="nav-link"><i class="fas fa-video"></i> Add Video</a>
            <a href="/ClassSync/teacher/manage_videos.php" class="nav-link"><i class="fas fa-play-circle"></i> My Videos</a>
        <?php elseif ($currentRole == 'student'): ?>
            <a href="/ClassSync/student/dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="/ClassSync/student/view_assignments.php" class="nav-link"><i class="fas fa-clipboard-list"></i> Assignments</a>
        <?php endif; ?>
        
        <a href="/ClassSync/logout.php" class="nav-link logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
</div>

<!-- Main Content Area -->
<div class="main-content" id="mainContent">
    <!-- Top Bar -->
    <div class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="page-title"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h2>
        <div class="topbar-right">
            <span class="welcome-text">Welcome, <?php echo htmlspecialchars($currentName); ?></span>
        </div>
    </div>
    
    <!-- Page Content starts here -->
    <div class="content-wrapper">
<?php endif; ?>
