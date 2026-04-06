<?php
// admin/view_assignments.php - View all assignments and submissions
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('admin');

$pageTitle = "All Assignments";

// get all assignments with subject & teacher info
$query = "SELECT c.*, s.subject_name, u.name as teacher_name,
          (SELECT COUNT(*) FROM submissions sub WHERE sub.classwork_id = c.id) as submission_count
          FROM classwork c 
          JOIN subjects s ON c.subject_id = s.id 
          JOIN users u ON c.teacher_id = u.id 
          ORDER BY c.created_at DESC";
$assignments = mysqli_query($conn, $query);

require_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-tasks"></i> All Assignments & Submissions</h3>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($assignments) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Topic</th>
                        <th>Teacher</th>
                        <th>Deadline</th>
                        <th>Submissions</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; while ($row = mysqli_fetch_assoc($assignments)): ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['topic']); ?></td>
                        <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($row['deadline'])); ?></td>
                        <td><span class="badge badge-info"><?php echo $row['submission_count']; ?></span></td>
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
            <p>No assignments have been created by any teacher.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
