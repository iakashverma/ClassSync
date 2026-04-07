<?php
$page_title = 'Student Dashboard';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('student');

$active_page = 'dashboard';
$user_id = $_SESSION['user_id'];

// Get student class details
$user_info = $conn->query("
    SELECT u.name, c.course_name, u.year, s.section_name, u.course_id, u.section_id
    FROM users u 
    LEFT JOIN courses c ON u.course_id = c.course_id 
    LEFT JOIN sections s ON u.section_id = s.section_id 
    WHERE u.id = $user_id
")->fetch_assoc();

$student_name = $user_info['name'] ?? '';
$course_name = $user_info['course_name'] ?? 'N/A';
$year_name = $user_info['year'] ?? 'N/A';
$section_name = $user_info['section_name'] ?? 'N/A';

$course_id = $user_info['course_id'] ?? 0;
$year = $user_info['year'] ?? '';
$section_id = $user_info['section_id'] ?? 0;

// Stats
$total_reports = $conn->query("SELECT COUNT(*) as cnt FROM reports WHERE course_id = '$course_id' AND year = '$year' AND section_id = '$section_id'")->fetch_assoc()['cnt'];

// Attendance stats
$att_total = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = $user_id")->fetch_assoc()['cnt'];
$att_present = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = $user_id AND status = 'present'")->fetch_assoc()['cnt'];
$att_percentage = $att_total > 0 ? round(($att_present / $att_total) * 100, 1) : 0;

$pending_assignments = $conn->query("
    SELECT COUNT(*) as cnt FROM assignments a 
    WHERE a.deadline >= CURDATE() 
    AND a.course_id = '$course_id' AND a.year = '$year' AND a.section_id = '$section_id'
    AND a.id NOT IN (SELECT assignment_id FROM submissions WHERE student_id = $user_id)
")->fetch_assoc()['cnt'];

// Unread notifications
$unread = $conn->query("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = $user_id AND is_read = 0")->fetch_assoc()['cnt'];

// Recent reports
$recent_reports = $conn->query("
    SELECT r.*, s.subject_name, u.name as teacher_name 
    FROM reports r 
    JOIN subjects s ON r.subject_id = s.subject_id
    JOIN users u ON r.teacher_id = u.id 
    WHERE r.course_id = '$course_id' AND r.year = '$year' AND r.section_id = '$section_id'
    ORDER BY r.date DESC LIMIT 3
");

// Enrolled subjects & teachers
$enrolled_subjects = $conn->query("
    SELECT s.subject_name, u.name as teacher_name
    FROM class_assignments ca
    JOIN subjects s ON ca.subject_id = s.subject_id
    JOIN users u ON ca.teacher_id = u.id
    WHERE ca.course_id = '$course_id' AND ca.year = '$year' AND ca.section_id = '$section_id'
");

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

            <!-- Profile Info Alert/Card -->
            <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 5px solid #22c55e;">
                <h2 style="margin: 0; color: #166534; font-size: 20px; margin-bottom: 5px;">👨‍🎓 <?php echo htmlspecialchars($student_name); ?></h2>
                <div style="color: #475569; font-size: 15px; display: flex; gap: 20px;">
                    <span><strong>Course:</strong> <?php echo htmlspecialchars($course_name); ?></span>
                    <span><strong>Year:</strong> <?php echo htmlspecialchars($year_name); ?></span>
                    <span><strong>Section:</strong> <?php echo htmlspecialchars($section_name); ?></span>
                </div>
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

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <!-- Enrolled Subjects -->
                <div class="card">
                    <h3>📚 Enrolled Subjects</h3>
                    <?php if ($enrolled_subjects && $enrolled_subjects->num_rows > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php while ($sub = $enrolled_subjects->fetch_assoc()): ?>
                            <div style="padding: 12px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3b82f6;">
                                <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($sub['subject_name']); ?></div>
                                <div style="font-size: 13px; color: #64748b; margin-top: 4px;">👨‍🏫 <?php echo htmlspecialchars($sub['teacher_name']); ?></div>
                            </div>
                        <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state"><p>No subjects enrolled yet</p></div>
                    <?php endif; ?>
                </div>

                <!-- Recent Reports -->
                <div class="card">
                    <h3>📝 Recent Reports</h3>
                    <?php if ($recent_reports && $recent_reports->num_rows > 0): ?>
                        <?php while ($r = $recent_reports->fetch_assoc()): ?>
                        <div class="report-card" style="border:none;border-bottom:1px solid #e2e8f0;border-radius:0;border-left:none;padding:12px 0;">
                            <div class="report-header">
                                <span class="report-subject"><?php echo htmlspecialchars($r['subject_name']); ?></span>
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
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

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
