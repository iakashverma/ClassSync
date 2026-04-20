<?php
require_once __DIR__ . '/../includes/layout.php';
require_login('teacher');
$db = get_db();
$teacherId = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submissionId = (int) ($_POST['submission_id'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');
    $marks = (float) ($_POST['marks'] ?? 0);

    $check = $db->prepare('SELECT s.id FROM submissions s JOIN classwork c ON c.id=s.classwork_id WHERE s.id=? AND c.teacher_id=?');
    $check->execute([$submissionId, $teacherId]);

    if ($check->fetch()) {
        $stmt = $db->prepare('INSERT INTO feedback (submission_id, teacher_id, remarks, marks)
                              VALUES (?, ?, ?, ?)
                              ON DUPLICATE KEY UPDATE remarks=VALUES(remarks), marks=VALUES(marks)');
        $stmt->execute([$submissionId, $teacherId, $remarks, $marks]);
        flash_set('ok', 'Feedback saved.');
    } else {
        flash_set('err', 'Unauthorized submission.');
    }
    redirect('/teacher/feedback.php');
}

$list = $db->prepare('SELECT s.id submission_id, u.name student_name, c.title, f.remarks, f.marks
                      FROM submissions s
                      JOIN users u ON u.id=s.student_id
                      JOIN classwork c ON c.id=s.classwork_id
                      LEFT JOIN feedback f ON f.submission_id=s.id
                      WHERE c.teacher_id=?
                      ORDER BY s.submitted_at DESC');
$list->execute([$teacherId]);
$rows = $list->fetchAll();

render_header('Feedback', 'teacher', '/teacher/feedback.php');
?>
<section class="panel">
    <h2>Provide Remarks & Marks</h2>
    <?php if ($m = flash_get('ok')): ?><div class="alert success"><?php echo e($m); ?></div><?php endif; ?>
    <?php if ($m = flash_get('err')): ?><div class="alert error"><?php echo e($m); ?></div><?php endif; ?>
    <table>
        <tr><th>Student</th><th>Classwork</th><th>Remarks</th><th>Marks</th><th>Save</th></tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo e($r['student_name']); ?></td>
                <td><?php echo e($r['title']); ?></td>
                <td>
                    <form method="post" class="grid-form" style="grid-template-columns:1fr 120px auto;">
                        <input type="hidden" name="submission_id" value="<?php echo (int)$r['submission_id']; ?>">
                        <input name="remarks" value="<?php echo e((string)($r['remarks'] ?? '')); ?>">
                </td>
                <td><input type="number" step="0.5" min="0" max="100" name="marks" value="<?php echo e((string)($r['marks'] ?? '0')); ?>"></td>
                <td><button class="btn teacher">Save</button></td>
                    </form>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
