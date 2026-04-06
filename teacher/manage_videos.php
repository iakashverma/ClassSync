<?php
// teacher/manage_videos.php - View and manage video lectures
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('teacher');

$pageTitle = "My Video Lectures";
$teacherId = getUserId();
$message = "";
$msgType = "";

// Handle delete action
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    $delStmt = mysqli_prepare($conn, "DELETE FROM video_lectures WHERE id = ? AND teacher_id = ?");
    mysqli_stmt_bind_param($delStmt, "ii", $deleteId, $teacherId);
    
    if (mysqli_stmt_execute($delStmt)) {
        $message = "Video lecture deleted successfully.";
        $msgType = "success";
    } else {
        $message = "Failed to delete video.";
        $msgType = "error";
    }
    mysqli_stmt_close($delStmt);
}

// Handle toggle public/private
if (isset($_GET['toggle'])) {
    $toggleId = intval($_GET['toggle']);
    $toggleStmt = mysqli_prepare($conn, "UPDATE video_lectures SET is_public = NOT is_public WHERE id = ? AND teacher_id = ?");
    mysqli_stmt_bind_param($toggleStmt, "ii", $toggleId, $teacherId);
    
    if (mysqli_stmt_execute($toggleStmt)) {
        $message = "Visibility updated.";
        $msgType = "success";
    }
    mysqli_stmt_close($toggleStmt);
}

// Fetch my videos
$videos = mysqli_query($conn, "SELECT vl.*, s.subject_name 
                                FROM video_lectures vl 
                                JOIN subjects s ON vl.subject_id = s.id 
                                WHERE vl.teacher_id = $teacherId 
                                ORDER BY vl.created_at DESC");

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
        <h3><i class="fas fa-play-circle"></i> My Video Lectures</h3>
        <a href="add_video.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add New
        </a>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($videos) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>URL</th>
                        <th>Visibility</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($vid = mysqli_fetch_assoc($videos)): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($vid['title']); ?></strong>
                            <?php if (!empty($vid['description'])): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars(mb_strimwidth($vid['description'], 0, 60, '...')); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($vid['subject_name']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($vid['video_url']); ?>" target="_blank" 
                               class="btn btn-outline btn-sm" style="font-size: 12px;">
                                <i class="fas fa-external-link-alt"></i> Open
                            </a>
                        </td>
                        <td>
                            <a href="?toggle=<?php echo $vid['id']; ?>" class="badge <?php echo $vid['is_public'] ? 'badge-success' : 'badge-warning'; ?>" 
                               style="cursor: pointer; text-decoration: none;">
                                <i class="fas <?php echo $vid['is_public'] ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                                <?php echo $vid['is_public'] ? 'Public' : 'Private'; ?>
                            </a>
                        </td>
                        <td><?php echo date('d M Y', strtotime($vid['created_at'])); ?></td>
                        <td>
                            <a href="?delete=<?php echo $vid['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this video lecture?')">
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
            <i class="fas fa-video-slash"></i>
            <h3>No Video Lectures Added</h3>
            <p>Start by adding your first video lecture link.</p>
            <a href="add_video.php" class="btn btn-primary mt-2">
                <i class="fas fa-plus-circle"></i> Add Video
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
