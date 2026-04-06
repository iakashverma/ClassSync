<?php
// admin/dashboard.php - Admin dashboard
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('admin');

$pageTitle = "Admin Dashboard";

// get counts for stats
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$totalTeachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='teacher'"))['count'];
$totalStudents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='student'"))['count'];
$totalSubjects = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM subjects"))['count'];
$totalAssignments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM classwork"))['count'];
$totalSubmissions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM submissions"))['count'];

// recent assignments
$recentQuery = "SELECT c.*, s.subject_name, u.name as teacher_name 
                FROM classwork c 
                JOIN subjects s ON c.subject_id = s.id 
                JOIN users u ON c.teacher_id = u.id 
                ORDER BY c.created_at DESC LIMIT 5";
$recentAssignments = mysqli_query($conn, $recentQuery);

require_once '../includes/header.php';
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $totalUsers; ?></h4>
            <p>Total Users</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $totalTeachers; ?></h4>
            <p>Teachers</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $totalStudents; ?></h4>
            <p>Students</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $totalSubjects; ?></h4>
            <p>Subjects</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon teal">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $totalAssignments; ?></h4>
            <p>Assignments</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-file-upload"></i>
        </div>
        <div class="stat-info">
            <h4><?php echo $totalSubmissions; ?></h4>
            <p>Submissions</p>
        </div>
    </div>
</div>

<!-- Recent Assignments -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-clock"></i> Recent Assignments</h3>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($recentAssignments) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Topic</th>
                        <th>Teacher</th>
                        <th>Deadline</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($recentAssignments)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['topic']); ?></td>
                        <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($row['deadline'])); ?></td>
                        <td>
                            <?php
                            $now = date("Y-m-d H:i:s");
                            if ($now > $row['deadline']) {
                                echo '<span class="badge badge-danger">Expired</span>';
                            } else {
                                echo '<span class="badge badge-success">Active</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>No Assignments Yet</h3>
            <p>Assignments will appear here once teachers create them.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
