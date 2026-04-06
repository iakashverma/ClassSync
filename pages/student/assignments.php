<?php
/**
 * ClassSync - Student: All Assignments List
 */
$pageTitle = 'My Assignments';
require_once __DIR__ . '/../../includes/header.php';
requireRole('student');

$studentId = $_SESSION['user_id'];

// Filters
$subjectFilter = $_GET['subject'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT c.*, s.subject_name, u.name as teacher_name
    FROM classwork c
    JOIN subjects s ON c.subject_id = s.id
    JOIN users u ON c.teacher_id = u.id
    WHERE 1=1
";
$params = [];

if ($subjectFilter) {
    $query .= " AND c.subject_id = ?";
    $params[] = (int)$subjectFilter;
}
if ($search) {
    $query .= " AND (c.topic LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY c.deadline DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$allClasswork = $stmt->fetchAll();

// Get subjects for filter
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();

// Filter by status (in PHP since it requires checking submissions)
$filteredClasswork = [];
foreach ($allClasswork as $cw) {
    $status = getSubmissionStatus($pdo, $cw['id'], $studentId);
    $cw['student_status'] = $status;

    if ($statusFilter && $statusFilter !== $status) {
        continue;
    }

    $filteredClasswork[] = $cw;
}
?>

<div class="page-header">
    <h1>📋 My Assignments</h1>
    <p>View all classwork and submit your assignments</p>
</div>

<!-- Filters -->
<div class="filter-bar">
    <div class="search-input">
        <form method="GET" style="display:flex;gap:8px;width:100%">
            <input type="text" name="search" class="form-control" placeholder="Search assignments..."
                   value="<?php echo e($search); ?>" style="padding-left:40px">
            <input type="hidden" name="subject" value="<?php echo e($subjectFilter); ?>">
            <input type="hidden" name="status" value="<?php echo e($statusFilter); ?>">
        </form>
    </div>

    <select onchange="window.location.href='?subject='+this.value+'&status=<?php echo e($statusFilter); ?>&search=<?php echo e($search); ?>'" class="form-control" style="max-width:180px">
        <option value="">All Subjects</option>
        <?php foreach ($subjects as $s): ?>
        <option value="<?php echo $s['id']; ?>" <?php echo $subjectFilter == $s['id'] ? 'selected' : ''; ?>><?php echo e($s['subject_name']); ?></option>
        <?php endforeach; ?>
    </select>

    <a href="?status=&subject=<?php echo e($subjectFilter); ?>" class="btn btn-sm <?php echo !$statusFilter ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
    <a href="?status=pending&subject=<?php echo e($subjectFilter); ?>" class="btn btn-sm <?php echo $statusFilter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">⏳ Pending</a>
    <a href="?status=submitted&subject=<?php echo e($subjectFilter); ?>" class="btn btn-sm <?php echo $statusFilter === 'submitted' ? 'btn-primary' : 'btn-secondary'; ?>">✅ Submitted</a>
    <a href="?status=missed&subject=<?php echo e($subjectFilter); ?>" class="btn btn-sm <?php echo $statusFilter === 'missed' ? 'btn-primary' : 'btn-secondary'; ?>">❌ Missed</a>
</div>

<!-- Assignment List -->
<?php if (empty($filteredClasswork)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>No assignments found</h3>
            <p>No assignments match your current filters.</p>
        </div>
    </div>
<?php else: ?>
    <div class="assignment-list">
        <?php foreach ($filteredClasswork as $cw): ?>
        <?php $status = $cw['student_status']; ?>
        <div class="assignment-item">
            <div class="assignment-info">
                <h3>
                    <?php echo e($cw['topic']); ?>
                </h3>
                <div class="assignment-meta">
                    <span>📚 <?php echo e($cw['subject_name']); ?></span>
                    <span>👨‍🏫 <?php echo e($cw['teacher_name']); ?></span>
                    <span>📅 <?php echo formatDeadline($cw['deadline']); ?></span>
                    <?php if (!isDeadlinePassed($cw['deadline'])): ?>
                        <span class="countdown" data-deadline="<?php echo $cw['deadline']; ?>"></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="assignment-actions">
                <?php if ($status === 'submitted'): ?>
                    <span class="badge badge-submitted">🟢 Submitted</span>
                <?php elseif ($status === 'missed'): ?>
                    <span class="badge badge-missed">🔴 Missed</span>
                <?php elseif ($status === 'pending'): ?>
                    <span class="badge badge-pending">🟡 Pending</span>
                    <a href="<?php echo BASE_URL; ?>/pages/student/submit.php?id=<?php echo $cw['id']; ?>"
                       class="btn btn-sm btn-primary">
                        📤 Submit
                    </a>
                <?php endif; ?>

                <a href="<?php echo BASE_URL; ?>/pages/student/view_classwork.php?id=<?php echo $cw['id']; ?>"
                   class="btn btn-sm btn-secondary">
                    👁️ View
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
