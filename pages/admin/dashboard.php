<?php
/**
 * ClassSync - Admin Dashboard
 */
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

// Get stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalSubjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$totalClasswork = $pdo->query("SELECT COUNT(*) FROM classwork")->fetchColumn();
$totalSubmissions = $pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn();

// Recent activity
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentClasswork = $pdo->query("
    SELECT c.*, s.subject_name, u.name as teacher_name
    FROM classwork c
    JOIN subjects s ON c.subject_id = s.id
    JOIN users u ON c.teacher_id = u.id
    ORDER BY c.created_at DESC LIMIT 5
")->fetchAll();
?>

<div class="page-header">
    <h1>📊 Admin Dashboard</h1>
    <p>Overview of your ClassSync system</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card accent-blue">
        <div class="stat-icon">👥</div>
        <div class="stat-value"><?php echo $totalUsers; ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card accent-cyan">
        <div class="stat-icon">👨‍🏫</div>
        <div class="stat-value"><?php echo $totalTeachers; ?></div>
        <div class="stat-label">Teachers</div>
    </div>
    <div class="stat-card accent-green">
        <div class="stat-icon">🎓</div>
        <div class="stat-value"><?php echo $totalStudents; ?></div>
        <div class="stat-label">Students</div>
    </div>
    <div class="stat-card accent-purple">
        <div class="stat-icon">📚</div>
        <div class="stat-value"><?php echo $totalSubjects; ?></div>
        <div class="stat-label">Subjects</div>
    </div>
    <div class="stat-card accent-yellow">
        <div class="stat-icon">📋</div>
        <div class="stat-value"><?php echo $totalClasswork; ?></div>
        <div class="stat-label">Classwork</div>
    </div>
    <div class="stat-card accent-red">
        <div class="stat-icon">📤</div>
        <div class="stat-value"><?php echo $totalSubmissions; ?></div>
        <div class="stat-label">Submissions</div>
    </div>
</div>

<!-- Two Column Layout -->
<div class="grid-2">
    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h2>👥 Recent Users</h2>
            <a href="<?php echo BASE_URL; ?>/pages/admin/manage_users.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <?php if (empty($recentUsers)): ?>
            <div class="empty-state">
                <p>No users yet.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                        <tr>
                            <td>
                                <strong><?php echo e($u['name']); ?></strong>
                                <br><small style="color:var(--text-muted)"><?php echo e($u['email']); ?></small>
                            </td>
                            <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                            <td><?php echo timeAgo($u['created_at']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Classwork -->
    <div class="card">
        <div class="card-header">
            <h2>📋 Recent Classwork</h2>
            <a href="<?php echo BASE_URL; ?>/pages/admin/monitor.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <?php if (empty($recentClasswork)): ?>
            <div class="empty-state">
                <p>No classwork created yet.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Topic</th>
                            <th>Subject</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentClasswork as $cw): ?>
                        <tr>
                            <td>
                                <strong><?php echo e($cw['topic']); ?></strong>
                                <br><small style="color:var(--text-muted)">by <?php echo e($cw['teacher_name']); ?></small>
                            </td>
                            <td><?php echo e($cw['subject_name']); ?></td>
                            <td>
                                <?php if (isDeadlinePassed($cw['deadline'])): ?>
                                    <span class="badge badge-closed">Closed</span>
                                <?php else: ?>
                                    <span class="badge badge-active">Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
