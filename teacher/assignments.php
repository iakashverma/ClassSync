<?php
$page_title = 'Assignments';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('teacher');

$active_page = 'assignments';
$user_id = $_SESSION['user_id'];

// Get my assignments with submission count
$assignments = $conn->query("
    SELECT a.*,
        (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id) as submission_count,
        (SELECT COUNT(*) FROM users WHERE role='student') as total_students
    FROM assignments a
    WHERE a.teacher_id = $user_id
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
    <link rel="stylesheet" href="/ClassSync/assets/css/auth.css">
</head>
<body>
    <div class="dashboard-page">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="top-bar">
                <div>
                    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('active')">☰</button>
                    <h1>Assignments</h1>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Assignment <?php echo htmlspecialchars($_GET['success']); ?> successfully!</div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_GET['error'] === 'required' ? 'Title and Deadline are required.' : 'Something went wrong.'; ?>
                </div>
            <?php endif; ?>

            <!-- Create Assignment Form -->
            <div class="dashboard-form">
                <h3>➕ Create Assignment</h3>
                <form method="POST" action="/ClassSync/actions/assignment_action.php">
                    <input type="hidden" name="action" value="create_assignment">

                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" placeholder="Assignment title" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" placeholder="Assignment details and instructions..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Deadline</label>
                        <input type="date" name="deadline" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-blue">Create Assignment</button>
                    </div>
                </form>
            </div>

            <!-- Assignments List -->
            <div class="card">
                <h3>📋 My Assignments</h3>
                <?php if ($assignments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Deadline</th>
                                <th>Submissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($a = $assignments->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($a['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($a['description'], 0, 60)) . (strlen($a['description']) > 60 ? '...' : ''); ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($a['deadline'])); ?>
                                    <?php if ($a['deadline'] < date('Y-m-d')): ?>
                                        <span class="badge badge-absent">Expired</span>
                                    <?php else: ?>
                                        <span class="badge badge-present">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $a['submission_count']; ?> / <?php echo $a['total_students']; ?></td>
                                <td>
                                    <a href="?view=<?php echo $a['id']; ?>" class="btn btn-blue btn-sm">View</a>
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
                    <p>No assignments yet. Create one above!</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- View Submissions -->
            <?php if (isset($_GET['view'])):
                $view_id = intval($_GET['view']);
                $subs = $conn->query("
                    SELECT s.*, u.name as student_name, u.registration_number
                    FROM submissions s
                    JOIN users u ON s.student_id = u.id
                    WHERE s.assignment_id = $view_id
                    ORDER BY s.submitted_at DESC
                ");
                $total_subs = $subs->num_rows;
                $assign_info = $conn->query("SELECT title FROM assignments WHERE id = $view_id")->fetch_assoc();
            ?>
            <div class="card">
                <h3>📩 Submissions for: <?php echo htmlspecialchars($assign_info['title'] ?? ''); ?> <span style="font-size:14px;color:#64748b;font-weight:normal;">(<?php echo $total_subs; ?> received)</span></h3>
                <?php if ($total_subs > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Reg No.</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Submitted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($sub = $subs->fetch_assoc()):
                                $file_ext = strtolower(pathinfo($sub['file_path'] ?? '', PATHINFO_EXTENSION));
                                $file_icon = ($file_ext === 'pdf') ? '📄' : '📝';
                                $original_name = !empty($sub['submission_text']) ? $sub['submission_text'] : basename($sub['file_path'] ?? 'N/A');
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sub['student_name']); ?></td>
                                <td><?php echo $sub['registration_number']; ?></td>
                                <td>
                                    <span style="font-size:16px;"><?php echo $file_icon; ?></span>
                                    <?php echo htmlspecialchars($original_name); ?>
                                    <div style="font-size:11px;color:#94a3b8;"><?php echo strtoupper($file_ext); ?> file</div>
                                </td>
                                <td><span class="badge badge-<?php echo $sub['status']; ?>"><?php echo ucfirst($sub['status']); ?></span></td>
                                <td><?php echo $sub['submitted_at'] ? date('M d, Y h:i A', strtotime($sub['submitted_at'])) : '-'; ?></td>
                                <td>
                                    <?php if (!empty($sub['file_path'])): ?>
                                        <a href="/ClassSync/<?php echo $sub['file_path']; ?>" target="_blank" class="btn btn-blue btn-sm" style="margin-bottom:4px;">👁 View</a>
                                        <a href="/ClassSync/<?php echo $sub['file_path']; ?>" download="<?php echo htmlspecialchars($original_name); ?>" class="btn btn-green btn-sm">⬇ Download</a>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;">No file</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state"><p>No submissions yet</p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
