<?php
$page_title = 'Manage Reports';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('admin');

$active_page = 'reports';

// Get all reports with teacher name
$reports = $conn->query("
    SELECT r.*, u.name as teacher_name, s.subject_name 
    FROM reports r 
    JOIN users u ON r.teacher_id = u.id 
    JOIN subjects s ON r.subject_id = s.subject_id 
    ORDER BY r.date DESC
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
                    <h1>All Reports</h1>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Report <?php echo htmlspecialchars($_GET['success']); ?> successfully!</div>
            <?php endif; ?>

            <div class="card">
                <?php if ($reports->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Topic</th>
                                <th>Description</th>
                                <th>Homework</th>
                                <th>Teacher</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = $reports->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($r['date'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($r['subject_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['topic']); ?></td>
                                <td><?php echo htmlspecialchars(substr($r['description'], 0, 80)) . (strlen($r['description']) > 80 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars(substr($r['homework'], 0, 60)) . (strlen($r['homework']) > 60 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($r['teacher_name']); ?></td>
                                <td>
                                    <form method="POST" action="/ClassSync/actions/report_action.php" style="display:inline;" onsubmit="return confirmDelete('Delete this report?')">
                                        <input type="hidden" name="action" value="delete_report">
                                        <input type="hidden" name="report_id" value="<?php echo $r['report_id']; ?>">
                                        <button type="submit" class="btn btn-red btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p>No reports found</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
