<?php
$page_title = 'Manage Attendance';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('admin');

$active_page = 'attendance';

// Get attendance with student names
$filter_date = $_GET['date'] ?? '';
$query = "SELECT a.*, u.name as student_name, u.registration_number FROM attendance a JOIN users u ON a.student_id = u.id";
if ($filter_date) {
    $query .= " WHERE a.date = '" . $conn->real_escape_string($filter_date) . "'";
}
$query .= " ORDER BY a.date DESC, u.name ASC";
$attendance = $conn->query($query);

// Get unique dates for filter
$dates = $conn->query("SELECT DISTINCT date FROM attendance ORDER BY date DESC");
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
                    <h1>Attendance Records</h1>
                </div>
            </div>

            <!-- Filter -->
            <form method="GET" class="filter-bar">
                <label style="font-weight:600;font-size:14px;">Filter by Date:</label>
                <input type="date" name="date" value="<?php echo $filter_date; ?>">
                <button type="submit" class="btn btn-blue btn-sm">Filter</button>
                <a href="/ClassSync/admin/manage_attendance.php" class="btn btn-sm" style="background:#e2e8f0;color:#333;">Clear</a>
            </form>

            <div class="card">
                <?php if ($attendance->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Reg No.</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($a = $attendance->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($a['date'])); ?></td>
                                <td><?php echo htmlspecialchars($a['student_name']); ?></td>
                                <td><?php echo $a['registration_number']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $a['status']; ?>">
                                        <?php echo ucfirst($a['status']); ?>
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
