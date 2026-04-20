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
    render_header('Classwork', 'student', '/student/classwork.php');
    echo '<section class="panel"><div class="alert error">No class assignment found.</div></section>';
    render_footer();
    exit;
}

$stmt = $db->prepare('SELECT c.*, sb.' . $subjectColumn . ' AS subject_name,
                      (SELECT id FROM submissions s WHERE s.classwork_id=c.id AND s.student_id=? LIMIT 1) submitted_id
                      FROM classwork c
                      JOIN subjects sb ON sb.id=c.subject_id
                      WHERE c.course_id=? AND c.year_id=? AND c.section_id=?
                      ORDER BY c.created_at DESC');
$stmt->execute([$studentId, $assignment['course_id'], $assignment['year_id'], $assignment['section_id']]);
$rows = $stmt->fetchAll();

render_header('Classwork', 'student', '/student/classwork.php');
?>
<section class="panel">
    <h2>Assigned Classwork</h2>
    <table>
        <tr><th>Subject</th><th>Title</th><th>Description</th><th>Homework</th><th>Deadline</th><th>Status</th></tr>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo e($r['subject_name']); ?></td>
                <td><?php echo e($r['title']); ?></td>
                <td><?php echo nl2br(e($r['description'])); ?></td>
                <td><?php echo nl2br(e($r['homework_instructions'])); ?></td>
                <td><?php echo e($r['deadline']); ?></td>
                <td>
                    <?php if ($r['submitted_id']): ?><span class="badge ok">Submitted</span>
                    <?php elseif (strtotime($r['deadline']) < time()): ?><span class="badge danger">Closed</span>
                    <?php else: ?><span class="badge warn">Open</span><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
