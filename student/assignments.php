<?php
$page_title = 'Assignments';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('student');

$active_page = 'assignments';
$user_id = $_SESSION['user_id'];

// Get all assignments with student's submission status
$assignments = $conn->query("
    SELECT a.*, u.name as teacher_name,
        s.id as submission_id, s.status as submission_status, s.submission_text, s.file_path, s.submitted_at
    FROM assignments a
    JOIN users u ON a.teacher_id = u.id
    LEFT JOIN submissions s ON s.assignment_id = a.id AND s.student_id = $user_id
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
                    <?php
                    $err_msgs = [
                        'nofile' => 'Please select a file to upload.',
                        'filetype' => 'Only PDF, DOC, and DOCX files are allowed.',
                        'filesize' => 'File size must be under 10MB.',
                        'upload' => 'File upload failed. Please try again.',
                        'failed' => 'Something went wrong. Please try again.'
                    ];
                    echo $err_msgs[$_GET['error']] ?? 'Something went wrong.';
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($assignments->num_rows > 0): ?>
                <?php while ($a = $assignments->fetch_assoc()):
                    $is_expired = $a['deadline'] < date('Y-m-d');
                    $has_submitted = !empty($a['submission_id']);
                ?>
                <div class="card" style="border-left:4px solid <?php
                    echo $has_submitted ? '#22c55e' : ($is_expired ? '#ef4444' : '#3b82f6');
                ?>;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <div>
                            <h3 style="margin-bottom:5px;"><?php echo htmlspecialchars($a['title']); ?></h3>
                            <span style="font-size:12px;color:#94a3b8;">By: <?php echo htmlspecialchars($a['teacher_name']); ?></span>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:13px;color:#64748b;">Deadline: <?php echo date('M d, Y', strtotime($a['deadline'])); ?></div>
                            <?php if ($has_submitted): ?>
                                <span class="badge badge-<?php echo $a['submission_status']; ?>"><?php echo ucfirst($a['submission_status']); ?></span>
                            <?php elseif ($is_expired): ?>
                                <span class="badge badge-absent">Expired</span>
                            <?php else: ?>
                                <span class="badge badge-pending">Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($a['description'])): ?>
                    <p style="color:#555;font-size:14px;margin:12px 0;line-height:1.6;"><?php echo nl2br(htmlspecialchars($a['description'])); ?></p>
                    <?php endif; ?>

                    <?php if ($has_submitted): ?>
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:12px;border-radius:6px;margin-top:10px;">
                            <strong style="color:#166534;font-size:13px;">✅ Your Submission:</strong>
                            <div style="display:flex;align-items:center;gap:10px;margin-top:8px;">
                                <span style="font-size:22px;"><?php
                                    $ext = strtolower(pathinfo($a['file_path'] ?? '', PATHINFO_EXTENSION));
                                    echo $ext === 'pdf' ? '📄' : '📝';
                                ?></span>
                                <div>
                                    <p style="color:#333;font-size:13px;font-weight:600;"><?php echo htmlspecialchars($a['submission_text']); ?></p>
                                    <span style="font-size:11px;color:#94a3b8;">Submitted: <?php echo date('M d, Y h:i A', strtotime($a['submitted_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php elseif (!$is_expired): ?>
                        <!-- File Upload Form -->
                        <form method="POST" action="/ClassSync/actions/assignment_action.php" enctype="multipart/form-data" style="margin-top:12px;">
                            <input type="hidden" name="action" value="submit_assignment">
                            <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
                            <div class="form-group">
                                <label style="font-size:13px;">Upload File (PDF, DOC, or DOCX only)</label>
                                <input type="file" name="submission_file" accept=".pdf,.doc,.docx" required
                                    style="padding:8px;border:1px solid #d1d5db;border-radius:6px;width:100%;font-size:13px;">
                                <span class="input-hint">Maximum file size: 10MB</span>
                            </div>
                            <button type="submit" class="btn btn-green btn-sm">📤 Upload & Submit</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card">
                    <div class="empty-state">
                        <div class="empty-icon">📝</div>
                        <p>No assignments available</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
