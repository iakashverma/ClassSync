<?php
$page_title = 'Academic Timeline';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('student');

$active_page = 'timeline';
$user_id = $_SESSION['user_id'];

// Filter by subject
$filter_subject = $_GET['subject'] ?? '';

$query = "SELECT r.*, u.name as teacher_name FROM reports r JOIN users u ON r.teacher_id = u.id";
if ($filter_subject) {
    $query .= " WHERE r.subject = '" . $conn->real_escape_string($filter_subject) . "'";
}
$query .= " ORDER BY r.date DESC";

$reports = $conn->query($query);

// Get unique subjects
$subjects = $conn->query("SELECT DISTINCT subject FROM reports ORDER BY subject ASC");

// Group reports by date
$grouped = [];
while ($r = $reports->fetch_assoc()) {
    $date = $r['date'];
    if (!isset($grouped[$date])) {
        $grouped[$date] = [];
    }
    $grouped[$date][] = $r;
}

// Get attendance for these dates
$attendance_map = [];
$att_q = $conn->query("SELECT date, status FROM attendance WHERE student_id = $user_id");
while ($a = $att_q->fetch_assoc()) {
    $attendance_map[$a['date']] = $a['status'];
}
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
                    <h1>📅 Academic Timeline</h1>
                </div>
            </div>

            <!-- Filter -->
            <form method="GET" class="filter-bar">
                <select name="subject">
                    <option value="">All Subjects</option>
                    <?php while ($s = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($s['subject']); ?>" <?php echo $filter_subject === $s['subject'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['subject']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn btn-blue btn-sm">Filter</button>
                <a href="/ClassSync/student/timeline.php" class="btn btn-sm" style="background:#e2e8f0;color:#333;">Clear</a>
            </form>

            <!-- Timeline -->
            <?php if (!empty($grouped)): ?>
            <div class="timeline">
                <?php foreach ($grouped as $date => $day_reports): ?>
                    <div class="timeline-item">
                        <div class="timeline-date">
                            <?php echo date('l, M d, Y', strtotime($date)); ?>
                            <?php
                            $att_status = $attendance_map[$date] ?? null;
                            if ($att_status === 'absent'): ?>
                                <span class="badge badge-absent" style="margin-left:8px;">Absent</span>
                            <?php elseif ($att_status === 'present'): ?>
                                <span class="badge badge-present" style="margin-left:8px;">Present</span>
                            <?php elseif ($att_status === 'late'): ?>
                                <span class="badge badge-late" style="margin-left:8px;">Late</span>
                            <?php endif; ?>
                        </div>

                        <?php foreach ($day_reports as $r): ?>
                        <div style="padding:8px 0;<?php echo count($day_reports) > 1 ? 'border-bottom:1px solid #f1f5f9;' : ''; ?>">
                            <div class="timeline-subject"><?php echo htmlspecialchars($r['subject']); ?></div>
                            <div class="timeline-topic"><?php echo htmlspecialchars($r['topic']); ?></div>
                            <?php if (!empty($r['description'])): ?>
                                <p style="font-size:13px;color:#555;margin-top:5px;"><?php echo htmlspecialchars(substr($r['description'], 0, 150)); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($r['homework'])): ?>
                                <div style="font-size:12px;color:#d97706;margin-top:5px;">📝 HW: <?php echo htmlspecialchars($r['homework']); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="empty-state">
                    <div class="empty-icon">📅</div>
                    <p>No timeline data available</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
