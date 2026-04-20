<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('student');
$db = get_db();
ensure_predefined_academic_data($db);
$subjectColumn = academic_subject_column($db);
$studentId = (int) current_user()['id'];
$assignment = get_student_assignment($studentId);

if (!$assignment) {
    render_header('Student Dashboard', 'student', '/student/dashboard.php');
    echo '<section class="panel"><div class="alert error">No class assignment found. Contact admin.</div></section>';
    render_footer();
    exit;
}

$tasksStmt = $db->prepare('SELECT c.*, sb.' . $subjectColumn . ' AS subject_name,
                          (SELECT id FROM submissions s WHERE s.classwork_id=c.id AND s.student_id=? LIMIT 1) submitted_id
                          FROM classwork c
                          JOIN subjects sb ON sb.id=c.subject_id
                          WHERE c.course_id=? AND c.year_id=? AND c.section_id=?
                          ORDER BY c.deadline ASC LIMIT 10');
$tasksStmt->execute([$studentId, $assignment['course_id'], $assignment['year_id'], $assignment['section_id']]);
$tasks = $tasksStmt->fetchAll();

$att = $db->prepare('SELECT COUNT(*) total, SUM(CASE WHEN status="Present" THEN 1 ELSE 0 END) present FROM attendance WHERE student_id=?');
$att->execute([$studentId]);
$attRow = $att->fetch();
$attPct = ((int)$attRow['total'] > 0) ? round(((int)$attRow['present'] / (int)$attRow['total']) * 100, 2) : 0;

$sub = $db->prepare('SELECT COUNT(*) FROM submissions WHERE student_id=?');
$sub->execute([$studentId]);
$subCount = (int)$sub->fetchColumn();

$missedStmt = $db->prepare('SELECT COUNT(*)
                            FROM classwork c
                            LEFT JOIN submissions s ON s.classwork_id=c.id AND s.student_id=?
                            WHERE c.course_id=? AND c.year_id=? AND c.section_id=? AND c.deadline < NOW() AND s.id IS NULL');
$missedStmt->execute([$studentId, $assignment['course_id'], $assignment['year_id'], $assignment['section_id']]);
$missed = (int) $missedStmt->fetchColumn();

render_header('Student Dashboard', 'student', '/student/dashboard.php');
?>
<div class="stats">
    <div class="stat-card"><div class="label">Today's Tasks</div><div class="value"><?php echo count($tasks); ?></div></div>
    <div class="stat-card"><div class="label">Submitted</div><div class="value"><?php echo $subCount; ?></div></div>
    <div class="stat-card"><div class="label">Attendance %</div><div class="value"><?php echo $attPct; ?></div></div>
    <div class="stat-card"><div class="label">Missed</div><div class="value"><?php echo $missed; ?></div></div>
</div>
<section class="panel">
    <h2>Alerts</h2>
    <?php if ($missed > 0): ?>
        <div class="alert error">You have <?php echo $missed; ?> missed submission(s).</div>
    <?php else: ?>
        <div class="alert success">No missed submissions.</div>
    <?php endif; ?>
    <?php if ($attPct < 75): ?>
        <div class="alert info">Attendance alert: your attendance is below 75%.</div>
    <?php endif; ?>
</section>
<section class="panel">
    <h2>Task Overview</h2>
    <table><tr><th>Subject</th><th>Title</th><th>Deadline</th><th>Status</th></tr>
        <?php foreach ($tasks as $t): ?>
            <?php $remainingHours = (strtotime($t['deadline']) - time()) / 3600; ?>
            <tr>
                <td><?php echo e($t['subject_name']); ?></td>
                <td><?php echo e($t['title']); ?></td>
                <td><?php echo e($t['deadline']); ?>
                    <?php if ($remainingHours > 0 && $remainingHours <= 24): ?>
                        <span class="badge warn">Deadline soon</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($t['submitted_id']): ?><span class="badge ok">Submitted</span>
                    <?php elseif (strtotime($t['deadline']) < time()): ?><span class="badge danger">Missed</span>
                    <?php else: ?><span class="badge warn">Pending</span><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
