<?php
/**
 * ClassSync - Teacher: My Classwork List
 */
$pageTitle = 'My Classwork';
require_once __DIR__ . '/../../includes/header.php';
requireRole('teacher');

$teacherId = $_SESSION['user_id'];
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

// Get all classwork by this teacher
$stmt = $pdo->prepare("
    SELECT c.*, s.subject_name,
        (SELECT COUNT(*) FROM submissions WHERE classwork_id = c.id) as sub_count
    FROM classwork c
    JOIN subjects s ON c.subject_id = s.id
    WHERE c.teacher_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$teacherId]);
$classworkList = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1>📋 My Classwork</h1>
            <p>View and manage your created classwork</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/pages/teacher/create_classwork.php" class="btn btn-primary">
            ➕ Create New
        </a>
    </div>
</div>

<?php if (empty($classworkList)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>No classwork yet</h3>
            <p>Create your first classwork to get started.</p>
            <a href="<?php echo BASE_URL; ?>/pages/teacher/create_classwork.php" class="btn btn-primary" style="margin-top: 16px;">
                ➕ Create Classwork
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="assignment-list">
        <?php foreach ($classworkList as $cw): ?>
        <div class="card" style="margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                <div style="flex: 1; min-width: 250px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-heading);">
                            <?php echo e($cw['topic']); ?>
                        </h3>
                        <?php if (isDeadlinePassed($cw['deadline'])): ?>
                            <span class="badge badge-closed">Expired</span>
                        <?php else: ?>
                            <span class="badge badge-active">Active</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($cw['description']): ?>
                        <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 12px;">
                            <?php echo e(substr($cw['description'], 0, 150)); ?><?php echo strlen($cw['description']) > 150 ? '...' : ''; ?>
                        </p>
                    <?php endif; ?>

                    <div class="assignment-meta">
                        <span>📚 <?php echo e($cw['subject_name']); ?></span>
                        <span>📅 <?php echo formatDeadline($cw['deadline']); ?></span>
                        <span>📤 <?php echo $cw['sub_count']; ?>/<?php echo $totalStudents; ?> submitted</span>
                        <?php if (!isDeadlinePassed($cw['deadline'])): ?>
                            <span class="countdown" data-deadline="<?php echo $cw['deadline']; ?>"></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-start;">
                    <a href="<?php echo BASE_URL; ?>/pages/teacher/view_submissions.php?id=<?php echo $cw['id']; ?>"
                       class="btn btn-sm btn-primary">
                        📊 Submissions
                    </a>

                    <!-- Extend Deadline Button -->
                    <button class="btn btn-sm btn-warning" onclick="openModal('extendModal<?php echo $cw['id']; ?>')">
                        🔁 Extend
                    </button>
                </div>
            </div>
        </div>

        <!-- Extend Deadline Modal -->
        <div class="modal-overlay" id="extendModal<?php echo $cw['id']; ?>">
            <div class="modal">
                <div class="modal-header">
                    <h2>🔁 Extend Deadline</h2>
                    <button class="modal-close" onclick="closeModal('extendModal<?php echo $cw['id']; ?>')">×</button>
                </div>
                <p style="color: var(--text-secondary); margin-bottom: 16px;">
                    <strong><?php echo e($cw['topic']); ?></strong><br>
                    Current deadline: <?php echo formatDeadline($cw['deadline']); ?>
                </p>
                <div class="form-group">
                    <label class="form-label">New Deadline</label>
                    <input type="datetime-local" id="extendDeadline_<?php echo $cw['id']; ?>" class="form-control"
                           min="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal('extendModal<?php echo $cw['id']; ?>')">Cancel</button>
                    <button class="btn btn-primary" onclick="extendDeadline(<?php echo $cw['id']; ?>)">
                        ✅ Extend Deadline
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
