<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('admin');
$db = get_db();
ensure_predefined_academic_data($db);

$courses = get_predefined_courses($db);
render_header('Courses', 'admin', '/admin/courses.php');
?>
<section class="panel">
    <h2>Predefined Courses</h2>
    <p class="block-sub">Courses are fixed by system policy and cannot be added, edited, or removed by admin.</p>
    <table>
        <tr><th>ID</th><th>Course Name</th><th>Type</th></tr>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?php echo (int) $course['id']; ?></td>
                <td><?php echo e($course['name']); ?></td>
                <td><span class="badge ok">Fixed</span></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
