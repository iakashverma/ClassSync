<?php
/**
 * ClassSync - Admin: Manage Subjects
 */
$pageTitle = 'Manage Subjects';
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

// Handle Add Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['subject_name'] ?? '');
        if (!empty($name)) {
            $check = $pdo->prepare("SELECT id FROM subjects WHERE subject_name = ?");
            $check->execute([$name]);
            if (!$check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
                $stmt->execute([$name]);
                setFlash('success', 'Subject "' . $name . '" added!');
            } else {
                setFlash('error', 'Subject already exists.');
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        if ($subjectId > 0) {
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$subjectId]);
            setFlash('success', 'Subject deleted.');
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($_POST['action'] === 'assign') {
        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        if ($teacherId > 0 && $subjectId > 0) {
            $check = $pdo->prepare("SELECT id FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?");
            $check->execute([$teacherId, $subjectId]);
            if (!$check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
                $stmt->execute([$teacherId, $subjectId]);
                setFlash('success', 'Subject assigned to teacher.');
            } else {
                setFlash('error', 'Already assigned.');
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($_POST['action'] === 'unassign') {
        $id = (int)($_POST['assignment_id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM teacher_subjects WHERE id = ?")->execute([$id]);
            setFlash('success', 'Assignment removed.');
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Get data
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();
$teachers = $pdo->query("SELECT id, name FROM users WHERE role = 'teacher' ORDER BY name")->fetchAll();

// Get teacher assignments
$assignments = $pdo->query("
    SELECT ts.id, ts.teacher_id, ts.subject_id, u.name as teacher_name, s.subject_name
    FROM teacher_subjects ts
    JOIN users u ON ts.teacher_id = u.id
    JOIN subjects s ON ts.subject_id = s.id
    ORDER BY u.name, s.subject_name
")->fetchAll();
?>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1>📚 Manage Subjects</h1>
            <p>Create subjects and assign them to teachers</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addSubjectModal')">
            ➕ Add Subject
        </button>
    </div>
</div>

<div class="grid-2">
    <!-- Subjects List -->
    <div class="card">
        <div class="card-header">
            <h2>📚 All Subjects</h2>
            <span class="badge badge-info"><?php echo count($subjects); ?> total</span>
        </div>
        <?php if (empty($subjects)): ?>
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3>No subjects yet</h3>
                <p>Add your first subject to get started.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $i => $s): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><strong><?php echo e($s['subject_name']); ?></strong></td>
                            <td>
                                <form method="POST" id="deleteSub<?php echo $s['id']; ?>" style="display:inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="subject_id" value="<?php echo $s['id']; ?>">
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('Delete subject <?php echo e($s['subject_name']); ?>? This will also delete all related classwork.', 'deleteSub<?php echo $s['id']; ?>')">
                                        🗑️
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Teacher-Subject Assignments -->
    <div class="card">
        <div class="card-header">
            <h2>🔗 Teacher Assignments</h2>
            <button class="btn btn-sm btn-primary" onclick="openModal('assignModal')">➕ Assign</button>
        </div>
        <?php if (empty($assignments)): ?>
            <div class="empty-state">
                <div class="empty-icon">🔗</div>
                <h3>No assignments yet</h3>
                <p>Assign subjects to teachers.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Subject</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td><strong><?php echo e($a['teacher_name']); ?></strong></td>
                            <td><?php echo e($a['subject_name']); ?></td>
                            <td>
                                <form method="POST" id="unassign<?php echo $a['id']; ?>" style="display:inline">
                                    <input type="hidden" name="action" value="unassign">
                                    <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('Remove this assignment?', 'unassign<?php echo $a['id']; ?>')">
                                        ❌
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal-overlay" id="addSubjectModal">
    <div class="modal">
        <div class="modal-header">
            <h2>➕ Add Subject</h2>
            <button class="modal-close" onclick="closeModal('addSubjectModal')">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label class="form-label">Subject Name</label>
                <input type="text" name="subject_name" class="form-control" placeholder="e.g. Mathematics" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addSubjectModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Subject</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Subject Modal -->
<div class="modal-overlay" id="assignModal">
    <div class="modal">
        <div class="modal-header">
            <h2>🔗 Assign Subject to Teacher</h2>
            <button class="modal-close" onclick="closeModal('assignModal')">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="assign">
            <div class="form-group">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-control" required>
                    <option value="">Select teacher...</option>
                    <?php foreach ($teachers as $t): ?>
                    <option value="<?php echo $t['id']; ?>"><?php echo e($t['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-control" required>
                    <option value="">Select subject...</option>
                    <?php foreach ($subjects as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo e($s['subject_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('assignModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
