<?php
// Dashboard Sidebar Include
// Usage: Set $sidebar_role and $active_page before including this file
$user = getCurrentUser();
$current_path = $_SERVER['REQUEST_URI'] ?? '';

if (strpos($current_path, '/teacher/') !== false) {
    $sidebar_role = 'teacher';
} elseif (strpos($current_path, '/student/') !== false) {
    $sidebar_role = 'student';
} elseif (strpos($current_path, '/admin/') !== false) {
    $sidebar_role = 'admin';
} else {
    $sidebar_role = $user['role'];
}

$active_page = $active_page ?? 'dashboard';

// Get unread notifications count
$notif_count = 0;
if (isset($conn) && $user['id']) {
    $notif_q = $conn->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0");
    $notif_q->bind_param("i", $user['id']);
    $notif_q->execute();
    $notif_result = $notif_q->get_result();
    $notif_count = $notif_result->fetch_assoc()['cnt'] ?? 0;
    $notif_q->close();
}
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">Class<span>Sync</span></div>
        <div class="user-info">
            <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
            <span class="user-role"><?php echo ucfirst($user['role']); ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>

            <?php if ($sidebar_role === 'admin'): ?>
                <a href="/ClassSync/admin/" class="<?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
                <a href="/ClassSync/admin/manage_users.php" class="<?php echo $active_page === 'users' ? 'active' : ''; ?>">
                    <span class="nav-icon">👥</span> Manage Users
                </a>
                <a href="/ClassSync/admin/manage_reports.php" class="<?php echo $active_page === 'reports' ? 'active' : ''; ?>">
                    <span class="nav-icon">📚</span> Reports
                </a>
                <a href="/ClassSync/admin/manage_attendance.php" class="<?php echo $active_page === 'attendance' ? 'active' : ''; ?>">
                    <span class="nav-icon">📋</span> Attendance
                </a>
                <a href="/ClassSync/admin/manage_assignments.php" class="<?php echo $active_page === 'assignments' ? 'active' : ''; ?>">
                    <span class="nav-icon">📝</span> Assignments
                </a>
                
                <div class="nav-section-title" style="margin-top:20px;">Quick Access</div>
                <a href="/ClassSync/teacher/index.php">
                    <span class="nav-icon">👨‍🏫</span> View as Teacher
                </a>
                <a href="/ClassSync/student/index.php">
                    <span class="nav-icon">👨‍🎓</span> View as Student
                </a>

            <?php elseif ($sidebar_role === 'teacher'): ?>
                <a href="/ClassSync/teacher/" class="<?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
                <a href="/ClassSync/teacher/reports.php" class="<?php echo $active_page === 'reports' ? 'active' : ''; ?>">
                    <span class="nav-icon">📚</span> Class Reports
                </a>
                <a href="/ClassSync/teacher/attendance.php" class="<?php echo $active_page === 'attendance' ? 'active' : ''; ?>">
                    <span class="nav-icon">📋</span> Attendance
                </a>
                <a href="/ClassSync/teacher/assignments.php" class="<?php echo $active_page === 'assignments' ? 'active' : ''; ?>">
                    <span class="nav-icon">📝</span> Assignments
                </a>

            <?php elseif ($sidebar_role === 'student'): ?>
                <a href="/ClassSync/student/" class="<?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
                <a href="/ClassSync/student/reports.php" class="<?php echo $active_page === 'reports' ? 'active' : ''; ?>">
                    <span class="nav-icon">📚</span> Class Reports
                </a>
                <a href="/ClassSync/student/attendance.php" class="<?php echo $active_page === 'attendance' ? 'active' : ''; ?>">
                    <span class="nav-icon">📋</span> My Attendance
                </a>
                <a href="/ClassSync/student/assignments.php" class="<?php echo $active_page === 'assignments' ? 'active' : ''; ?>">
                    <span class="nav-icon">📝</span> Assignments
                </a>
                <a href="/ClassSync/student/timeline.php" class="<?php echo $active_page === 'timeline' ? 'active' : ''; ?>">
                    <span class="nav-icon">📅</span> Timeline
                </a>
            <?php endif; ?>

            <?php if ($user['role'] === 'admin' && $sidebar_role !== 'admin'): ?>
                <div class="nav-section-title" style="margin-top:20px;">Admin Controls</div>
                <a href="/ClassSync/admin/">
                    <span class="nav-icon">⬅️</span> Back to Admin
                </a>
            <?php endif; ?>

        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="/ClassSync/logout.php">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </div>
</aside>
