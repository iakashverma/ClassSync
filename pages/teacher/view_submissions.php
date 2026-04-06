<?php
/**
 * ClassSync - Teacher: View Submissions for a Classwork
 */
$pageTitle = 'View Submissions';
require_once __DIR__ . '/../../includes/header.php';
requireRole('teacher');

$teacherId = $_SESSION['user_id'];
$classworkId = (int)($_GET['id'] ?? 0);

if ($classworkId <= 0) {
    header('Location: ' . BASE_URL . '/pages/teacher/my_classwork.php');
    exit;
}

// Get classwork details
$stmt = $pdo->prepare("
    SELECT c.*, s.subject_name
    FROM classwork c
    JOIN subjects s ON c.subject_id = s.id
    WHERE c.id = ? AND c.teacher_id = ?
");
$stmt->execute([$classworkId, $teacherId]);
$classwork = $stmt->fetch();

if (!$classwork) {
    setFlash('error', 'Classwork not found.');
    header('Location: ' . BASE_URL . '/pages/teacher/my_classwork.php');
    exit;
}

// Get all students and their submission status
$students = $pdo->query("SELECT id, name, email, reg_number FROM users WHERE role = 'student' ORDER BY name")->fetchAll();

$submissions = $pdo->prepare("SELECT * FROM submissions WHERE classwork_id = ?");
$submissions->execute([$classworkId]);
$submissionMap = [];
foreach ($submissions->fetchAll() as $sub) {
    $submissionMap[$sub['student_id']] = $sub;
}

$deadlinePassed = isDeadlinePassed($classwork['deadline']);

// Count stats
$submittedCount = count($submissionMap);
$totalStudents = count($students);
$missedCount = $deadlinePassed ? ($totalStudents - $submittedCount) : 0;
$pendingCount = $deadlinePassed ? 0 : ($totalStudents - $submittedCount);
?>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1>📊 Submissions: <?php echo e($classwork['topic']); ?></h1>
            <p>
                📚 <?php echo e($classwork['subject_name']); ?> &nbsp;|&nbsp;
                📅 Deadline: <?php echo formatDeadline($classwork['deadline']); ?>
                &nbsp;
                <?php if ($deadlinePassed): ?>
                    <span class="badge badge-closed">⏰ Expired</span>
                <?php else: ?>
                    <span class="countdown" data-deadline="<?php echo $classwork['deadline']; ?>"></span>
                <?php endif; ?>
            </p>
        </div>
        <a href="<?php echo BASE_URL; ?>/pages/teacher/my_classwork.php" class="btn btn-secondary">
            ← Back
        </a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card accent-blue">
        <div class="stat-icon">🎓</div>
        <div class="stat-value"><?php echo $totalStudents; ?></div>
        <div class="stat-label">Total Students</div>
    </div>
    <div class="stat-card accent-green">
        <div class="stat-icon">✅</div>
        <div class="stat-value"><?php echo $submittedCount; ?></div>
        <div class="stat-label">Submitted</div>
    </div>
    <div class="stat-card accent-red">
        <div class="stat-icon">❌</div>
        <div class="stat-value"><?php echo $missedCount; ?></div>
        <div class="stat-label">Missed</div>
    </div>
    <div class="stat-card accent-yellow">
        <div class="stat-icon">⏳</div>
        <div class="stat-value"><?php echo $pendingCount; ?></div>
        <div class="stat-label">Pending</div>
    </div>
</div>

<!-- Students Table -->
<div class="card">
    <div class="card-header">
        <h2>👥 Student Submissions</h2>
        <?php if ($submittedCount > 0): ?>
        <a href="<?php echo BASE_URL; ?>/pages/teacher/download.php?classwork_id=<?php echo $classworkId; ?>&all=1"
           class="btn btn-sm btn-success">
            📥 Download All
        </a>
        <?php endif; ?>
    </div>

    <?php if (empty($students)): ?>
        <div class="empty-state">
            <div class="empty-icon">🎓</div>
            <h3>No students</h3>
            <p>No students are registered in the system yet.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Reg No</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>File</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $i => $student): ?>
                    <?php
                    $sub = $submissionMap[$student['id']] ?? null;
                    $status = 'pending';
                    if ($sub) {
                        $status = $sub['status'];
                    } elseif ($deadlinePassed) {
                        $status = 'missed';
                    }
                    ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td>
                            <strong><?php echo e($student['name']); ?></strong>
                            <br><small style="color:var(--text-muted)"><?php echo e($student['email']); ?></small>
                        </td>
                        <td><?php echo $student['reg_number'] ? e($student['reg_number']) : '—'; ?></td>
                        <td>
                            <?php if ($status === 'submitted'): ?>
                                <span class="badge badge-submitted">🟢 Submitted</span>
                            <?php elseif ($status === 'late'): ?>
                                <span class="badge badge-late">🟠 Late</span>
                            <?php elseif ($status === 'missed'): ?>
                                <span class="badge badge-missed">🔴 Missed</span>
                            <?php else: ?>
                                <span class="badge badge-pending">🟡 Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $sub ? timeAgo($sub['submitted_at']) : '—'; ?>
                        </td>
                        <td>
                            <?php if ($sub): ?>
                                <div class="file-info" style="padding: 6px 10px;">
                                    <span class="file-icon"><?php echo pathinfo($sub['original_filename'], PATHINFO_EXTENSION) === 'pdf' ? '📄' : '📝'; ?></span>
                                    <div class="file-details">
                                        <div class="file-name" style="font-size: 0.75rem;"><?php echo e($sub['original_filename']); ?></div>
                                        <div class="file-size"><?php echo formatFileSize($sub['file_size']); ?></div>
                                    </div>
                                </div>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($sub): ?>
                                <a href="<?php echo BASE_URL; ?>/pages/teacher/download.php?id=<?php echo $sub['id']; ?>"
                                   class="btn btn-sm btn-secondary" title="Download">
                                    📥
                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
