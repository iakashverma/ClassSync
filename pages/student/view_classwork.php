<?php
/**
 * ClassSync - Student: View Classwork Details
 */
$pageTitle = 'View Classwork';
require_once __DIR__ . '/../../includes/header.php';
requireRole('student');

$studentId = $_SESSION['user_id'];
$classworkId = (int)($_GET['id'] ?? 0);

if ($classworkId <= 0) {
    header('Location: ' . BASE_URL . '/pages/student/assignments.php');
    exit;
}

// Get classwork
$stmt = $pdo->prepare("
    SELECT c.*, s.subject_name, u.name as teacher_name
    FROM classwork c
    JOIN subjects s ON c.subject_id = s.id
    JOIN users u ON c.teacher_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$classworkId]);
$classwork = $stmt->fetch();

if (!$classwork) {
    setFlash('error', 'Classwork not found.');
    header('Location: ' . BASE_URL . '/pages/student/assignments.php');
    exit;
}

// Get submission status
$status = getSubmissionStatus($pdo, $classworkId, $studentId);
$deadlinePassed = isDeadlinePassed($classwork['deadline']);

// Get existing submission
$subStmt = $pdo->prepare("SELECT * FROM submissions WHERE classwork_id = ? AND student_id = ?");
$subStmt->execute([$classworkId, $studentId]);
$submission = $subStmt->fetch();
?>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1>📋 Classwork Details</h1>
            <p><?php echo e($classwork['subject_name']); ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>/pages/student/assignments.php" class="btn btn-secondary">
            ← Back to Assignments
        </a>
    </div>
</div>

<div style="max-width: 800px;">
    <div class="card" style="margin-bottom: 20px;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
            <div>
                <h2 style="color: var(--text-heading); font-size: 1.4rem; margin-bottom: 4px;">
                    <?php echo e($classwork['topic']); ?>
                </h2>
                <div class="assignment-meta" style="font-size: 0.85rem;">
                    <span>📚 <?php echo e($classwork['subject_name']); ?></span>
                    <span>👨‍🏫 <?php echo e($classwork['teacher_name']); ?></span>
                    <span>📅 Created: <?php echo timeAgo($classwork['created_at']); ?></span>
                </div>
            </div>
            <div style="text-align: right;">
                <?php if ($status === 'submitted'): ?>
                    <span class="badge badge-submitted" style="font-size: 0.85rem; padding: 6px 16px;">🟢 Submitted</span>
                <?php elseif ($status === 'missed'): ?>
                    <span class="badge badge-missed" style="font-size: 0.85rem; padding: 6px 16px;">🔴 Missed</span>
                <?php else: ?>
                    <span class="badge badge-pending" style="font-size: 0.85rem; padding: 6px 16px;">🟡 Pending</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Deadline -->
        <div style="background: var(--bg-input); border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <div>
                <span style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Deadline</span>
                <div style="font-size: 1.1rem; font-weight: 600; color: var(--text-heading);">
                    <?php echo formatDeadline($classwork['deadline']); ?>
                </div>
            </div>
            <?php if ($deadlinePassed): ?>
                <span class="badge badge-closed" style="font-size: 0.85rem; padding: 6px 16px;">⏰ Expired</span>
            <?php else: ?>
                <span class="countdown" data-deadline="<?php echo $classwork['deadline']; ?>" style="font-size: 0.9rem;"></span>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <?php if ($classwork['description']): ?>
        <div style="margin-bottom: 20px;">
            <h4 style="color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                Description
            </h4>
            <div style="color: var(--text-primary); line-height: 1.8; font-size: 0.95rem;">
                <?php echo nl2br(e($classwork['description'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Submission Info -->
        <?php if ($submission): ?>
        <div style="border-top: 1px solid var(--border-subtle); padding-top: 20px;">
            <h4 style="color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">
                Your Submission
            </h4>
            <div class="file-info">
                <span class="file-icon">
                    <?php echo pathinfo($submission['original_filename'], PATHINFO_EXTENSION) === 'pdf' ? '📄' : '📝'; ?>
                </span>
                <div class="file-details">
                    <div class="file-name"><?php echo e($submission['original_filename']); ?></div>
                    <div class="file-size">
                        <?php echo formatFileSize($submission['file_size']); ?> &nbsp;•&nbsp;
                        Submitted <?php echo timeAgo($submission['submitted_at']); ?>
                    </div>
                </div>
                <span class="badge badge-<?php echo $submission['status']; ?>">
                    <?php echo ucfirst($submission['status']); ?>
                </span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Action Button -->
    <?php if ($status === 'pending' && !$deadlinePassed): ?>
        <a href="<?php echo BASE_URL; ?>/pages/student/submit.php?id=<?php echo $classworkId; ?>"
           class="btn btn-primary btn-lg" style="width: 100%;">
            📤 Submit Assignment
        </a>
    <?php elseif ($deadlinePassed && !$submission): ?>
        <div class="deadline-locked">
            <div class="lock-icon">🚫</div>
            <h3>Time Up – Submission Closed</h3>
            <p>The deadline for this assignment has passed.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
