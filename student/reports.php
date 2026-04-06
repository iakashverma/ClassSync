<?php
$page_title = 'Class Reports';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('student');

$active_page = 'reports';
$user_id = $_SESSION['user_id'];

// Filters
$filter_subject = $_GET['subject'] ?? '';
$filter_date = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT r.*, u.name as teacher_name FROM reports r JOIN users u ON r.teacher_id = u.id WHERE 1=1";
$params = [];
$types = "";

if ($filter_subject) {
    $query .= " AND r.subject = ?";
    $params[] = $filter_subject;
    $types .= "s";
}
if ($filter_date) {
    $query .= " AND r.date = ?";
    $params[] = $filter_date;
    $types .= "s";
}
if ($search) {
    $search_param = "%$search%";
    $query .= " AND (r.subject LIKE ? OR r.topic LIKE ? OR r.description LIKE ?)";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$query .= " ORDER BY r.date DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reports = $stmt->get_result();

// Get unique subjects for filter dropdown
$subjects = $conn->query("SELECT DISTINCT subject FROM reports ORDER BY subject ASC");

// Get student's attendance to find missed classes
$my_absences = $conn->query("
    SELECT a.date FROM attendance a 
    WHERE a.student_id = $user_id AND a.status = 'absent'
    ORDER BY a.date DESC
");
$missed_dates = [];
while ($row = $my_absences->fetch_assoc()) {
    $missed_dates[] = $row['date'];
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
                    <h1>Class Reports</h1>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="filter-bar">
                <input type="text" name="search" placeholder="Search topics, subjects..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="subject">
                    <option value="">All Subjects</option>
                    <?php while ($s = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($s['subject']); ?>" <?php echo $filter_subject === $s['subject'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['subject']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="date" name="date" value="<?php echo $filter_date; ?>">
                <button type="submit" class="btn btn-blue btn-sm">Search</button>
                <a href="/ClassSync/student/reports.php" class="btn btn-sm" style="background:#e2e8f0;color:#333;">Clear</a>
            </form>

            <!-- Missed Class Alert -->
            <?php if (!empty($missed_dates)): ?>
            <div class="alert alert-warning">
                ⚠️ <strong>Missed Classes:</strong> You were absent on <?php echo count($missed_dates); ?> day(s). 
                <a href="?date=<?php echo $missed_dates[0]; ?>" style="color:#92400e;font-weight:600;">View missed lectures →</a>
            </div>
            <?php endif; ?>

            <!-- Reports -->
            <?php if ($reports->num_rows > 0): ?>
                <?php while ($r = $reports->fetch_assoc()):
                    $is_missed = in_array($r['date'], $missed_dates);
                ?>
                <div class="report-card" style="<?php echo $is_missed ? 'border-left-color:#ef4444;background:#fef2f2;' : ''; ?>">
                    <?php if ($is_missed): ?>
                        <div style="font-size:11px;color:#ef4444;font-weight:600;margin-bottom:8px;">⚠️ MISSED CLASS - Review notes & homework</div>
                    <?php endif; ?>
                    <div class="report-header">
                        <span class="report-subject"><?php echo htmlspecialchars($r['subject']); ?></span>
                        <span class="report-date"><?php echo date('M d, Y', strtotime($r['date'])); ?></span>
                    </div>
                    <div class="report-topic"><?php echo htmlspecialchars($r['topic']); ?></div>
                    <div class="report-desc"><?php echo nl2br(htmlspecialchars($r['description'])); ?></div>
                    <?php if (!empty($r['homework'])): ?>
                    <div class="report-homework">
                        <strong>📝 Homework:</strong>
                        <?php echo htmlspecialchars($r['homework']); ?>
                    </div>
                    <?php endif; ?>
                    <div style="font-size:12px;color:#94a3b8;margin-top:10px;">By: <?php echo htmlspecialchars($r['teacher_name']); ?></div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <p>No reports found matching your filters</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
