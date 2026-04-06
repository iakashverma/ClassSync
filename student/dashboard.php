<?php
// student/dashboard.php - Student progress dashboard
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('student');

$pageTitle = "Student Dashboard";
$studentId = getUserId();
$currentTime = date("Y-m-d H:i:s");

// total assignments (all classwork)
$totalAssignments = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as count FROM classwork")
)['count'];

// submitted assignments
$submittedCount = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as count FROM submissions WHERE student_id = $studentId")
)['count'];

// missed assignments (deadline passed and no submission)
$missedQuery = "SELECT COUNT(*) as count FROM classwork c 
                WHERE c.deadline < '$currentTime' 
                AND c.id NOT IN (SELECT classwork_id FROM submissions WHERE student_id = $studentId)";
$missedCount = mysqli_fetch_assoc(mysqli_query($conn, $missedQuery))['count'];

// pending assignments (deadline not passed, not submitted yet)
$pendingCount = $totalAssignments - $submittedCount - $missedCount;
if ($pendingCount < 0) $pendingCount = 0;

// completion percentage
$completionPercent = ($totalAssignments > 0) ? round(($submittedCount / $totalAssignments) * 100) : 0;

// upcoming assignments (nearest deadlines)
$upcomingQuery = "SELECT c.*, s.subject_name 
                  FROM classwork c 
                  JOIN subjects s ON c.subject_id = s.id 
                  WHERE c.deadline > '$currentTime'
                  AND c.id NOT IN (SELECT classwork_id FROM submissions WHERE student_id = $studentId)
                  ORDER BY c.deadline ASC LIMIT 5";
$upcoming = mysqli_query($conn, $upcomingQuery);

require_once '../includes/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $totalAssignments; ?></h4>
            <p>Total Assignments</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $submittedCount; ?></h4>
            <p>Submitted</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $pendingCount; ?></h4>
            <p>Pending</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $missedCount; ?></h4>
            <p>Missed</p>
        </div>
    </div>
</div>

<!-- Progress Bar -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-chart-bar"></i> Completion Progress</h3>
    </div>
    <div class="card-body">
        <div class="progress-wrap">
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: <?php echo $completionPercent; ?>%"></div>
            </div>
            <div class="progress-text">
                <span><?php echo $submittedCount; ?> of <?php echo $totalAssignments; ?> completed</span>
                <span><?php echo $completionPercent; ?>%</span>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Assignments -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-calendar-alt"></i> Upcoming Deadlines</h3>
        <a href="view_assignments.php" class="btn btn-primary btn-sm">
            <i class="fas fa-list"></i> View All
        </a>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($upcoming) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Topic</th>
                        <th>Deadline</th>
                        <th>Time Left</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($upcoming)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['topic']); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($row['deadline'])); ?></td>
                        <td>
                            <div class="countdown" id="dash-timer-<?php echo $row['id']; ?>"></div>
                            <script>startCountdown('<?php echo $row['deadline']; ?>', 'dash-timer-<?php echo $row['id']; ?>');</script>
                        </td>
                        <td>
                            <a href="submit_assignment.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-upload"></i> Submit
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-check-double"></i>
            <h3>All Caught Up!</h3>
            <p>No pending assignments right now.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
