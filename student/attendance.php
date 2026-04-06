<?php
$page_title = 'My Attendance';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('student');

$active_page = 'attendance';
$user_id = $_SESSION['user_id'];

// Attendance stats
$att_total = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = $user_id")->fetch_assoc()['cnt'];
$att_present = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = $user_id AND status = 'present'")->fetch_assoc()['cnt'];
$att_absent = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = $user_id AND status = 'absent'")->fetch_assoc()['cnt'];
$att_late = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE student_id = $user_id AND status = 'late'")->fetch_assoc()['cnt'];
$att_percentage = $att_total > 0 ? round(($att_present / $att_total) * 100, 1) : 0;

// Determine status class
$status_class = 'good';
if ($att_percentage < 75) $status_class = 'danger';
elseif ($att_percentage < 85) $status_class = 'warning';

// Attendance records
$records = $conn->query("SELECT * FROM attendance WHERE student_id = $user_id ORDER BY date DESC");
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
                    <h1>My Attendance</h1>
                </div>
            </div>

            <?php if ($att_percentage < 75 && $att_total > 0): ?>
            <div class="alert alert-error">
                ⚠️ <strong>Low Attendance Alert!</strong> Your attendance is <?php echo $att_percentage; ?>%, which is below the required 75%. Please attend classes regularly.
            </div>
            <?php endif; ?>

            <!-- Attendance Overview -->
            <div class="stats-grid">
                <div class="stat-card" style="text-align:center;">
                    <div class="stat-icon">📊</div>
                    <div class="attendance-percentage <?php echo $status_class; ?>" style="font-size:36px;"><?php echo $att_percentage; ?>%</div>
                    <div class="stat-label">Overall Attendance</div>
                    <div class="attendance-bar" style="margin-top:15px;">
                        <div class="fill <?php echo $status_class; ?>" style="width:<?php echo $att_percentage; ?>%"></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value" style="color:#22c55e;"><?php echo $att_present; ?></div>
                    <div class="stat-label">Present</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">❌</div>
                    <div class="stat-value" style="color:#ef4444;"><?php echo $att_absent; ?></div>
                    <div class="stat-label">Absent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-value" style="color:#f59e0b;"><?php echo $att_late; ?></div>
                    <div class="stat-label">Late</div>
                </div>
            </div>

            <!-- Attendance Records -->
            <div class="card">
                <h3>📋 Attendance History</h3>
                <?php if ($records->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = $records->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($r['date'])); ?></td>
                                <td><?php echo date('l', strtotime($r['date'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $r['status']; ?>">
                                        <?php echo ucfirst($r['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <p>No attendance records found</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
