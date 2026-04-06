<?php
// teacher/manage_materials.php - View and manage uploaded study materials
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('teacher');

$pageTitle = "My Study Materials";
$teacherId = getUserId();
$message = "";
$msgType = "";

// Handle delete action
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    // Get file path before deleting
    $fileStmt = mysqli_prepare($conn, "SELECT file_path FROM study_materials WHERE id = ? AND teacher_id = ?");
    mysqli_stmt_bind_param($fileStmt, "ii", $deleteId, $teacherId);
    mysqli_stmt_execute($fileStmt);
    $fileResult = mysqli_fetch_assoc(mysqli_stmt_get_result($fileStmt));
    mysqli_stmt_close($fileStmt);
    
    if ($fileResult) {
        // Delete from database
        $delStmt = mysqli_prepare($conn, "DELETE FROM study_materials WHERE id = ? AND teacher_id = ?");
        mysqli_stmt_bind_param($delStmt, "ii", $deleteId, $teacherId);
        
        if (mysqli_stmt_execute($delStmt)) {
            // Delete the actual file
            $filePath = '../uploads/materials/' . $fileResult['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $message = "Material deleted successfully.";
            $msgType = "success";
        } else {
            $message = "Failed to delete material.";
            $msgType = "error";
        }
        mysqli_stmt_close($delStmt);
    }
}

// Handle toggle public/private
if (isset($_GET['toggle'])) {
    $toggleId = intval($_GET['toggle']);
    $toggleStmt = mysqli_prepare($conn, "UPDATE study_materials SET is_public = NOT is_public WHERE id = ? AND teacher_id = ?");
    mysqli_stmt_bind_param($toggleStmt, "ii", $toggleId, $teacherId);
    
    if (mysqli_stmt_execute($toggleStmt)) {
        $message = "Visibility updated.";
        $msgType = "success";
    }
    mysqli_stmt_close($toggleStmt);
}

// Fetch my materials
$materials = mysqli_query($conn, "SELECT sm.*, s.subject_name 
                                   FROM study_materials sm 
                                   JOIN subjects s ON sm.subject_id = s.id 
                                   WHERE sm.teacher_id = $teacherId 
                                   ORDER BY sm.created_at DESC");

require_once '../includes/header.php';
?>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $msgType; ?>">
        <i class="fas fa-<?php echo $msgType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-book-open"></i> My Study Materials</h3>
        <a href="upload_material.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Upload New
        </a>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($materials) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Visibility</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($mat = mysqli_fetch_assoc($materials)): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($mat['title']); ?></strong>
                            <?php if (!empty($mat['description'])): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars(mb_strimwidth($mat['description'], 0, 60, '...')); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($mat['subject_name']); ?></td>
                        <td>
                            <span class="badge <?php echo $mat['file_type'] == 'pdf' ? 'badge-danger' : 'badge-info'; ?>">
                                <i class="fas <?php echo $mat['file_type'] == 'pdf' ? 'fa-file-pdf' : 'fa-file-word'; ?>"></i>
                                <?php echo strtoupper($mat['file_type']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="?toggle=<?php echo $mat['id']; ?>" class="badge <?php echo $mat['is_public'] ? 'badge-success' : 'badge-warning'; ?>" 
                               style="cursor: pointer; text-decoration: none;">
                                <i class="fas <?php echo $mat['is_public'] ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                                <?php echo $mat['is_public'] ? 'Public' : 'Private'; ?>
                            </a>
                        </td>
                        <td><?php echo date('d M Y', strtotime($mat['created_at'])); ?></td>
                        <td>
                            <a href="../uploads/materials/<?php echo rawurlencode($mat['file_path']); ?>" 
                               class="btn btn-primary btn-sm" download>
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="?delete=<?php echo $mat['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this material?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No Materials Uploaded</h3>
            <p>Start by uploading your first study material.</p>
            <a href="upload_material.php" class="btn btn-primary mt-2">
                <i class="fas fa-upload"></i> Upload Material
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
