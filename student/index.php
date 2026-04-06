<?php
$page_title = 'Student Dashboard';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('student');

$active_page = 'dashboard';
$user_id = $_SESSION['user_id'];

// Stats
$total_reports = $conn->query("SELECT COUNT(*) as cnt FROM reports")->fetch_assoc()['cnt'];

// Attendance stats
$att_total = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = $user_id")->fetch_assoc()['cnt'];
$att_present = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = $user_id AND status = 'present'")->fetch_assoc()['cnt'];
$att_percentage = $att_total > 0 ? round(($att_present / $att_total) * 100, 1) : 0;

$pending_assignments = $conn->query("
    SELECT COUNT(*) as cnt FROM assignments a 
    WHERE a.deadline >= CURDATE() 
    AND a.id NOT IN (SELECT assignment_id FROM submissions WHERE student_id = $user_id)
")->fetch_assoc()['cnt'];

// Unread notifications
$unread = $conn->query("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = $user_id AND is_read = 0")->fetch_assoc()['cnt'];

// Recent reports
$recent_reports = $conn->query("SELECT r.*, u.name as teacher_name FROM reports r JOIN users u ON r.teacher_id = u.id ORDER BY r.date DESC LIMIT 3");

// Recent notifications
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
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
                    <h1>Student Dashboard</h1>
                </div>
                <span class="breadcrumb">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo $total_reports; ?></div>
                    <div class="stat-label">Class Reports</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value attendance-percentage <?php echo $att_percentage >= 75 ? 'good' : ($att_percentage >= 60 ? 'warning' : 'danger'); ?>">
                        <?php echo $att_percentage; ?>%
                    </div>
                    <div class="stat-label">Attendance</div>
                    <?php if ($att_percentage < 75 && $att_total > 0): ?>
                        <div style="color:#ef4444;font-size:12px;margin-top:5px;">⚠️ Below 75%!</div>
                    <?php endif; ?>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo $pending_assignments; ?></div>
                    <div class="stat-label">Pending Assignments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔔</div>
                    <div class="stat-value"><?php echo $unread; ?></div>
                    <div class="stat-label">Notifications</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <!-- Recent Reports -->
                <div class="card">
                    <h3>📚 Recent Reports</h3>
                    <?php if ($recent_reports->num_rows > 0): ?>
                        <?php while ($r = $recent_reports->fetch_assoc()): ?>
                        <div class="report-card" style="border:none;border-bottom:1px solid #e2e8f0;border-radius:0;border-left:none;padding:12px 0;">
                            <div class="report-header">
                                <span class="report-subject"><?php echo htmlspecialchars($r['subject']); ?></span>
                                <span class="report-date"><?php echo date('M d', strtotime($r['date'])); ?></span>
                            </div>
                            <div class="report-topic"><?php echo htmlspecialchars($r['topic']); ?></div>
                        </div>
                        <?php endwhile; ?>
                        <a href="/ClassSync/student/reports.php" class="btn btn-blue btn-sm mt-2" style="display:inline-block;">View All</a>
                    <?php else: ?>
                        <div class="empty-state"><p>No reports yet</p></div>
                    <?php endif; ?>
                </div>

                <!-- Notifications -->
                <div class="card">
                    <h3>🔔 Notifications</h3>
                    <?php if ($notifications->num_rows > 0): ?>
                        <?php while ($n = $notifications->fetch_assoc()): ?>
                        <div style="padding:10px 0;border-bottom:1px solid #e2e8f0;<?php echo $n['is_read'] ? '' : 'background:#eff6ff;padding:10px;border-radius:6px;margin-bottom:5px;'; ?>">
                            <div style="font-size:13px;color:#333;"><?php echo htmlspecialchars($n['message']); ?></div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:3px;"><?php echo date('M d, h:i A', strtotime($n['created_at'])); ?></div>
                        </div>
                        <?php endwhile; ?>
                        <form method="POST" action="/ClassSync/actions/notification_action.php" class="mt-2">
                            <input type="hidden" name="action" value="mark_all_read">
                            <button type="submit" class="btn btn-sm" style="background:#e2e8f0;color:#333;">Mark All Read</button>
                        </form>
                    <?php else: ?>
                        <div class="empty-state"><p>No notifications</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
