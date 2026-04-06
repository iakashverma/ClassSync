<?php
// teacher/add_video.php - Add video lecture link
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('teacher');

$pageTitle = "Add Video Lecture";
$teacherId = getUserId();
$error = "";
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subjectId = intval($_POST['subject_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $videoUrl = trim($_POST['video_url']);
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    
    // Validate
    if (empty($title) || empty($subjectId) || empty($videoUrl)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
        $error = "Please enter a valid URL.";
    } else {
        // Auto-extract YouTube thumbnail
        $thumbnailUrl = getYouTubeThumbnailUrl($videoUrl);
        
        $stmt = mysqli_prepare($conn, "INSERT INTO video_lectures (teacher_id, subject_id, title, description, video_url, thumbnail_url, is_public) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissssi", $teacherId, $subjectId, $title, $description, $videoUrl, $thumbnailUrl, $isPublic);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Video lecture added successfully!";
        } else {
            $error = "Failed to add video. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Helper: extract YouTube thumbnail
function getYouTubeThumbnailUrl($url) {
    $videoId = '';
    if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $m)) {
        $videoId = $m[1];
    } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $m)) {
        $videoId = $m[1];
    } elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $m)) {
        $videoId = $m[1];
    }
    return $videoId ? "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg" : '';
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
        <h3><i class="fas fa-video"></i> Add Video Lecture</h3>
        <a href="manage_videos.php" class="btn btn-outline btn-sm">
            <i class="fas fa-list"></i> My Videos
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="">
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
                       placeholder="e.g., Lecture 3 - Sorting Algorithms Explained" required>
            </div>
            
            <div class="form-group">
                <label for="video_url">Video URL <span style="color: var(--danger);">*</span></label>
                <input type="url" name="video_url" id="video_url" class="form-control" 
                       placeholder="https://www.youtube.com/watch?v=..." required>
                <small style="color: var(--gray-500); display: block; margin-top: 6px;">
                    <i class="fab fa-youtube" style="color: #ef4444;"></i> 
                    YouTube links will auto-generate thumbnails. Other URLs are also supported.
                </small>
            </div>
            
            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea name="description" id="description" class="form-control" 
                          placeholder="Brief description of this video lecture..."></textarea>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_public" value="1" checked 
                           style="width: 18px; height: 18px; accent-color: var(--primary);">
                    <span>Show on public homepage (visible to all visitors)</span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Add Video Lecture
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
