<?php
/**
 * ClassSync - Student: Submit Assignment
 * With deadline check and file upload
 */
$pageTitle = 'Submit Assignment';
require_once __DIR__ . '/../../includes/header.php';
requireRole('student');

$studentId = $_SESSION['user_id'];
$classworkId = (int)($_GET['id'] ?? 0);

if ($classworkId <= 0) {
    header('Location: ' . BASE_URL . '/pages/student/assignments.php');
    exit;
}

// Get classwork details
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

// Check if already submitted
$existingSub = $pdo->prepare("SELECT * FROM submissions WHERE classwork_id = ? AND student_id = ?");
$existingSub->execute([$classworkId, $studentId]);
$existingSubmission = $existingSub->fetch();

$deadlinePassed = isDeadlinePassed($classwork['deadline']);
?>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1>📤 Submit Assignment</h1>
            <p><?php echo e($classwork['topic']); ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>/pages/student/assignments.php" class="btn btn-secondary">
            ← Back
        </a>
    </div>
</div>

<div style="max-width: 700px;">
    <!-- Classwork Details -->
    <div class="card" style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="color: var(--text-heading); font-size: 1.1rem; margin-bottom: 8px;">
                    <?php echo e($classwork['topic']); ?>
                </h3>
                <div class="assignment-meta">
                    <span>📚 <?php echo e($classwork['subject_name']); ?></span>
                    <span>👨‍🏫 <?php echo e($classwork['teacher_name']); ?></span>
                    <span>📅 <?php echo formatDeadline($classwork['deadline']); ?></span>
                </div>
            </div>
            <div>
                <?php if ($deadlinePassed): ?>
                    <span class="badge badge-closed">⏰ Expired</span>
                <?php else: ?>
                    <span class="countdown" data-deadline="<?php echo $classwork['deadline']; ?>"></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($classwork['description']): ?>
            <p style="color: var(--text-secondary); margin-top: 16px; font-size: 0.9rem; line-height: 1.7; padding-top: 16px; border-top: 1px solid var(--border-subtle);">
                <?php echo nl2br(e($classwork['description'])); ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if ($existingSubmission): ?>
        <!-- Already Submitted -->
        <div class="card">
            <div style="text-align: center; padding: 20px 0;">
                <div style="font-size: 3rem; margin-bottom: 12px;">✅</div>
                <h3 style="color: var(--success); font-size: 1.3rem; margin-bottom: 8px;">Assignment Submitted!</h3>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">
                    You submitted this assignment on <?php echo formatDeadline($existingSubmission['submitted_at']); ?>
                </p>

                <div class="file-info" style="max-width: 400px; margin: 0 auto;">
                    <span class="file-icon">
                        <?php echo pathinfo($existingSubmission['original_filename'], PATHINFO_EXTENSION) === 'pdf' ? '📄' : '📝'; ?>
                    </span>
                    <div class="file-details">
                        <div class="file-name"><?php echo e($existingSubmission['original_filename']); ?></div>
                        <div class="file-size"><?php echo formatFileSize($existingSubmission['file_size']); ?></div>
                    </div>
                    <span class="badge badge-<?php echo $existingSubmission['status']; ?>">
                        <?php echo ucfirst($existingSubmission['status']); ?>
                    </span>
                </div>
            </div>
        </div>

    <?php elseif ($deadlinePassed): ?>
        <!-- Deadline Passed -->
        <div class="deadline-locked">
            <div class="lock-icon">🚫</div>
            <h3>Time Up – Submission Closed</h3>
            <p>The deadline for this assignment has passed. You can no longer submit.</p>
            <p style="margin-top: 8px; font-size: 0.85rem; color: var(--text-muted);">
                Deadline was: <?php echo formatDeadline($classwork['deadline']); ?>
            </p>
        </div>

    <?php else: ?>
        <!-- Upload Form -->
        <div class="card">
            <h3 style="color: var(--text-heading); margin-bottom: 16px;">📎 Upload Your Work</h3>

            <form id="uploadForm" enctype="multipart/form-data">
                <div class="upload-zone" id="uploadZone">
                    <input type="file" id="fileInput" name="file" accept=".pdf,.doc,.docx"
                           style="display:none">
                    <div class="upload-icon">📁</div>
                    <div class="upload-text">
                        <strong>Click or drag & drop</strong> your file here
                    </div>
                    <div class="upload-hint">
                        Accepted: PDF, DOC, DOCX &nbsp;|&nbsp; Max size: 10MB
                    </div>
                </div>

                <!-- File Info (shown after selection) -->
                <div class="file-info" id="fileInfo" style="display: none; margin-top: 16px;">
                    <span class="file-icon" id="fileIcon">📄</span>
                    <div class="file-details">
                        <div class="file-name" id="fileName">—</div>
                        <div class="file-size" id="fileSize">—</div>
                    </div>
                </div>

                <!-- Upload Progress -->
                <div class="upload-progress" style="margin-top: 16px;">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" id="uploadProgressFill" style="width: 0%;"></div>
                    </div>
                </div>

                <button type="button" id="submitBtn" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 20px;"
                        onclick="uploadSubmission(<?php echo $classworkId; ?>)" disabled>
                    📤 Submit Assignment
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
