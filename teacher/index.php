<?php
$page_title = 'Teacher Dashboard';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('teacher');

$active_page = 'dashboard';
$user_id = $_SESSION['user_id'];

// Stats
$my_reports = $conn->query("SELECT COUNT(*) as cnt FROM reports WHERE teacher_id = $user_id")->fetch_assoc()['cnt'];
$my_assignments = $conn->query("SELECT COUNT(*) as cnt FROM assignments WHERE teacher_id = $user_id")->fetch_assoc()['cnt'];
$total_students = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role='student'")->fetch_assoc()['cnt'];
$today_attendance = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE date = CURDATE() AND marked_by = $user_id")->fetch_assoc()['cnt'];

// Recent reports
$recent_reports = $conn->query("SELECT * FROM reports WHERE teacher_id = $user_id ORDER BY created_at DESC LIMIT 5");

// Upcoming assignments
$upcoming = $conn->query("SELECT * FROM assignments WHERE teacher_id = $user_id AND deadline >= CURDATE() ORDER BY deadline ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Class Sync</title>
    <link rel="stylesheet" href="/ClassSync/assets/css/style.css">
    <link rel="stylesheet" href="/ClassSync/assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-page">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="top-bar">
                <div>
                    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('active')">☰</button>
                    <h1>Teacher Dashboard</h1>
                </div>
                <span class="breadcrumb">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo $my_reports; ?></div>
                    <div class="stat-label">My Reports</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo $my_assignments; ?></div>
                    <div class="stat-label">My Assignments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-value"><?php echo $total_students; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-value"><?php echo $today_attendance; ?></div>
                    <div class="stat-label">Marked Today</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <!-- Recent Reports -->
                <div class="card">
                    <h3>📚 Recent Reports</h3>
                    <?php if ($recent_reports->num_rows > 0): ?>
                        <?php while ($r = $recent_reports->fetch_assoc()): ?>
                        <div style="padding:10px 0;border-bottom:1px solid #e2e8f0;">
                            <strong style="color:#3b82f6;"><?php echo htmlspecialchars($r['subject']); ?></strong>
                            <span style="float:right;font-size:12px;color:#94a3b8;"><?php echo date('M d', strtotime($r['date'])); ?></span>
                            <div style="font-size:13px;color:#555;margin-top:3px;"><?php echo htmlspecialchars($r['topic']); ?></div>
                        </div>
                        <?php endwhile; ?>
                        <a href="/ClassSync/teacher/reports.php" class="btn btn-blue btn-sm mt-2" style="display:inline-block;">View All</a>
                    <?php else: ?>
                        <div class="empty-state"><p>No reports yet</p></div>
                    <?php endif; ?>
                </div>

                <!-- Upcoming Assignments -->
                <div class="card">
                    <h3>📝 Upcoming Deadlines</h3>
                    <?php if ($upcoming->num_rows > 0): ?>
                        <?php while ($a = $upcoming->fetch_assoc()): ?>
                        <div style="padding:10px 0;border-bottom:1px solid #e2e8f0;">
                            <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                            <span style="float:right;font-size:12px;color:#94a3b8;"><?php echo date('M d', strtotime($a['deadline'])); ?></span>
                        </div>
                        <?php endwhile; ?>
                        <a href="/ClassSync/teacher/assignments.php" class="btn btn-blue btn-sm mt-2" style="display:inline-block;">View All</a>
                    <?php else: ?>
                        <div class="empty-state"><p>No upcoming deadlines</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
