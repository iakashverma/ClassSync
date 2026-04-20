<?php
require_once __DIR__ . '/../includes/layout.php';
require_login('admin');
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generate') {
    $weekStart = $_POST['week_start'] ?? date('Y-m-d', strtotime('monday this week'));
    $students = $db->query("SELECT id FROM users WHERE role='student'")->fetchAll();
    foreach ($students as $s) {
        upsert_weekly_report((int)$s['id'], $weekStart, 'Auto-generated weekly report');
    }
    flash_set('ok', 'Weekly reports generated for all students.');
    redirect('/admin/reports.php');
}

$reports = $db->query('SELECT r.*, u.name student_name FROM reports r JOIN users u ON u.id=r.student_id ORDER BY r.week_start DESC, u.name')->fetchAll();
render_header('Reports', 'admin', '/admin/reports.php');
?>
<section class="panel">
    <h2>Generate Weekly Reports</h2>
    <?php if ($m = flash_get('ok')): ?><div class="alert success"><?php echo e($m); ?></div><?php endif; ?>
    <form method="post" class="grid-form">
        <input type="hidden" name="action" value="generate">
        <div><label>Week Start (Monday)</label><input type="date" name="week_start" value="<?php echo date('Y-m-d', strtotime('monday this week')); ?>" required></div>
        <div style="align-self:end;"><button class="btn admin">Generate</button></div>
    </form>
</section>
<section class="panel">
    <h2>Report List</h2>
    <table>
        <tr><th>Student</th><th>Week Start</th><th>Attendance %</th><th>Submission %</th><th>Remarks</th></tr>
        <?php foreach ($reports as $r): ?>
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
