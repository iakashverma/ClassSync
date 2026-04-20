<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

function app_links_by_role(string $role): array
{
    if ($role === 'admin') {
        return [
            ['Dashboard', '/admin/dashboard.php'],
            ['Add Teacher', '/admin/add_teacher.php'],
            ['Add Student', '/admin/add_student.php'],
            ['View Records', '/admin/view_records.php'],
            ['Reports', '/admin/reports.php'],
            ['Logout', '/admin/logout.php'],
        ];
    }

    if ($role === 'teacher') {
        return [
            ['Dashboard', '/teacher/dashboard.php'],
            ['Create Classwork', '/teacher/classwork_create.php'],
            ['Attendance', '/teacher/attendance.php'],
            ['Submissions', '/teacher/submissions.php'],
            ['Feedback', '/teacher/feedback.php'],
            ['Weekly Reports', '/teacher/reports.php'],
            ['Logout', '/teacher/logout.php'],
        ];
    }

    return [
        ['Dashboard', '/student/dashboard.php'],
        ['Classwork', '/student/classwork.php'],
        ['Submit Work', '/student/submit.php'],
        ['Weekly Reports', '/student/report.php'],
        ['Logout', '/student/logout.php'],
    ];
}

function render_header(string $title, string $role, string $activePath): void
{
    $user = current_user();
    $links = app_links_by_role($role);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?> - ClassSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
    <script defer src="<?php echo BASE_URL; ?>/js/app.js"></script>
</head>
<body class="dashboard <?php echo e($role); ?>">
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">ClassSync</div>
        <div class="role-tag"><?php echo strtoupper(e($role)); ?></div>
        <nav>
            <?php foreach ($links as $link): ?>
                <?php [$label, $path] = $link; ?>
                <a class="nav-link <?php echo $activePath === $path ? 'active' : ''; ?>" href="<?php echo BASE_URL . $path; ?>">
                    <?php echo e($label); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <main class="main">
        <header class="topbar">
            <div class="topbar-title">
                <?php if ($role === 'admin'): ?>
                    <p class="topbar-kicker">Administration</p>
                <?php endif; ?>
                <h1><?php echo e($title); ?></h1>
            </div>
            <div class="user-pill"><?php echo e($user['name'] ?? 'User'); ?></div>
        </header>
        <section class="content">
            <?php if ($role === 'admin'): ?>
                <nav class="admin-toolbar" aria-label="Admin quick navigation">
                    <?php foreach ($links as $link): ?>
                        <?php [$label, $path] = $link; ?>
                        <?php if ($label === 'Logout'): ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <a class="admin-toolbar-link <?php echo $activePath === $path ? 'active' : ''; ?>" href="<?php echo BASE_URL . $path; ?>">
                            <?php echo e($label); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
<?php
}

function render_footer(): void
{
    ?>
        </section>
    </main>
</div>
</body>
</html>
<?php
}
