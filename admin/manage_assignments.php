<?php
$page_title = 'Manage Assignments';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('admin');

$active_page = 'assignments';

// Get all assignments with teacher name and submission count
$assignments = $conn->query("
    SELECT a.*, u.name as teacher_name,
        (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id) as submission_count,
        (SELECT COUNT(*) FROM users WHERE role='student') as total_students
    FROM assignments a
    JOIN users u ON a.teacher_id = u.id
    ORDER BY a.deadline DESC
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
                    <h1>All Assignments</h1>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Assignment <?php echo htmlspecialchars($_GET['success']); ?> successfully!</div>
            <?php endif; ?>

            <div class="card">
                <?php if ($assignments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Deadline</th>
                                <th>Teacher</th>
                                <th>Submissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($a = $assignments->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($a['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($a['description'], 0, 80)) . (strlen($a['description']) > 80 ? '...' : ''); ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($a['deadline'])); ?>
                                    <?php if ($a['deadline'] < date('Y-m-d')): ?>
                                        <span class="badge badge-absent">Expired</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($a['teacher_name']); ?></td>
                                <td><?php echo $a['submission_count']; ?> / <?php echo $a['total_students']; ?></td>
                                <td>
                                    <form method="POST" action="/ClassSync/actions/assignment_action.php" style="display:inline;" onsubmit="return confirmDelete('Delete this assignment?')">
                                        <input type="hidden" name="action" value="delete_assignment">
                                        <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
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
                    <div class="empty-icon">📝</div>
                    <p>No assignments found</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
