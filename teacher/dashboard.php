<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('teacher');
$db = get_db();
ensure_predefined_academic_data($db);
$subjectColumn = academic_subject_column($db);
$teacherId = (int) current_user()['id'];

$assignments = $db->prepare('SELECT ta.id, c.name course_name, y.year_name, s.section_name, sb.' . $subjectColumn . ' AS subject_name
                             FROM teacher_assignments ta
                             JOIN courses c ON c.id=ta.course_id
                             JOIN years y ON y.id=ta.year_id
                             JOIN sections s ON s.id=ta.section_id
                             JOIN subjects sb ON sb.id=ta.subject_id
                             WHERE ta.teacher_id=?
                             ORDER BY c.name, y.year_name, s.section_name');
$assignments->execute([$teacherId]);
$assignmentRows = $assignments->fetchAll();

$cwStmt = $db->prepare('SELECT COUNT(*) FROM classwork WHERE teacher_id=?');
$cwStmt->execute([$teacherId]);
$totalClasswork = (int) $cwStmt->fetchColumn();

$subStmt = $db->prepare('SELECT COUNT(*) FROM submissions s JOIN classwork c ON c.id=s.classwork_id WHERE c.teacher_id=?');
$subStmt->execute([$teacherId]);
$totalSub = (int) $subStmt->fetchColumn();

render_header('Teacher Dashboard', 'teacher', '/teacher/dashboard.php');
?>
<div class="stats">
    <div class="stat-card"><div class="label">Assigned Classes</div><div class="value"><?php echo count($assignmentRows); ?></div></div>
    <div class="stat-card"><div class="label">Classwork Posted</div><div class="value"><?php echo $totalClasswork; ?></div></div>
    <div class="stat-card"><div class="label">Submissions Received</div><div class="value"><?php echo $totalSub; ?></div></div>
    <div class="stat-card"><div class="label">Today</div><div class="value"><?php echo date('d M'); ?></div></div>
</div>
<section class="panel">
    <h2>Assigned Classes</h2>
    <table>
        <tr><th>Course</th><th>Year</th><th>Section</th><th>Subject</th></tr>
        <?php foreach ($assignmentRows as $row): ?>
            <tr>
                <td><?php echo e($row['course_name']); ?></td>
                <td><?php echo e($row['year_name']); ?></td>
                <td><?php echo e($row['section_name']); ?></td>
                <td><?php echo e($row['subject_name']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
