<?php
// student/view_assignments.php - View all assignments with status
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('student');

$pageTitle = "My Assignments";
$studentId = getUserId();
$currentTime = date("Y-m-d H:i:s");

// get all assignments with submission status
$query = "SELECT c.*, s.subject_name, u.name as teacher_name,
          (SELECT sub.id FROM submissions sub WHERE sub.classwork_id = c.id AND sub.student_id = ?) as submission_id,
          (SELECT sub.submitted_at FROM submissions sub WHERE sub.classwork_id = c.id AND sub.student_id = ?) as submitted_at
          FROM classwork c 
          JOIN subjects s ON c.subject_id = s.id 
          JOIN users u ON c.teacher_id = u.id
          ORDER BY c.deadline DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $studentId, $studentId);
mysqli_stmt_execute($stmt);
$assignments = mysqli_stmt_get_result($stmt);

require_once '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-clipboard-list"></i> All Assignments</h3>
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
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; while ($row = mysqli_fetch_assoc($assignments)): ?>
                    <?php
                    // figure out status
                    $hasSubmitted = !empty($row['submission_id']);
                    $deadlinePassed = ($currentTime > $row['deadline']);
                    
                    if ($hasSubmitted) {
                        $status = 'submitted';
                        $statusLabel = '<span class="badge badge-success">Submitted</span>';
                    } elseif ($deadlinePassed) {
                        $status = 'missed';
                        $statusLabel = '<span class="badge badge-danger">Missed</span>';
                    } else {
                        $status = 'pending';
                        $statusLabel = '<span class="badge badge-warning">Pending</span>';
                    }
                    ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['topic']); ?></td>
                        <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
                        <td>
                            <?php echo date('d M Y, h:i A', strtotime($row['deadline'])); ?>
                            <?php if (!$deadlinePassed && !$hasSubmitted): ?>
                                <div class="countdown" id="list-timer-<?php echo $row['id']; ?>"></div>
                                <script>startCountdown('<?php echo $row['deadline']; ?>', 'list-timer-<?php echo $row['id']; ?>');</script>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $statusLabel; ?></td>
                        <td>
                            <?php if ($status == 'pending'): ?>
                                <a href="submit_assignment.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-upload"></i> Submit
                                </a>
                            <?php elseif ($status == 'submitted'): ?>
                                <span class="text-muted text-sm">
                                    <i class="fas fa-check"></i> Done on <?php echo date('d M', strtotime($row['submitted_at'])); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted text-sm">
                                    <i class="fas fa-lock"></i> Closed
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard"></i>
            <h3>No Assignments</h3>
            <p>No assignments have been posted yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
