<?php
// teacher/dashboard.php - Teacher dashboard
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('teacher');

$pageTitle = "Teacher Dashboard";
$teacherId = getUserId();

// count my assignments
$myAssignments = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as count FROM classwork WHERE teacher_id = $teacherId")
)['count'];

// count active assignments (deadline not passed)
$now = date("Y-m-d H:i:s");
$activeStmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM classwork WHERE teacher_id = ? AND deadline > ?");
mysqli_stmt_bind_param($activeStmt, "is", $teacherId, $now);
mysqli_stmt_execute($activeStmt);
$activeCount = mysqli_fetch_assoc(mysqli_stmt_get_result($activeStmt))['count'];
mysqli_stmt_close($activeStmt);

// count total submissions on my assignments
$totalSubs = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as count FROM submissions sub 
                          JOIN classwork c ON sub.classwork_id = c.id 
                          WHERE c.teacher_id = $teacherId")
)['count'];

// expired (deadline passed)
$expiredCount = $myAssignments - $activeCount;

// recent submissions on my assignments
$recentSubs = mysqli_query($conn, "SELECT sub.*, u.name as student_name, c.topic, s.subject_name
                                    FROM submissions sub
                                    JOIN users u ON sub.student_id = u.id
                                    JOIN classwork c ON sub.classwork_id = c.id
                                    JOIN subjects s ON c.subject_id = s.id
                                    WHERE c.teacher_id = $teacherId
                                    ORDER BY sub.submitted_at DESC LIMIT 5");

require_once '../includes/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $myAssignments; ?></h4>
            <p>My Assignments</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $activeCount; ?></h4>
            <p>Active</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $expiredCount; ?></h4>
            <p>Expired</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-file-upload"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $totalSubs; ?></h4>
            <p>Total Submissions</p>
        </div>
    </div>
</div>

<!-- Quick Action -->
<div class="card mb-3">
    <div class="card-body" style="display: flex; justify-content: center; padding: 20px;">
        <a href="create_assignment.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Create New Assignment
        </a>
    </div>
</div>

<!-- Recent Submissions -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-clock"></i> Recent Submissions</h3>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($recentSubs) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Topic</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($sub = mysqli_fetch_assoc($recentSubs)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sub['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($sub['topic']); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($sub['submitted_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No Submissions Yet</h3>
            <p>Student submissions will appear here.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
