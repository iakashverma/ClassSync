<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('admin');
$db = get_db();
ensure_predefined_academic_data($db);
$subjectColumn = academic_subject_column($db);

$teachers = $db->query('SELECT u.id, u.name, u.teacher_unique_id, u.email, c.name AS course_name, y.year_name, s.section_name, sb.' . $subjectColumn . ' AS subject_name
                        FROM users u
                        LEFT JOIN teacher_assignments ta ON ta.teacher_id = u.id
                        LEFT JOIN courses c ON c.id = ta.course_id
                        LEFT JOIN years y ON y.id = ta.year_id
                        LEFT JOIN sections s ON s.id = ta.section_id
                        LEFT JOIN subjects sb ON sb.id = ta.subject_id
                        WHERE u.role = "teacher"
                        ORDER BY u.created_at DESC')->fetchAll();

$students = $db->query('SELECT u.id, u.name, u.student_unique_id, u.email, c.name AS course_name, y.year_name, s.section_name
                        FROM users u
                        LEFT JOIN student_assignments sa ON sa.student_id = u.id
                        LEFT JOIN courses c ON c.id = sa.course_id
                        LEFT JOIN years y ON y.id = sa.year_id
                        LEFT JOIN sections s ON s.id = sa.section_id
                        WHERE u.role = "student"
                        ORDER BY u.created_at DESC')->fetchAll();

render_header('View Records', 'admin', '/admin/view_records.php');
?>
<section class="panel">
    <h2>Teacher Records</h2>
    <table>
        <tr><th>Name</th><th>4-digit ID</th><th>Email</th><th>Course</th><th>Year</th><th>Section</th><th>Subject</th></tr>
        <?php foreach ($teachers as $t): ?>
            <tr>
                <td><?php echo e($t['name']); ?></td>
                <td><?php echo e((string) $t['teacher_unique_id']); ?></td>
                <td><?php echo e($t['email']); ?></td>
                <td><?php echo e((string) ($t['course_name'] ?? '')); ?></td>
                <td><?php echo e((string) ($t['year_name'] ?? '')); ?></td>
                <td><?php echo e((string) ($t['section_name'] ?? '')); ?></td>
                <td><?php echo e((string) ($t['subject_name'] ?? '')); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<section class="panel">
    <h2>Student Records</h2>
    <table>
        <tr><th>Name</th><th>6-digit ID</th><th>Email</th><th>Course</th><th>Year</th><th>Section</th></tr>
        <?php foreach ($students as $s): ?>
            <tr>
                <td><?php echo e($s['name']); ?></td>
                <td><?php echo e((string) $s['student_unique_id']); ?></td>
                <td><?php echo e($s['email']); ?></td>
                <td><?php echo e((string) ($s['course_name'] ?? '')); ?></td>
                <td><?php echo e((string) ($s['year_name'] ?? '')); ?></td>
                <td><?php echo e((string) ($s['section_name'] ?? '')); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
