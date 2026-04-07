<?php
$page_title = 'Attendance';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('teacher');

$active_page = 'attendance';
$user_id = $_SESSION['user_id'];

$selected_date = $_GET['date'] ?? date('Y-m-d');
$selected_class_id = $_GET['class_id'] ?? '';

// Get assigned classes mapped
$assigned_classes = $conn->query("
    SELECT ca.id as class_assignment_id, c.course_id, c.course_name, sec.section_id, sec.year, sec.section_name, sub.subject_id, sub.subject_name 
    FROM class_assignments ca
    JOIN courses c ON ca.course_id = c.course_id
    JOIN sections sec ON ca.section_id = sec.section_id
    JOIN subjects sub ON ca.subject_id = sub.subject_id
    WHERE ca.teacher_id = $user_id
");

$assigned_classes_list = [];
while ($row = $assigned_classes->fetch_assoc()) {
    $assigned_classes_list[] = $row;
}
if (empty($selected_class_id) && count($assigned_classes_list) > 0) {
    $selected_class_id = $assigned_classes_list[0]['class_assignment_id'];
}

$selected_class = null;
foreach ($assigned_classes_list as $c) {
    if ($c['class_assignment_id'] == $selected_class_id) {
        $selected_class = $c;
        break;
    }
}

$c_id = $selected_class['course_id'] ?? 0;
$c_yr = $selected_class['year'] ?? '';
$c_sec = $selected_class['section_id'] ?? 0;

// Get all students
$students_stmt = $conn->prepare("SELECT * FROM users WHERE role = 'student' AND course_id = ? AND year = ? AND section_id = ? ORDER BY name ASC");
$students_stmt->bind_param("isi", $c_id, $c_yr, $c_sec);
$students_stmt->execute();
$students = $students_stmt->get_result();

// Get existing attendance for selected date
$existing = [];
$att_query = $conn->prepare("SELECT student_id, status FROM attendance a JOIN users u ON a.student_id = u.id WHERE date = ? AND u.course_id = ? AND u.year = ? AND u.section_id = ?");
$att_query->bind_param("sisi", $selected_date, $c_id, $c_yr, $c_sec);
$att_query->execute();
$att_result = $att_query->get_result();
while ($row = $att_result->fetch_assoc()) {
    $existing[$row['student_id']] = $row['status'];
}

// Get attendance history (last 7 dates)
$history_stmt = $conn->prepare("
    SELECT a.date, 
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
        SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
        COUNT(*) as total
    FROM attendance a
    JOIN users u ON a.student_id = u.id
    WHERE a.marked_by = ? AND u.course_id = ? AND u.year = ? AND u.section_id = ?
    GROUP BY a.date
    ORDER BY a.date DESC
    LIMIT 7
");
$history_stmt->bind_param("iisi", $user_id, $c_id, $c_yr, $c_sec);
$history_stmt->execute();
$history = $history_stmt->get_result();
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

            <!-- Class and Date Selector -->
            <form method="GET" class="filter-bar">
                <label style="font-weight:600;font-size:14px;">Select Class:</label>
                <select name="class_id">
                    <?php foreach ($assigned_classes_list as $cls): ?>
                        <option value="<?php echo $cls['class_assignment_id']; ?>" <?php echo $selected_class_id == $cls['class_assignment_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cls['course_name'] . ' - ' . $cls['year'] . ' Year (Sec ' . $cls['section_name'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                    <input type="hidden" name="course_id" value="<?php echo $c_id; ?>">
                    <input type="hidden" name="year" value="<?php echo htmlspecialchars($c_yr); ?>">
                    <input type="hidden" name="section_id" value="<?php echo $c_sec; ?>">

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
