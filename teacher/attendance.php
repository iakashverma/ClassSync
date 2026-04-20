<?php
require_once __DIR__ . '/../includes/layout.php';
require_login('teacher');
$db = get_db();
$teacherId = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classworkId = (int) ($_POST['classwork_id'] ?? 0);
    $studentId = (int) ($_POST['student_id'] ?? 0);
    $status = $_POST['status'] ?? 'Present';
    $submissionStatus = $_POST['submission_status'] ?? 'Not Submitted';

    $allowed1 = ['Present', 'Absent'];
    $allowed2 = ['Submitted', 'Not Submitted'];

    $check = $db->prepare('SELECT id FROM classwork WHERE id=? AND teacher_id=?');
    $check->execute([$classworkId, $teacherId]);

    if ($check->fetch() && in_array($status, $allowed1, true) && in_array($submissionStatus, $allowed2, true)) {
        $stmt = $db->prepare('INSERT INTO attendance (student_id, classwork_id, status, submission_status)
                              VALUES (?, ?, ?, ?)
                              ON DUPLICATE KEY UPDATE status=VALUES(status), submission_status=VALUES(submission_status)');
        $stmt->execute([$studentId, $classworkId, $status, $submissionStatus]);
        flash_set('ok', 'Attendance updated.');
    } else {
        flash_set('err', 'Invalid attendance request.');
    }

    redirect('/teacher/attendance.php?classwork_id=' . $classworkId);
}

$classworkList = $db->prepare('SELECT id, title, deadline FROM classwork WHERE teacher_id=? ORDER BY created_at DESC');
$classworkList->execute([$teacherId]);
$classworks = $classworkList->fetchAll();

$selectedClasswork = (int) ($_GET['classwork_id'] ?? 0);
$students = [];
if ($selectedClasswork > 0) {
    $sStmt = $db->prepare('SELECT u.id, u.name, COALESCE(a.status, "Present") status, COALESCE(a.submission_status, "Not Submitted") submission_status
                           FROM classwork c
                           JOIN student_assignments sa ON sa.course_id=c.course_id AND sa.year_id=c.year_id AND sa.section_id=c.section_id
                           JOIN users u ON u.id=sa.student_id
                           LEFT JOIN attendance a ON a.student_id=u.id AND a.classwork_id=c.id
                           WHERE c.id=? AND c.teacher_id=?
                           ORDER BY u.name');
    $sStmt->execute([$selectedClasswork, $teacherId]);
    $students = $sStmt->fetchAll();
}

render_header('Attendance', 'teacher', '/teacher/attendance.php');
?>
<section class="panel">
    <h2>Choose Classwork</h2>
    <?php if ($m = flash_get('ok')): ?><div class="alert success"><?php echo e($m); ?></div><?php endif; ?>
    <?php if ($m = flash_get('err')): ?><div class="alert error"><?php echo e($m); ?></div><?php endif; ?>
    <form method="get" class="grid-form">
        <div class="full">
            <label>Classwork</label>
            <select name="classwork_id" onchange="this.form.submit()">
                <option value="">Select classwork</option>
                <?php foreach ($classworks as $cw): ?>
                    <option value="<?php echo (int)$cw['id']; ?>" <?php echo $selectedClasswork === (int)$cw['id'] ? 'selected' : ''; ?>>
                        <?php echo e($cw['title'].' (Deadline: '.$cw['deadline'].')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</section>

<?php if ($selectedClasswork > 0): ?>
<section class="panel">
    <h2>Mark Attendance & Submission Status</h2>
    <table>
        <tr><th>Student</th><th>Attendance</th><th>Submission Status</th><th>Action</th></tr>
        <?php foreach ($students as $st): ?>
            <tr>
                <td><?php echo e($st['name']); ?></td>
                <td>
                    <form method="post" class="grid-form" style="grid-template-columns:1fr 1fr auto;">
                        <input type="hidden" name="classwork_id" value="<?php echo $selectedClasswork; ?>">
                        <input type="hidden" name="student_id" value="<?php echo (int)$st['id']; ?>">
                        <select name="status">
                            <option <?php echo $st['status']==='Present'?'selected':''; ?>>Present</option>
                            <option <?php echo $st['status']==='Absent'?'selected':''; ?>>Absent</option>
                        </select>
                </td>
                <td>
                        <select name="submission_status">
                            <option <?php echo $st['submission_status']==='Submitted'?'selected':''; ?>>Submitted</option>
                            <option <?php echo $st['submission_status']==='Not Submitted'?'selected':''; ?>>Not Submitted</option>
                        </select>
                </td>
                <td><button class="btn teacher">Save</button></td>
                    </form>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php endif; ?>
<?php render_footer();
