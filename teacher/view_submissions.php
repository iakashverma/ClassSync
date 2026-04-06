<?php
// teacher/view_submissions.php - View submissions for an assignment
// also allows extending deadline
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('teacher');

$teacherId = getUserId();
$error = "";
$success = "";

// get assignment id from URL
$assignmentId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($assignmentId == 0) {
    header("Location: my_assignments.php");
    exit();
}

// verify this assignment belongs to this teacher
$aStmt = mysqli_prepare($conn, "SELECT c.*, s.subject_name FROM classwork c JOIN subjects s ON c.subject_id = s.id WHERE c.id = ? AND c.teacher_id = ?");
mysqli_stmt_bind_param($aStmt, "ii", $assignmentId, $teacherId);
mysqli_stmt_execute($aStmt);
$assignment = mysqli_fetch_assoc(mysqli_stmt_get_result($aStmt));
mysqli_stmt_close($aStmt);

if (!$assignment) {
    header("Location: my_assignments.php");
    exit();
}

$pageTitle = "Submissions - " . $assignment['topic'];

// handle deadline extension
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['extend_deadline'])) {
    $newDeadline = $_POST['new_deadline'];
    
    if (empty($newDeadline)) {
        $error = "Please select a new deadline.";
    } else {
        $newDeadlineFormatted = date("Y-m-d H:i:s", strtotime($newDeadline));
        
        $updateStmt = mysqli_prepare($conn, "UPDATE classwork SET deadline = ? WHERE id = ?");
        mysqli_stmt_bind_param($updateStmt, "si", $newDeadlineFormatted, $assignmentId);
        
        if (mysqli_stmt_execute($updateStmt)) {
            $assignment['deadline'] = $newDeadlineFormatted;
            $success = "Deadline extended successfully!";
        } else {
            $error = "Failed to update deadline.";
        }
        mysqli_stmt_close($updateStmt);
    }
}

// check deadline status
$currentTime = date("Y-m-d H:i:s");
$isExpired = ($currentTime > $assignment['deadline']);

// get all students
$students = mysqli_query($conn, "SELECT * FROM users WHERE role = 'student' ORDER BY name");

// get submissions for this assignment
$subsQuery = mysqli_prepare($conn, "SELECT sub.*, u.name as student_name, u.reg_no 
                                     FROM submissions sub 
                                     JOIN users u ON sub.student_id = u.id 
                                     WHERE sub.classwork_id = ?
                                     ORDER BY sub.submitted_at DESC");
mysqli_stmt_bind_param($subsQuery, "i", $assignmentId);
mysqli_stmt_execute($subsQuery);
$submissions = mysqli_stmt_get_result($subsQuery);

// build array of students who submitted
$submittedStudents = [];
$submissionRows = [];
while ($s = mysqli_fetch_assoc($submissions)) {
    $submittedStudents[] = $s['student_id'];
    $submissionRows[] = $s;
}

require_once '../includes/header.php';
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<!-- Assignment Info -->
<div class="card mb-3">
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <p class="text-muted text-sm">Subject</p>
                <p style="font-weight: 600;"><?php echo htmlspecialchars($assignment['subject_name']); ?></p>
            </div>
            <div>
                <p class="text-muted text-sm">Topic</p>
                <p style="font-weight: 600;"><?php echo htmlspecialchars($assignment['topic']); ?></p>
            </div>
            <div>
                <p class="text-muted text-sm">Deadline</p>
                <p style="font-weight: 600;">
                    <?php echo date('d M Y, h:i A', strtotime($assignment['deadline'])); ?>
                    <?php if ($isExpired): ?>
                        <span class="badge badge-danger">Expired</span>
                    <?php else: ?>
                        <span class="badge badge-success">Active</span>
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <p class="text-muted text-sm">Description</p>
                <p><?php echo !empty($assignment['description']) ? htmlspecialchars($assignment['description']) : 'No description'; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Extend Deadline -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-clock"></i> Extend Deadline</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                <label for="new_deadline">New Deadline</label>
                <input type="datetime-local" name="new_deadline" id="new_deadline" class="form-control">
            </div>
            <button type="submit" name="extend_deadline" class="btn btn-warning" style="height: 46px;">
                <i class="fas fa-clock"></i> Extend
            </button>
        </form>
    </div>
</div>

<!-- Submissions Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-file-alt"></i> Student Submissions</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Reg No</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // reset students query
                    mysqli_data_seek($students, 0);
                    $count = 1;
                    while ($student = mysqli_fetch_assoc($students)):
                        // check if this student has submitted
                        $hasSubmitted = in_array($student['id'], $submittedStudents);
                        $submissionData = null;
                        
                        if ($hasSubmitted) {
                            // find the submission details
                            foreach ($submissionRows as $sr) {
                                if ($sr['student_id'] == $student['id']) {
                                    $submissionData = $sr;
                                    break;
                                }
                            }
                        }
                    ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo $student['reg_no'] ? $student['reg_no'] : '-'; ?></td>
                        <td>
                            <?php if ($hasSubmitted): ?>
                                <span class="badge badge-success">Submitted</span>
                            <?php elseif ($isExpired): ?>
                                <span class="badge badge-danger">Missed</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($hasSubmitted && $submissionData): ?>
                                <?php echo date('d M Y, h:i A', strtotime($submissionData['submitted_at'])); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($hasSubmitted && $submissionData): ?>
                                <a href="/ClassSync/<?php echo $submissionData['file_path']; ?>" 
                                   class="btn btn-primary btn-sm" target="_blank" download>
                                    <i class="fas fa-download"></i> Download
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
