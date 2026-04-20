<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('admin');
$db = get_db();
ensure_predefined_academic_data($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $student = (int) ($_POST['student_id'] ?? 0);
        $course = (int) ($_POST['course_id'] ?? 0);
        $year = (int) ($_POST['year_id'] ?? 0);
        $section = (int) ($_POST['section_id'] ?? 0);

        if ($student && $course && $year && $section) {
            $studentCheck = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'student' LIMIT 1");
            $studentCheck->execute([$student]);

            if (!$studentCheck->fetch()) {
                flash_set('err', 'Invalid student selected.');
            } elseif (academic_valid_class_selection($db, $course, $year, $section)) {
                $db->prepare('INSERT INTO student_assignments (student_id, course_id, year_id, section_id) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE course_id=VALUES(course_id), year_id=VALUES(year_id), section_id=VALUES(section_id)')
                   ->execute([$student, $course, $year, $section]);
                flash_set('ok', 'Student assignment saved.');
            } else {
                flash_set('err', 'Invalid predefined academic selection.');
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM student_assignments WHERE id=?')->execute([$id]);
        flash_set('ok', 'Assignment deleted.');
    }

    redirect('/admin/student_assignments.php');
}

$students = $db->query("SELECT id, name FROM users WHERE role='student' ORDER BY name")->fetchAll();
$courses = get_predefined_courses($db);
$years = get_predefined_years($db);
$sections = get_predefined_sections($db);
$list = $db->query('SELECT sa.id, u.name student_name, c.name course_name, y.year_name, s.section_name
                    FROM student_assignments sa
                    JOIN users u ON u.id=sa.student_id
                    JOIN courses c ON c.id=sa.course_id
                    JOIN years y ON y.id=sa.year_id
                    JOIN sections s ON s.id=sa.section_id
                    ORDER BY u.name')->fetchAll();

render_header('Student Assignments', 'admin', '/admin/student_assignments.php');
?>
<section class="panel">
    <h2>Assign Student</h2>
    <?php if ($m = flash_get('ok')): ?><div class="alert success"><?php echo e($m); ?></div><?php endif; ?>
    <?php if ($m = flash_get('err')): ?><div class="alert error"><?php echo e($m); ?></div><?php endif; ?>
    <form method="post" class="grid-form">
        <input type="hidden" name="action" value="add">
        <div><label>Student</label><select name="student_id" required><option value="">Select</option><?php foreach ($students as $s): ?><option value="<?php echo (int)$s['id']; ?>"><?php echo e($s['name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Course</label><select name="course_id" required><option value="">Select</option><?php foreach ($courses as $c): ?><option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Year</label><select name="year_id" required><option value="">Select</option><?php foreach ($years as $y): ?><option value="<?php echo (int)$y['id']; ?>"><?php echo e($y['year_name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Section</label><select name="section_id" required><option value="">Select</option><?php foreach ($sections as $s): ?><option value="<?php echo (int)$s['id']; ?>"><?php echo e('Section ' . $s['section_name']); ?></option><?php endforeach; ?></select></div>
        <div class="full"><button class="btn admin">Save Assignment</button></div>
    </form>
</section>
<section class="panel">
    <h2>Assignment List</h2>
    <table><tr><th>Student</th><th>Course</th><th>Year</th><th>Section</th><th>Action</th></tr>
        <?php foreach ($list as $row): ?>
            <tr>
                <td><?php echo e($row['student_name']); ?></td><td><?php echo e($row['course_name']); ?></td><td><?php echo e($row['year_name']); ?></td><td><?php echo e($row['section_name']); ?></td>
                <td><form method="post" data-confirm="Delete assignment?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>"><button class="btn">Delete</button></form></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
