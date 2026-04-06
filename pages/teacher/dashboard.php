<?php
/**
 * ClassSync - Teacher Dashboard
 */
$pageTitle = 'Teacher Dashboard';
require_once __DIR__ . '/../../includes/header.php';
requireRole('teacher');

$teacherId = $_SESSION['user_id'];

// Stats
$activeClasswork = $pdo->prepare("SELECT COUNT(*) FROM classwork WHERE teacher_id = ? AND deadline > NOW()");
$activeClasswork->execute([$teacherId]);
$activeCount = $activeClasswork->fetchColumn();

$totalClasswork = $pdo->prepare("SELECT COUNT(*) FROM classwork WHERE teacher_id = ?");
$totalClasswork->execute([$teacherId]);
$totalCount = $totalClasswork->fetchColumn();

$totalSubmissions = $pdo->prepare("
    SELECT COUNT(*) FROM submissions s
    JOIN classwork c ON s.classwork_id = c.id
    WHERE c.teacher_id = ?
");
$totalSubmissions->execute([$teacherId]);
$submissionCount = $totalSubmissions->fetchColumn();

$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

// Recent submissions
$recentSubs = $pdo->prepare("
    SELECT s.*, u.name as student_name, c.topic, sub.subject_name
    FROM submissions s
    JOIN users u ON s.student_id = u.id
    JOIN classwork c ON s.classwork_id = c.id
    JOIN subjects sub ON c.subject_id = sub.id
    WHERE c.teacher_id = ?
    ORDER BY s.submitted_at DESC
    LIMIT 8
");
$recentSubs->execute([$teacherId]);
$recentSubmissions = $recentSubs->fetchAll();

// Upcoming deadlines
$upcoming = $pdo->prepare("
    SELECT c.*, s.subject_name,
        (SELECT COUNT(*) FROM submissions WHERE classwork_id = c.id) as sub_count
    FROM classwork c
    JOIN subjects s ON c.subject_id = s.id
    WHERE c.teacher_id = ? AND c.deadline > NOW()
    ORDER BY c.deadline ASC
    LIMIT 5
");
$upcoming->execute([$teacherId]);
$upcomingDeadlines = $upcoming->fetchAll();
?>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1>👨‍🏫 Teacher Dashboard</h1>
            <p>Welcome back, <?php echo e($_SESSION['user_name']); ?>!</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/pages/teacher/create_classwork.php" class="btn btn-primary">
            ➕ Create Classwork
        </a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card accent-blue">
        <div class="stat-icon">📋</div>
        <div class="stat-value"><?php echo $totalCount; ?></div>
        <div class="stat-label">Total Classwork</div>
    </div>
    <div class="stat-card accent-green">
        <div class="stat-icon">⚡</div>
        <div class="stat-value"><?php echo $activeCount; ?></div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card accent-purple">
        <div class="stat-icon">📤</div>
        <div class="stat-value"><?php echo $submissionCount; ?></div>
        <div class="stat-label">Submissions</div>
    </div>
    <div class="stat-card accent-cyan">
        <div class="stat-icon">🎓</div>
        <div class="stat-value"><?php echo $totalStudents; ?></div>
        <div class="stat-label">Students</div>
    </div>
</div>

<div class="grid-2">
    <!-- Upcoming Deadlines -->
    <div class="card">
        <div class="card-header">
            <h2>⏰ Upcoming Deadlines</h2>
            <a href="<?php echo BASE_URL; ?>/pages/teacher/my_classwork.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <?php if (empty($upcomingDeadlines)): ?>
            <div class="empty-state">
                <div class="empty-icon">⏰</div>
                <h3>No upcoming deadlines</h3>
                <p>Create new classwork to see deadlines here.</p>
            </div>
        <?php else: ?>
            <div class="assignment-list">
                <?php foreach ($upcomingDeadlines as $cw): ?>
                <div class="assignment-item" style="padding: 14px;">
                    <div class="assignment-info">
                        <h3 style="font-size: 0.9rem;"><?php echo e($cw['topic']); ?></h3>
                        <div class="assignment-meta">
                            <span>📚 <?php echo e($cw['subject_name']); ?></span>
                            <span>📤 <?php echo $cw['sub_count']; ?>/<?php echo $totalStudents; ?></span>
                        </div>
                    </div>
                    <span class="countdown" data-deadline="<?php echo $cw['deadline']; ?>"></span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Submissions -->
    <div class="card">
        <div class="card-header">
            <h2>📤 Recent Submissions</h2>
        </div>
        <?php if (empty($recentSubmissions)): ?>
            <div class="empty-state">
                <div class="empty-icon">📤</div>
                <h3>No submissions yet</h3>
                <p>Student submissions will appear here.</p>
            </div>
        <?php else: ?>
            <div class="assignment-list">
                <?php foreach ($recentSubmissions as $sub): ?>
                <div class="assignment-item" style="padding: 12px;">
                    <div class="assignment-info">
                        <h3 style="font-size: 0.875rem;"><?php echo e($sub['student_name']); ?></h3>
                        <div class="assignment-meta">
                            <span>📋 <?php echo e($sub['topic']); ?></span>
                            <span>🕒 <?php echo timeAgo($sub['submitted_at']); ?></span>
                        </div>
                    </div>
                    <span class="badge badge-<?php echo $sub['status']; ?>"><?php echo ucfirst($sub['status']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
