<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('admin');
$db = get_db();
ensure_predefined_academic_data($db);

$subjects = get_predefined_subjects($db);
$coverage = $db->query('SELECT c.name course_name, y.year_name, s.section_name, COUNT(DISTINCT ta.subject_id) mapped_subjects
                        FROM teacher_assignments ta
                        JOIN courses c ON c.id = ta.course_id
                        JOIN years y ON y.id = ta.year_id
                        JOIN sections s ON s.id = ta.section_id
                        GROUP BY ta.course_id, ta.year_id, ta.section_id
                        ORDER BY c.name, y.year_name, s.section_name')->fetchAll();

render_header('Subjects', 'admin', '/admin/subjects.php');
?>
<section class="panel">
    <h2>Predefined Subjects</h2>
    <p class="block-sub">The subject catalog is fixed system-wide. For each class, assign any 5 out of these 6 subjects.</p>
    <table>
        <tr><th>ID</th><th>Subject</th><th>Type</th></tr>
        <?php foreach ($subjects as $subject): ?>
            <tr>
                <td><?php echo (int) $subject['id']; ?></td>
                <td><?php echo e($subject['subject_name']); ?></td>
                <td><span class="badge ok">Fixed</span></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<section class="panel">
    <h2>Class Subject Coverage (Max 5)</h2>
    <table><tr><th>Class</th><th>Mapped Subjects</th><th>Status</th></tr>
        <?php foreach ($coverage as $row): ?>
            <tr>
                <td><?php echo e($row['course_name'] . ' | ' . $row['year_name'] . ' | Section ' . $row['section_name']); ?></td>
                <td><?php echo (int) $row['mapped_subjects']; ?> / 5</td>
                <td>
                    <?php if ((int) $row['mapped_subjects'] === 5): ?>
                        <span class="badge ok">Ready</span>
                    <?php elseif ((int) $row['mapped_subjects'] > 5): ?>
                        <span class="badge danger">Invalid</span>
                    <?php else: ?>
                        <span class="badge warn">Incomplete</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
