<?php
require_once __DIR__ . '/../includes/layout.php';
require_login('teacher');
$db = get_db();
$teacherId = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generate') {
    $weekStart = $_POST['week_start'] ?? date('Y-m-d', strtotime('monday this week'));

    $studentsStmt = $db->prepare('SELECT DISTINCT sa.student_id
                                  FROM teacher_assignments ta
                                  JOIN student_assignments sa ON sa.course_id=ta.course_id AND sa.year_id=ta.year_id AND sa.section_id=ta.section_id
                                  WHERE ta.teacher_id=?');
    $studentsStmt->execute([$teacherId]);
    $students = $studentsStmt->fetchAll();

    foreach ($students as $s) {
        upsert_weekly_report((int)$s['student_id'], $weekStart, 'Teacher generated weekly summary');
    }

    flash_set('ok', 'Weekly reports generated for assigned classes.');
    redirect('/teacher/reports.php');
}

$list = $db->prepare('SELECT DISTINCT r.*, u.name student_name
                      FROM reports r
                      JOIN users u ON u.id=r.student_id
                      JOIN student_assignments sa ON sa.student_id=u.id
                      JOIN teacher_assignments ta ON ta.course_id=sa.course_id AND ta.year_id=sa.year_id AND ta.section_id=sa.section_id
                      WHERE ta.teacher_id=?
                      ORDER BY r.week_start DESC, u.name');
$list->execute([$teacherId]);
$rows = $list->fetchAll();

render_header('Weekly Reports', 'teacher', '/teacher/reports.php');
?>
<section class="panel">
    <h2>Generate Weekly Reports (Assigned Classes)</h2>
    <?php if ($m = flash_get('ok')): ?><div class="alert success"><?php echo e($m); ?></div><?php endif; ?>
    <form method="post" class="grid-form">
        <input type="hidden" name="action" value="generate">
        <div><label>Week Start</label><input type="date" name="week_start" value="<?php echo date('Y-m-d', strtotime('monday this week')); ?>" required></div>
        <div style="align-self:end;"><button class="btn teacher">Generate</button></div>
    </form>
</section>
<section class="panel">
    <h2>Report List</h2>
    <table>
        <tr><th>Student</th><th>Week</th><th>Attendance %</th><th>Submission %</th><th>Remarks</th></tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo e($r['student_name']); ?></td>
                <td><?php echo e($r['week_start']); ?></td>
                <td><?php echo e($r['attendance_percentage']); ?></td>
                <td><?php echo e($r['submission_rate']); ?></td>
                <td><?php echo e($r['remarks']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
