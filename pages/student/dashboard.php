<?php
/**
 * ClassSync - Student Dashboard (Progress Tracking 📈)
 */
$pageTitle = 'Student Dashboard';
require_once __DIR__ . '/../../includes/header.php';
requireRole('student');

$studentId = $_SESSION['user_id'];

// Get progress stats
$progress = getStudentProgress($pdo, $studentId);

// Upcoming deadlines (next 5)
$upcoming = $pdo->prepare("
    SELECT c.*, s.subject_name, u.name as teacher_name
    FROM classwork c
    JOIN subjects s ON c.subject_id = s.id
    JOIN users u ON c.teacher_id = u.id
    WHERE c.deadline > NOW()
    ORDER BY c.deadline ASC
    LIMIT 5
");
$upcoming->execute();
$upcomingDeadlines = $upcoming->fetchAll();

// Recent submissions
$recentSubs = $pdo->prepare("
    SELECT sub.*, c.topic, s.subject_name
    FROM submissions sub
    JOIN classwork c ON sub.classwork_id = c.id
    JOIN subjects s ON c.subject_id = s.id
    WHERE sub.student_id = ?
    ORDER BY sub.submitted_at DESC
    LIMIT 5
");
$recentSubs->execute([$studentId]);
$recentSubmissions = $recentSubs->fetchAll();
?>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1>📈 My Dashboard</h1>
            <p>Welcome back, <?php echo e($_SESSION['user_name']); ?>! Here's your progress overview.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/pages/student/assignments.php" class="btn btn-primary">
            📋 View Assignments
        </a>
    </div>
</div>

<!-- Progress Stats -->
<div class="stats-grid">
    <div class="stat-card accent-blue">
        <div class="stat-icon">📋</div>
        <div class="stat-value"><?php echo $progress['total']; ?></div>
        <div class="stat-label">Total Assignments</div>
    </div>
    <div class="stat-card accent-green">
        <div class="stat-icon">✅</div>
        <div class="stat-value"><?php echo $progress['submitted']; ?></div>
        <div class="stat-label">Submitted</div>
    </div>
    <div class="stat-card accent-red">
        <div class="stat-icon">❌</div>
        <div class="stat-value"><?php echo $progress['missed']; ?></div>
        <div class="stat-label">Missed</div>
    </div>
    <div class="stat-card accent-yellow">
        <div class="stat-icon">⏳</div>
        <div class="stat-value"><?php echo $progress['pending']; ?></div>
        <div class="stat-label">Pending</div>
    </div>
</div>

<!-- Progress Bar + Chart Row -->
<div class="grid-2" style="margin-bottom: 32px;">
    <!-- Progress Percentage -->
    <div class="card" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
        <h3 style="color: var(--text-secondary); margin-bottom: 16px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
            Overall Progress
        </h3>
        <div class="progress-text"><?php echo $progress['progress']; ?>%</div>
        <div class="progress-bar-container" style="width: 100%; margin-top: 12px;">
            <div class="progress-bar-fill" style="width: <?php echo $progress['progress']; ?>%;"></div>
        </div>
        <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 8px;">
            <?php echo $progress['submitted']; ?> of <?php echo $progress['total']; ?> assignments completed
        </p>
    </div>

    <!-- Doughnut Chart -->
    <div class="card">
        <h3 style="color: var(--text-secondary); margin-bottom: 8px; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; text-align: center;">
            Submission Breakdown
        </h3>
        <div class="chart-container">
            <canvas id="progressChart"
                    data-submitted="<?php echo $progress['submitted']; ?>"
                    data-missed="<?php echo $progress['missed']; ?>"
                    data-pending="<?php echo $progress['pending']; ?>">
            </canvas>
        </div>
    </div>
</div>

<!-- Two Column: Upcoming + Recent -->
<div class="grid-2">
    <!-- Upcoming Deadlines -->
    <div class="card">
        <div class="card-header">
            <h2>⏰ Upcoming Deadlines</h2>
        </div>
        <?php if (empty($upcomingDeadlines)): ?>
            <div class="empty-state" style="padding: 30px;">
                <div class="empty-icon">🎉</div>
                <h3>All caught up!</h3>
                <p>No pending deadlines right now.</p>
            </div>
        <?php else: ?>
            <div class="assignment-list">
                <?php foreach ($upcomingDeadlines as $cw): ?>
                <?php $status = getSubmissionStatus($pdo, $cw['id'], $studentId); ?>
                <div class="assignment-item" style="padding: 12px;">
                    <div class="assignment-info">
                        <h3 style="font-size: 0.875rem;"><?php echo e($cw['topic']); ?></h3>
                        <div class="assignment-meta">
                            <span>📚 <?php echo e($cw['subject_name']); ?></span>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                        <?php if ($status === 'submitted'): ?>
                            <span class="badge badge-submitted">✅ Done</span>
                        <?php else: ?>
                            <span class="countdown" data-deadline="<?php echo $cw['deadline']; ?>"></span>
                        <?php endif; ?>
                    </div>
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
            <div class="empty-state" style="padding: 30px;">
                <div class="empty-icon">📤</div>
                <h3>No submissions yet</h3>
                <p>Submit your first assignment!</p>
            </div>
        <?php else: ?>
            <div class="assignment-list">
                <?php foreach ($recentSubmissions as $sub): ?>
                <div class="assignment-item" style="padding: 12px;">
                    <div class="assignment-info">
                        <h3 style="font-size: 0.875rem;"><?php echo e($sub['topic']); ?></h3>
                        <div class="assignment-meta">
                            <span>📚 <?php echo e($sub['subject_name']); ?></span>
                            <span>🕒 <?php echo timeAgo($sub['submitted_at']); ?></span>
                        </div>
                    </div>
                    <span class="badge badge-<?php echo $sub['status']; ?>">
                        <?php echo $sub['status'] === 'submitted' ? '✅' : '🟠'; ?> <?php echo ucfirst($sub['status']); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
