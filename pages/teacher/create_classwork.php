<?php
/**
 * ClassSync - Teacher: Create Classwork
 */
$pageTitle = 'Create Classwork';
require_once __DIR__ . '/../../includes/header.php';
requireRole('teacher');

$teacherId = $_SESSION['user_id'];

// Get teacher's assigned subjects
$subjects = $pdo->prepare("
    SELECT s.* FROM subjects s
    JOIN teacher_subjects ts ON s.id = ts.subject_id
    WHERE ts.teacher_id = ?
    ORDER BY s.subject_name
");
$subjects->execute([$teacherId]);
$teacherSubjects = $subjects->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $topic = trim($_POST['topic'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $deadline = $_POST['deadline'] ?? '';

    if (empty($subjectId) || empty($topic) || empty($deadline)) {
        $error = 'Please fill in all required fields (Subject, Topic, Deadline).';
    } else {
        // Validate subject belongs to teacher
        $check = $pdo->prepare("SELECT id FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?");
        $check->execute([$teacherId, $subjectId]);
        if (!$check->fetch()) {
            $error = 'Invalid subject selected.';
        } else {
            // Validate deadline is in the future
            $dlDate = new DateTime($deadline);
            if ($dlDate <= new DateTime()) {
                $error = 'Deadline must be in the future.';
            } else {
                $formattedDeadline = $dlDate->format('Y-m-d H:i:s');
                $stmt = $pdo->prepare("INSERT INTO classwork (subject_id, teacher_id, topic, description, deadline) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$subjectId, $teacherId, $topic, $description, $formattedDeadline]);

                setFlash('success', 'Classwork "' . $topic . '" created successfully!');
                header('Location: ' . BASE_URL . '/pages/teacher/my_classwork.php');
                exit;
            }
        }
    }
}
?>

<div class="page-header">
    <h1>➕ Create New Classwork</h1>
    <p>Assign work to students with a deadline</p>
</div>

<div style="max-width: 700px;">
    <div class="card">
        <?php if (!empty($error)): ?>
            <div class="auth-error" style="margin-bottom: 20px;">
                <span>❌</span> <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($teacherSubjects)): ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3>No Subjects Assigned</h3>
                <p>Ask an admin to assign subjects to your account before creating classwork.</p>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Subject *</label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">Select subject...</option>
                        <?php foreach ($teacherSubjects as $s): ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo (isset($_POST['subject_id']) && $_POST['subject_id'] == $s['id']) ? 'selected' : ''; ?>>
                            <?php echo e($s['subject_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Topic *</label>
                    <input type="text" name="topic" class="form-control"
                           placeholder="e.g. Linear Algebra – Matrix Operations"
                           value="<?php echo e($_POST['topic'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="Describe the assignment, instructions, requirements..."><?php echo e($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Deadline *</label>
                    <input type="datetime-local" name="deadline" id="deadlineInput" class="form-control"
                           value="<?php echo e($_POST['deadline'] ?? ''); ?>" required>

                    <div class="quick-options">
                        <button type="button" class="quick-btn" onclick="setQuickDeadline('+2days')">+2 Days</button>
                        <button type="button" class="quick-btn" onclick="setQuickDeadline('+3days')">+3 Days</button>
                        <button type="button" class="quick-btn" onclick="setQuickDeadline('+5days')">+5 Days</button>
                        <button type="button" class="quick-btn" onclick="setQuickDeadline('+1week')">+1 Week</button>
                        <button type="button" class="quick-btn" onclick="setQuickDeadline('next_monday')">Next Monday 12PM</button>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        🚀 Create Classwork
                    </button>
                    <a href="<?php echo BASE_URL; ?>/pages/teacher/my_classwork.php" class="btn btn-secondary btn-lg">
                        Cancel
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
