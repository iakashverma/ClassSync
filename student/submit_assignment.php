<?php
// student/submit_assignment.php - Upload assignment file
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('student');

$studentId = getUserId();
$error = "";
$success = "";

// get assignment id
$assignmentId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($assignmentId == 0) {
    header("Location: view_assignments.php");
    exit();
}

// get assignment details
$aStmt = mysqli_prepare($conn, "SELECT c.*, s.subject_name, u.name as teacher_name 
                                 FROM classwork c 
                                 JOIN subjects s ON c.subject_id = s.id 
                                 JOIN users u ON c.teacher_id = u.id 
                                 WHERE c.id = ?");
mysqli_stmt_bind_param($aStmt, "i", $assignmentId);
mysqli_stmt_execute($aStmt);
$assignment = mysqli_fetch_assoc(mysqli_stmt_get_result($aStmt));
mysqli_stmt_close($aStmt);

if (!$assignment) {
    header("Location: view_assignments.php");
    exit();
}

$pageTitle = "Submit - " . $assignment['topic'];

// check if already submitted
$checkSub = mysqli_prepare($conn, "SELECT * FROM submissions WHERE classwork_id = ? AND student_id = ?");
mysqli_stmt_bind_param($checkSub, "ii", $assignmentId, $studentId);
mysqli_stmt_execute($checkSub);
$existingSubmission = mysqli_fetch_assoc(mysqli_stmt_get_result($checkSub));
mysqli_stmt_close($checkSub);

// check deadline
$currentTime = date("Y-m-d H:i:s");
$deadline = $assignment['deadline'];

if ($currentTime > $deadline) {
    $canUpload = false;
} else {
    $canUpload = true;
}

// handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $canUpload && !$existingSubmission) {
    
    if (!isset($_FILES['assignment_file']) || $_FILES['assignment_file']['error'] != 0) {
        $error = "Please select a file to upload.";
    } else {
        $file = $_FILES['assignment_file'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        
        // get file extension
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // allowed types
        $allowedTypes = ['pdf', 'doc', 'docx'];
        
        if (!in_array($ext, $allowedTypes)) {
            $error = "Only PDF, DOC, and DOCX files are allowed.";
        } elseif ($fileSize > 10 * 1024 * 1024) {
            // 10MB limit
            $error = "File size must be under 10MB.";
        } else {
            // create unique file name to avoid conflicts
            $newFileName = "assignment_" . $assignmentId . "_student_" . $studentId . "_" . time() . "." . $ext;
            $uploadPath = "uploads/" . $newFileName;
            $fullPath = __DIR__ . "/../uploads/" . $newFileName;
            
            if (move_uploaded_file($fileTmp, $fullPath)) {
                // save to database
                $subStmt = mysqli_prepare($conn, "INSERT INTO submissions (classwork_id, student_id, file_path, status) VALUES (?, ?, ?, 'submitted')");
                mysqli_stmt_bind_param($subStmt, "iis", $assignmentId, $studentId, $uploadPath);
                
                if (mysqli_stmt_execute($subStmt)) {
                    $success = "Assignment submitted successfully!";
                    $existingSubmission = true; // prevent re-upload
                } else {
                    $error = "Database error. Please try again.";
                }
                mysqli_stmt_close($subStmt);
            } else {
                $error = "Failed to upload file. Please try again.";
            }
        }
    }
}

require_once '../includes/header.php';
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<!-- Assignment Details -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-info-circle"></i> Assignment Details</h3>
    </div>
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
                <p class="text-muted text-sm">Teacher</p>
                <p style="font-weight: 600;"><?php echo htmlspecialchars($assignment['teacher_name']); ?></p>
            </div>
            <div>
                <p class="text-muted text-sm">Deadline</p>
                <p style="font-weight: 600;">
                    <?php echo date('d M Y, h:i A', strtotime($deadline)); ?>
                    <?php if ($canUpload): ?>
                        <span class="badge badge-success">Open</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Closed</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <?php if (!empty($assignment['description'])): ?>
        <div class="mt-2">
            <p class="text-muted text-sm">Description</p>
            <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($canUpload && !$existingSubmission): ?>
        <div class="mt-2">
            <div class="countdown" id="submit-timer"></div>
            <script>startCountdown('<?php echo $deadline; ?>', 'submit-timer');</script>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Upload Section -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-upload"></i> Submit Your Work</h3>
    </div>
    <div class="card-body">
        <?php if (!$canUpload): ?>
            <!-- Deadline passed -->
            <div class="time-up-banner">
                <i class="fas fa-lock"></i> Time Up – Submission Closed
            </div>
            <p class="text-center text-muted mt-2">The deadline for this assignment has passed. You can no longer submit.</p>
        
        <?php elseif ($existingSubmission): ?>
            <!-- Already submitted -->
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> You have already submitted this assignment.
            </div>
        
        <?php else: ?>
            <!-- Upload form -->
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="upload-area" onclick="document.getElementById('assignment_file').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p id="file-label">Click to select your file</p>
                    <p class="file-info">Allowed: PDF, DOC, DOCX | Max size: 10MB</p>
                    <input type="file" name="assignment_file" id="assignment_file" 
                           accept=".pdf,.doc,.docx" style="display: none;"
                           onchange="updateFileName(this)" required>
                </div>
                
                <div class="mt-2 text-center">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Submit Assignment
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
