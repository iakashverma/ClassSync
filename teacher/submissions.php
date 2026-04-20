<?php
require_once __DIR__ . '/../includes/layout.php';
require_login('teacher');
$db = get_db();
$teacherId = (int) current_user()['id'];

$cwStmt = $db->prepare('SELECT id, title FROM classwork WHERE teacher_id=? ORDER BY created_at DESC');
$cwStmt->execute([$teacherId]);
$classworks = $cwStmt->fetchAll();

$selected = (int) ($_GET['classwork_id'] ?? 0);
$rows = [];
if ($selected > 0) {
    $stmt = $db->prepare('SELECT s.id submission_id, s.type, s.content, s.file_path, s.submitted_at, u.name student_name, c.deadline
                          FROM submissions s
                          JOIN users u ON u.id=s.student_id
                          JOIN classwork c ON c.id=s.classwork_id
                          WHERE s.classwork_id=? AND c.teacher_id=?
                          ORDER BY s.submitted_at DESC');
    $stmt->execute([$selected, $teacherId]);
    $rows = $stmt->fetchAll();
}

render_header('Submissions', 'teacher', '/teacher/submissions.php');
?>
<section class="panel">
    <h2>Review Submissions</h2>
    <form method="get" class="grid-form">
        <div class="full">
            <label>Classwork</label>
            <select name="classwork_id" onchange="this.form.submit()">
                <option value="">Select</option>
                <?php foreach ($classworks as $cw): ?>
                    <option value="<?php echo (int)$cw['id']; ?>" <?php echo $selected === (int)$cw['id'] ? 'selected' : ''; ?>><?php echo e($cw['title']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</section>

<?php foreach ($rows as $r): ?>
<section class="panel">
    <h2><?php echo e($r['student_name']); ?> - <?php echo e($r['submitted_at']); ?></h2>
    <?php if ($r['type'] === 'text'): ?>
        <div class="card"><?php echo nl2br(e($r['content'] ?? '')); ?></div>
    <?php else: ?>
        <iframe class="pdf-frame" src="<?php echo BASE_URL . '/uploads/' . e($r['file_path']); ?>"></iframe>
    <?php endif; ?>
</section>
<?php endforeach; ?>
<?php render_footer();
