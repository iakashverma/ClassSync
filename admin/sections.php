<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('admin');
$db = get_db();
ensure_predefined_academic_data($db);

$years = get_predefined_years($db);
$sections = get_predefined_sections($db);
$sectionNames = array_map(static function (array $row): string {
    return 'Section ' . $row['section_name'];
}, $sections);

render_header('Sections', 'admin', '/admin/sections.php');
?>
<section class="panel">
    <h2>Predefined Sections</h2>
    <p class="block-sub">Section values are fixed globally and cannot be created, edited, or deleted by admin.</p>
    <table>
        <tr><th>ID</th><th>Section</th><th>Type</th></tr>
        <?php foreach ($sections as $section): ?>
            <tr>
                <td><?php echo (int) $section['id']; ?></td>
                <td><?php echo e('Section ' . $section['section_name']); ?></td>
                <td><span class="badge ok">Fixed</span></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<section class="panel">
    <h2>Year-Section Structure</h2>
    <table><tr><th>Year</th><th>Sections</th></tr>
        <?php foreach ($years as $year): ?>
            <tr>
                <td><?php echo e($year['year_name']); ?></td>
                <td><?php echo e(implode(', ', $sectionNames)); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
