<?php
// teacher/create_assignment.php - Create new assignment
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('teacher');

$pageTitle = "Create Assignment";
$teacherId = getUserId();
$error = "";
$success = "";

// handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subjectId = intval($_POST['subject_id']);
    $topic = trim($_POST['topic']);
    $description = trim($_POST['description']);
    $deadline = $_POST['deadline'];
    
    // basic validation
    if (empty($subjectId) || empty($topic) || empty($deadline)) {
        $error = "Please fill in all required fields.";
    } else {
        // convert deadline from datetime-local format
        $deadlineFormatted = date("Y-m-d H:i:s", strtotime($deadline));
        
        // check if deadline is in the future
        $currentTime = date("Y-m-d H:i:s");
        if ($deadlineFormatted <= $currentTime) {
            $error = "Deadline must be in the future.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO classwork (subject_id, teacher_id, topic, description, deadline) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iisss", $subjectId, $teacherId, $topic, $description, $deadlineFormatted);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Assignment created successfully!";
            } else {
                $error = "Failed to create assignment. Try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// get subjects for dropdown
$subjects = mysqli_query($conn, "SELECT * FROM subjects ORDER BY subject_name");

require_once '../includes/header.php';
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle"></i> New Assignment</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="form-group">
                <label for="subject_id">Subject</label>
                <select name="subject_id" id="subject_id" class="form-control" required>
                    <option value="">-- Select Subject --</option>
                    <?php while ($sub = mysqli_fetch_assoc($subjects)): ?>
                        <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['subject_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="topic">Topic</label>
                <input type="text" name="topic" id="topic" class="form-control" 
                       placeholder="e.g., Binary Search Tree Implementation" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea name="description" id="description" class="form-control" 
                          placeholder="Describe the assignment..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="deadline">Deadline</label>
                <input type="datetime-local" name="deadline" id="deadline" class="form-control" required>
                
                <!-- Quick deadline buttons -->
                <div class="quick-deadline-btns">
                    <button type="button" class="quick-deadline-btn" onclick="setDeadline('2days')">
                        <i class="fas fa-clock"></i> +2 Days
                    </button>
                    <button type="button" class="quick-deadline-btn" onclick="setDeadline('3days')">
                        <i class="fas fa-clock"></i> +3 Days
                    </button>
                    <button type="button" class="quick-deadline-btn" onclick="setDeadline('nextMonday')">
                        <i class="fas fa-calendar-week"></i> Next Monday 12 PM
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-success">
                <i class="fas fa-paper-plane"></i> Create Assignment
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
