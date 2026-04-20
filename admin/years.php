<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('admin');
$db = get_db();
ensure_predefined_academic_data($db);

$courses = get_predefined_courses($db);
$years = get_predefined_years($db);
$yearNames = array_map(static function (array $row): string {
    return $row['year_name'];
}, $years);

render_header('Years', 'admin', '/admin/years.php');
?>
<section class="panel">
    <h2>Predefined Academic Years</h2>
    <p class="block-sub">Each course follows the same fixed year structure. Year entries are read-only.</p>
    <table>
        <tr><th>ID</th><th>Year</th><th>Type</th></tr>
        <?php foreach ($years as $year): ?>
            <tr>
                <td><?php echo (int) $year['id']; ?></td>
                <td><?php echo e($year['year_name']); ?></td>
                <td><span class="badge ok">Fixed</span></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<section class="panel">
    <h2>Course-Year Structure</h2>
    <table>
        <tr><th>Course</th><th>Available Years</th></tr>
        <?php foreach ($courses as $course): ?>
            <tr>
                <td><?php echo e($course['name']); ?></td>
                <td><?php echo e(implode(', ', $yearNames)); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
