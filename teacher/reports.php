<?php
$page_title = 'Class Reports';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('teacher');

$active_page = 'reports';
$user_id = $_SESSION['user_id'];

// Check for edit mode
$edit_report = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM reports WHERE report_id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $edit_report = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get all reports by this teacher
$reports = $conn->query("SELECT * FROM reports WHERE teacher_id = $user_id ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Class Sync</title>
    <link rel="stylesheet" href="/ClassSync/assets/css/style.css">
    <link rel="stylesheet" href="/ClassSync/assets/css/dashboard.css">
    <link rel="stylesheet" href="/ClassSync/assets/css/auth.css">
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

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Report <?php echo htmlspecialchars($_GET['success']); ?> successfully!</div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_GET['error'] === 'required' ? 'Subject and Topic are required.' : 'Something went wrong.'; ?>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Report Form -->
            <div class="dashboard-form">
                <h3><?php echo $edit_report ? '✏️ Edit Report' : '➕ Add Daily Report'; ?></h3>
                <form method="POST" action="/ClassSync/actions/report_action.php">
                    <input type="hidden" name="action" value="<?php echo $edit_report ? 'edit_report' : 'add_report'; ?>">
                    <?php if ($edit_report): ?>
                        <input type="hidden" name="report_id" value="<?php echo $edit_report['report_id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" placeholder="e.g. Database Management" value="<?php echo $edit_report ? htmlspecialchars($edit_report['subject']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Topic</label>
                            <input type="text" name="topic" placeholder="e.g. Introduction to SQL" value="<?php echo $edit_report ? htmlspecialchars($edit_report['topic']) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" placeholder="What was covered in class today..."><?php echo $edit_report ? htmlspecialchars($edit_report['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Homework</label>
                        <textarea name="homework" placeholder="Homework assigned..."><?php echo $edit_report ? htmlspecialchars($edit_report['homework']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" value="<?php echo $edit_report ? $edit_report['date'] : date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-blue"><?php echo $edit_report ? 'Update Report' : 'Add Report'; ?></button>
                        <?php if ($edit_report): ?>
                            <a href="/ClassSync/teacher/reports.php" class="btn" style="background:#e2e8f0;color:#333;">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Reports List -->
            <div class="card">
                <h3>📋 My Reports</h3>
                <?php if ($reports->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Topic</th>
                                <th>Homework</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = $reports->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($r['date'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($r['subject']); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['topic']); ?></td>
                                <td><?php echo htmlspecialchars(substr($r['homework'], 0, 50)) . (strlen($r['homework']) > 50 ? '...' : ''); ?></td>
                                <td>
                                    <a href="?edit=<?php echo $r['report_id']; ?>" class="btn btn-blue btn-sm">Edit</a>
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
                    <p>No reports yet. Add your first daily report above!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
