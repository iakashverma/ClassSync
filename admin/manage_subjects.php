<?php
// admin/manage_subjects.php - Add and delete subjects
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('admin');

$pageTitle = "Manage Subjects";
$error = "";
$success = "";

// handle delete subject
if (isset($_GET['delete'])) {
    $subjectId = intval($_GET['delete']);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM subjects WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $subjectId);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Subject deleted successfully.";
    } else {
        $error = "Cannot delete subject. It might have assignments linked to it.";
    }
    mysqli_stmt_close($stmt);
}

// handle add subject
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subjectName = trim($_POST['subject_name']);
    
    if (empty($subjectName)) {
        $error = "Subject name cannot be empty.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO subjects (subject_name) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "s", $subjectName);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Subject added successfully!";
        } else {
            $error = "Failed to add subject.";
        }
        mysqli_stmt_close($stmt);
    }
}

// fetch all subjects with assignment count
$subjects = mysqli_query($conn, "SELECT s.*, COUNT(c.id) as assignment_count 
                                  FROM subjects s 
                                  LEFT JOIN classwork c ON s.id = c.subject_id 
                                  GROUP BY s.id 
                                  ORDER BY s.subject_name");

require_once '../includes/header.php';
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<!-- Add Subject Form -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-plus-circle"></i> Add New Subject</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" style="display: flex; gap: 12px; align-items: flex-end;">
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label for="subject_name">Subject Name</label>
                <input type="text" name="subject_name" id="subject_name" class="form-control" 
                       placeholder="e.g., Machine Learning" required>
            </div>
            <button type="submit" class="btn btn-primary" style="height: 46px;">
                <i class="fas fa-plus"></i> Add
            </button>
        </form>
    </div>
</div>

<!-- Subjects List -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-book"></i> All Subjects</h3>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($subjects) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject Name</th>
                        <th>Assignments</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; while ($sub = mysqli_fetch_assoc($subjects)): ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                        <td><span class="badge badge-info"><?php echo $sub['assignment_count']; ?></span></td>
                        <td>
                            <a href="manage_subjects.php?delete=<?php echo $sub['id']; ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this subject? Related assignments will also be deleted.')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h3>No Subjects</h3>
            <p>Add subjects using the form above.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
