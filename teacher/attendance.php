<?php
$page_title = 'Attendance';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('teacher');

$active_page = 'attendance';
$user_id = $_SESSION['user_id'];

$selected_date = $_GET['date'] ?? date('Y-m-d');

// Get all students
$students = $conn->query("SELECT * FROM users WHERE role = 'student' ORDER BY name ASC");

// Get existing attendance for selected date
$existing = [];
$att_query = $conn->query("SELECT student_id, status FROM attendance WHERE date = '$selected_date'");
while ($row = $att_query->fetch_assoc()) {
    $existing[$row['student_id']] = $row['status'];
}

// Get attendance history (last 7 dates)
$history = $conn->query("
    SELECT a.date, 
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
        SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
        COUNT(*) as total
    FROM attendance a
    WHERE a.marked_by = $user_id
    GROUP BY a.date
    ORDER BY a.date DESC
    LIMIT 7
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
                    <h1>Mark Attendance</h1>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Attendance marked successfully!</div>
            <?php endif; ?>

            <!-- Date Selector -->
            <form method="GET" class="filter-bar">
                <label style="font-weight:600;font-size:14px;">Select Date:</label>
                <input type="date" name="date" value="<?php echo $selected_date; ?>">
                <button type="submit" class="btn btn-blue btn-sm">Load</button>
            </form>

            <!-- Attendance Form -->
            <div class="dashboard-form">
                <h3>📋 Attendance for <?php echo date('M d, Y', strtotime($selected_date)); ?></h3>
                <form method="POST" action="/ClassSync/actions/attendance_action.php">
                    <input type="hidden" name="action" value="mark_attendance">
                    <input type="hidden" name="date" value="<?php echo $selected_date; ?>">

                    <?php if ($students->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Reg No.</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $students->data_seek(0);
                                while ($s = $students->fetch_assoc()):
                                    $current_status = $existing[$s['id']] ?? 'present';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($s['name']); ?></td>
                                    <td><?php echo $s['registration_number']; ?></td>
                                    <td><input type="radio" name="attendance[<?php echo $s['id']; ?>]" value="present" <?php echo $current_status === 'present' ? 'checked' : ''; ?>></td>
                                    <td><input type="radio" name="attendance[<?php echo $s['id']; ?>]" value="absent" <?php echo $current_status === 'absent' ? 'checked' : ''; ?>></td>
                                    <td><input type="radio" name="attendance[<?php echo $s['id']; ?>]" value="late" <?php echo $current_status === 'late' ? 'checked' : ''; ?>></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-green">Save Attendance</button>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">👤</div>
                        <p>No students registered yet</p>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Attendance History -->
            <div class="card">
                <h3>📊 Recent Attendance History</h3>
                <?php if ($history->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($h = $history->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <a href="?date=<?php echo $h['date']; ?>" style="color:#3b82f6;">
                                        <?php echo date('M d, Y', strtotime($h['date'])); ?>
                                    </a>
                                </td>
                                <td><span class="badge badge-present"><?php echo $h['present_count']; ?></span></td>
                                <td><span class="badge badge-absent"><?php echo $h['absent_count']; ?></span></td>
                                <td><span class="badge badge-late"><?php echo $h['late_count']; ?></span></td>
                                <td><?php echo $h['total']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state"><p>No attendance history yet</p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
