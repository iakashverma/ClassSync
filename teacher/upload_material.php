<?php
// teacher/upload_material.php - Upload study materials (PDF/DOC)
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('teacher');

$pageTitle = "Upload Study Material";
$teacherId = getUserId();
$error = "";
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subjectId = intval($_POST['subject_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    
    // Validate basics
    if (empty($title) || empty($subjectId)) {
        $error = "Please fill in all required fields.";
    } elseif (!isset($_FILES['material_file']) || $_FILES['material_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a file to upload.";
    } else {
        $file = $_FILES['material_file'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        
        // Check file extension
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['pdf', 'doc', 'docx'];
        
        if (!in_array($ext, $allowedTypes)) {
            $error = "Only PDF, DOC, and DOCX files are allowed.";
        } elseif ($fileSize > 10 * 1024 * 1024) { // 10MB limit
            $error = "File size must be under 10MB.";
        } else {
            // Generate unique filename
            $newFileName = 'material_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $uploadDir = '../uploads/materials/';
            $uploadPath = $uploadDir . $newFileName;
            
            // Create directory if not exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Map extension to file_type
            $fileType = ($ext === 'pdf') ? 'pdf' : 'doc';
            
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Insert into database
                $stmt = mysqli_prepare($conn, "INSERT INTO study_materials (teacher_id, subject_id, title, description, file_path, file_type, is_public) VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iissssi", $teacherId, $subjectId, $title, $description, $newFileName, $fileType, $isPublic);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Study material uploaded successfully!";
                } else {
                    $error = "Database error. Please try again.";
                    // Clean up uploaded file on db error
                    unlink($uploadPath);
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = "Failed to upload file. Please try again.";
            }
        }
    }
}

// Get subjects for dropdown
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
        <h3><i class="fas fa-file-upload"></i> Upload Study Material</h3>
        <a href="manage_materials.php" class="btn btn-outline btn-sm">
            <i class="fas fa-list"></i> My Materials
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="subject_id">Subject <span style="color: var(--danger);">*</span></label>
                <select name="subject_id" id="subject_id" class="form-control" required>
                    <option value="">-- Select Subject --</option>
                    <?php while ($sub = mysqli_fetch_assoc($subjects)): ?>
                        <option value="<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['subject_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="title">Title <span style="color: var(--danger);">*</span></label>
                <input type="text" name="title" id="title" class="form-control" 
                       placeholder="e.g., Chapter 5 - Binary Trees Notes" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea name="description" id="description" class="form-control" 
                          placeholder="Brief description of this material..."></textarea>
            </div>
            
            <div class="form-group">
                <label>File (PDF, DOC, DOCX — Max 10MB) <span style="color: var(--danger);">*</span></label>
                <div class="upload-area" onclick="document.getElementById('material_file').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p id="file-label">Click to select file or drag & drop</p>
                    <p class="file-info">Supported: PDF, DOC, DOCX (max 10MB)</p>
                </div>
                <input type="file" name="material_file" id="material_file" 
                       accept=".pdf,.doc,.docx" style="display: none;" 
                       onchange="updateFileName(this)" required>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_public" value="1" checked 
                           style="width: 18px; height: 18px; accent-color: var(--primary);">
                    <span>Show on public homepage (visible to all visitors)</span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-success">
                <i class="fas fa-upload"></i> Upload Material
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
