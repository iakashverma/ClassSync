<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine current page for active nav link
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Class Sync - Digitize Your Classroom Experience. Track daily class work, attendance, and assignments in one place.">
    <title><?php echo isset($page_title) ? $page_title . ' - Class Sync' : 'Class Sync'; ?></title>
    <link rel="stylesheet" href="/ClassSync/assets/css/style.css">
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="/ClassSync/assets/css/<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="/ClassSync/" class="logo">Class<span>Sync</span></a>
            <button class="menu-toggle" id="menu-toggle">☰</button>
            <div class="nav-links" id="nav-links">
                <a href="/ClassSync/" class="<?php echo $current_page === 'index' ? 'active' : ''; ?>">Home</a>
                <a href="/ClassSync/#about">About</a>
                <a href="/ClassSync/#features">Features</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    $dash_link = '/ClassSync/';
                    if ($_SESSION['role'] === 'admin') $dash_link = '/ClassSync/admin/';
                    elseif ($_SESSION['role'] === 'teacher') $dash_link = '/ClassSync/teacher/';
                    elseif ($_SESSION['role'] === 'student') $dash_link = '/ClassSync/student/';
                    ?>
                    <a href="<?php echo $dash_link; ?>">Dashboard</a>
                    <a href="/ClassSync/logout.php" class="btn-login">Logout</a>
                <?php else: ?>
                    <a href="/ClassSync/login.php" class="<?php echo $current_page === 'login' ? 'active' : ''; ?>">Login</a>
                    <a href="/ClassSync/register.php" class="btn-login">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
