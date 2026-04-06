<?php
$page_title = 'Admin Dashboard';
$extra_css = ['dashboard.css'];
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('admin');

$active_page = 'dashboard';

// Get stats
$total_teachers = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role='teacher'")->fetch_assoc()['cnt'];
$total_students = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role='student'")->fetch_assoc()['cnt'];
$total_reports = $conn->query("SELECT COUNT(*) as cnt FROM reports")->fetch_assoc()['cnt'];
$total_assignments = $conn->query("SELECT COUNT(*) as cnt FROM assignments")->fetch_assoc()['cnt'];

// Recent reports
$recent_reports = $conn->query("SELECT r.*, u.name as teacher_name FROM reports r JOIN users u ON r.teacher_id = u.id ORDER BY r.created_at DESC LIMIT 5");

// Recent users
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
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
                    <h1>Admin Dashboard</h1>
                </div>
                <span class="breadcrumb">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-value"><?php echo $total_teachers; ?></div>
                    <div class="stat-label">Teachers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-value"><?php echo $total_students; ?></div>
                    <div class="stat-label">Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?php echo $total_reports; ?></div>
                    <div class="stat-label">Reports</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-value"><?php echo $total_assignments; ?></div>
                    <div class="stat-label">Assignments</div>
                </div>
            </div>

            <!-- Recent Reports -->
            <div class="card">
                <h3>📚 Recent Reports</h3>
                <?php if ($recent_reports->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Topic</th>
                                <th>Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($report = $recent_reports->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($report['date'])); ?></td>
                                <td><?php echo htmlspecialchars($report['subject']); ?></td>
                                <td><?php echo htmlspecialchars($report['topic']); ?></td>
                                <td><?php echo htmlspecialchars($report['teacher_name']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p>No reports yet</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Users -->
            <div class="card">
                <h3>👥 Recent Users</h3>
                <?php if ($recent_users->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Reg No.</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($u = $recent_users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['name']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><span class="badge badge-info"><?php echo ucfirst($u['role']); ?></span></td>
                                <td><?php echo $u['registration_number'] ?? '-'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">👤</div>
                    <p>No users registered yet</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
