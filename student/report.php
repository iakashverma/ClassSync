<?php
require_once __DIR__ . '/../includes/layout.php';
require_login('student');
$db = get_db();
$studentId = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generate') {
    $weekStart = $_POST['week_start'] ?? date('Y-m-d', strtotime('monday this week'));
    upsert_weekly_report($studentId, $weekStart, 'Auto-generated personal report');
    flash_set('ok', 'Report generated.');
    redirect('/student/report.php');
}

$rows = $db->prepare('SELECT * FROM reports WHERE student_id=? ORDER BY week_start DESC');
$rows->execute([$studentId]);
$reports = $rows->fetchAll();

$fb = $db->prepare('SELECT c.title, f.remarks, f.marks, f.created_at
                    FROM feedback f
                    JOIN submissions s ON s.id=f.submission_id
                    JOIN classwork c ON c.id=s.classwork_id
                    WHERE s.student_id=?
                    ORDER BY f.created_at DESC LIMIT 20');
$fb->execute([$studentId]);
$feedbackRows = $fb->fetchAll();

render_header('Weekly Reports', 'student', '/student/report.php');
?>
<section class="panel">
    <h2>Generate Weekly Report</h2>
    <?php if ($m = flash_get('ok')): ?><div class="alert success"><?php echo e($m); ?></div><?php endif; ?>
    <form method="post" class="grid-form">
        <input type="hidden" name="action" value="generate">
        <div><label>Week Start</label><input type="date" name="week_start" value="<?php echo date('Y-m-d', strtotime('monday this week')); ?>" required></div>
        <div style="align-self:end;"><button class="btn student">Generate</button></div>
    </form>
</section>
<section class="panel">
    <h2>Report History</h2>
    <table><tr><th>Week</th><th>Attendance %</th><th>Submission %</th><th>Remarks</th></tr>
        <?php foreach ($reports as $r): ?>
            <tr><td><?php echo e($r['week_start']); ?></td><td><?php echo e($r['attendance_percentage']); ?></td><td><?php echo e($r['submission_rate']); ?></td><td><?php echo e($r['remarks']); ?></td></tr>
        <?php endforeach; ?>
    </table>
</section>
<section class="panel">
    <h2>Teacher Feedback</h2>
    <table><tr><th>Classwork</th><th>Remarks</th><th>Marks</th><th>Date</th></tr>
        <?php foreach ($feedbackRows as $f): ?>
            <tr><td><?php echo e($f['title']); ?></td><td><?php echo e($f['remarks']); ?></td><td><?php echo e($f['marks']); ?></td><td><?php echo e($f['created_at']); ?></td></tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
