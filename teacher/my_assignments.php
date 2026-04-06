<?php
// teacher/my_assignments.php - List teacher's assignments
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('teacher');

$pageTitle = "My Assignments";
$teacherId = getUserId();

// get all assignments by this teacher
$query = "SELECT c.*, s.subject_name,
          (SELECT COUNT(*) FROM submissions sub WHERE sub.classwork_id = c.id) as submission_count
          FROM classwork c 
          JOIN subjects s ON c.subject_id = s.id 
          WHERE c.teacher_id = ?
          ORDER BY c.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $teacherId);
mysqli_stmt_execute($stmt);
$assignments = mysqli_stmt_get_result($stmt);

require_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-clipboard-list"></i> My Assignments</h3>
        <a href="create_assignment.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> New
        </a>
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
                        <th>Deadline</th>
                        <th>Submissions</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; while ($row = mysqli_fetch_assoc($assignments)): ?>
                    <?php
                    $currentTime = date("Y-m-d H:i:s");
                    $isExpired = ($currentTime > $row['deadline']);
                    ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['topic']); ?></td>
                        <td>
                            <?php echo date('d M Y, h:i A', strtotime($row['deadline'])); ?>
                            <?php if (!$isExpired): ?>
                                <div class="countdown" id="timer-<?php echo $row['id']; ?>"></div>
                                <script>startCountdown('<?php echo $row['deadline']; ?>', 'timer-<?php echo $row['id']; ?>');</script>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-info"><?php echo $row['submission_count']; ?></span></td>
                        <td>
                            <?php if ($isExpired): ?>
                                <span class="badge badge-danger">Expired</span>
                            <?php else: ?>
                                <span class="badge badge-success">Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="view_submissions.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard"></i>
            <h3>No Assignments Created</h3>
            <p>Create your first assignment to get started!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
