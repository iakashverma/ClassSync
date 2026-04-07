<?php
$page_title = 'Teacher Dashboard';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('teacher');

$active_page = 'dashboard';
$user_id = $_SESSION['user_id'];

// Get teacher specific info
$teacher_info = $conn->query("
    SELECT u.name, u.department, s.subject_name 
    FROM users u 
    LEFT JOIN subjects s ON u.subject_id = s.subject_id 
    WHERE u.id = $user_id
")->fetch_assoc();

$teacher_name = $teacher_info['name'] ?? '';
$department_name = $teacher_info['department'] ?? 'N/A';
$principal_subject = $teacher_info['subject_name'] ?? 'N/A';

// Stats
$my_reports = $conn->query("SELECT COUNT(*) as cnt FROM reports WHERE teacher_id = $user_id")->fetch_assoc()['cnt'];
$my_assignments = $conn->query("SELECT COUNT(*) as cnt FROM assignments WHERE teacher_id = $user_id")->fetch_assoc()['cnt'];

// Count total students across all assigned classes
$total_students = $conn->query("
    SELECT COUNT(DISTINCT u.id) as cnt 
    FROM users u
    JOIN class_assignments ca ON u.course_id = ca.course_id AND u.year = ca.year AND u.section_id = ca.section_id
    WHERE ca.teacher_id = $user_id AND u.role = 'student'
")->fetch_assoc()['cnt'];

$today_attendance = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE date = CURDATE() AND marked_by = $user_id")->fetch_assoc()['cnt'];

// Recent reports
$recent_reports = $conn->query("
    SELECT r.*, s.subject_name 
    FROM reports r 
    JOIN subjects s ON r.subject_id = s.subject_id 
    WHERE r.teacher_id = $user_id 
    ORDER BY r.created_at DESC LIMIT 5
");

// Upcoming assignments
$upcoming = $conn->query("SELECT * FROM assignments WHERE teacher_id = $user_id AND deadline >= CURDATE() ORDER BY deadline ASC LIMIT 5");

// Assigned classes
$assigned_classes = $conn->query("
    SELECT c.course_name, sec.year, sec.section_name, sub.subject_name 
    FROM class_assignments ca
    JOIN courses c ON ca.course_id = c.course_id
    JOIN sections sec ON ca.section_id = sec.section_id
    JOIN subjects sub ON ca.subject_id = sub.subject_id
    WHERE ca.teacher_id = $user_id
");
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

            <!-- Profile Info Alert/Card -->
            <div style="background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%); padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 5px solid #3b82f6;">
                <h2 style="margin: 0; color: #1e3a8a; font-size: 20px; margin-bottom: 5px;">👨‍🏫 <?php echo htmlspecialchars($teacher_name); ?></h2>
                <div style="color: #475569; font-size: 15px; display: flex; gap: 20px;">
                    <span><strong>Department:</strong> <?php echo htmlspecialchars($department_name); ?></span>
                    <span><strong>Primary Subject:</strong> <?php echo htmlspecialchars($principal_subject); ?></span>
                </div>
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

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <!-- Assigned Classes -->
                <div class="card">
                    <h3>👨‍🏫 Assigned Classes</h3>
                    <?php if ($assigned_classes && $assigned_classes->num_rows > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php while ($cls = $assigned_classes->fetch_assoc()): ?>
                            <div style="padding: 12px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #10b981;">
                                <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($cls['course_name']) . ' - ' . htmlspecialchars($cls['year']); ?></div>
                                <div style="font-size: 13px; color: #64748b; margin-top: 4px;">Section: <?php echo htmlspecialchars($cls['section_name']); ?> • Subject: <?php echo htmlspecialchars($cls['subject_name']); ?></div>
                            </div>
                        <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state"><p>No classes assigned yet</p></div>
                    <?php endif; ?>
                </div>

                <!-- Recent Reports -->
                <div class="card">
                    <h3>📚 Recent Reports</h3>
                    <?php if ($recent_reports && $recent_reports->num_rows > 0): ?>
                        <?php while ($r = $recent_reports->fetch_assoc()): ?>
                        <div style="padding:10px 0;border-bottom:1px solid #e2e8f0;">
                            <strong style="color:#3b82f6;"><?php echo htmlspecialchars($r['subject_name']); ?></strong>
                            <span style="float:right;font-size:12px;color:#94a3b8;"><?php echo date('M d', strtotime($r['date'])); ?></span>
                            <div style="font-size:13px;color:#555;margin-top:3px;"><?php echo htmlspecialchars($r['topic']); ?></div>
                        </div>
                        <?php endwhile; ?>
                        <a href="/ClassSync/teacher/reports.php" class="btn btn-blue btn-sm mt-2" style="display:inline-block;">View All</a>
                    <?php else: ?>
                        <div class="empty-state"><p>No reports yet</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

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
