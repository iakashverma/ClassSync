<?php
/**
 * ClassSync - Admin: Monitor All Classwork & Submissions
 */
$pageTitle = 'Monitor';
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

// Get all classwork with teacher and subject info
$classwork = $pdo->query("
    SELECT c.*, s.subject_name, u.name as teacher_name,
        (SELECT COUNT(*) FROM submissions WHERE classwork_id = c.id) as submission_count,
        (SELECT COUNT(*) FROM users WHERE role = 'student') as total_students
    FROM classwork c
    JOIN subjects s ON c.subject_id = s.id
    JOIN users u ON c.teacher_id = u.id
    ORDER BY c.created_at DESC
")->fetchAll();

// Filter
$statusFilter = $_GET['status'] ?? '';
?>

<div class="page-header">
    <h1>🔍 Monitor Classwork & Submissions</h1>
    <p>Overview of all classwork and student submissions</p>
</div>

<!-- Filters -->
<div class="filter-bar">
    <a href="?" class="btn btn-sm <?php echo !$statusFilter ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
    <a href="?status=active" class="btn btn-sm <?php echo $statusFilter === 'active' ? 'btn-primary' : 'btn-secondary'; ?>">Active</a>
    <a href="?status=closed" class="btn btn-sm <?php echo $statusFilter === 'closed' ? 'btn-primary' : 'btn-secondary'; ?>">Closed/Expired</a>
</div>

<!-- Classwork List -->
<div class="assignment-list">
    <?php
    $filtered = $classwork;
    if ($statusFilter === 'active') {
        $filtered = array_filter($classwork, fn($c) => !isDeadlinePassed($c['deadline']));
    } elseif ($statusFilter === 'closed') {
        $filtered = array_filter($classwork, fn($c) => isDeadlinePassed($c['deadline']));
    }
    ?>

    <?php if (empty($filtered)): ?>
        <div class="card">
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No classwork found</h3>
                <p>No classwork matches your current filter.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($filtered as $cw): ?>
        <div class="assignment-item">
            <div class="assignment-info">
                <h3><?php echo e($cw['topic']); ?></h3>
                <div class="assignment-meta">
                    <span>📚 <?php echo e($cw['subject_name']); ?></span>
                    <span>👨‍🏫 <?php echo e($cw['teacher_name']); ?></span>
                    <span>📤 <?php echo $cw['submission_count']; ?>/<?php echo $cw['total_students']; ?> submitted</span>
                    <span>📅 <?php echo formatDeadline($cw['deadline']); ?></span>
                </div>
            </div>
            <div class="assignment-actions">
                <?php if (isDeadlinePassed($cw['deadline'])): ?>
                    <span class="badge badge-closed">⏰ Expired</span>
                <?php else: ?>
                    <span class="countdown" data-deadline="<?php echo $cw['deadline']; ?>"></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
