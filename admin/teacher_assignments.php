<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('admin');
$db = get_db();
ensure_predefined_academic_data($db);
$subjectColumn = academic_subject_column($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $teacher = (int) ($_POST['teacher_id'] ?? 0);
        $subject = (int) ($_POST['subject_id'] ?? 0);
        $course = (int) ($_POST['course_id'] ?? 0);
        $year = (int) ($_POST['year_id'] ?? 0);
        $section = (int) ($_POST['section_id'] ?? 0);

        if ($teacher && $subject && $course && $year && $section) {
            $teacherCheck = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'teacher' LIMIT 1");
            $teacherCheck->execute([$teacher]);

            if (!$teacherCheck->fetch()) {
                flash_set('err', 'Invalid teacher selected.');
            } elseif (!academic_valid_class_selection($db, $course, $year, $section, $subject)) {
                flash_set('err', 'Invalid predefined academic selection.');
            } else {
                [$canAssign, $assignMessage] = academic_can_assign_subject_to_class($db, $course, $year, $section, $subject);
                if (!$canAssign) {
                    flash_set('err', $assignMessage);
                } else {
                    try {
                        $db->prepare('INSERT INTO teacher_assignments (teacher_id, subject_id, course_id, year_id, section_id) VALUES (?, ?, ?, ?, ?)')
                           ->execute([$teacher, $subject, $course, $year, $section]);
                        flash_set('ok', 'Assignment created.');
                    } catch (Throwable $e) {
                        flash_set('err', 'Duplicate assignment.');
                    }
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $db->prepare('DELETE FROM teacher_assignments WHERE id=?')->execute([$id]);
        flash_set('ok', 'Assignment deleted.');
    }

    redirect('/admin/teacher_assignments.php');
}

$teachers = $db->query("SELECT id, name FROM users WHERE role='teacher' ORDER BY name")->fetchAll();
$courses = get_predefined_courses($db);
$years = get_predefined_years($db);
$sections = get_predefined_sections($db);
$subjects = get_predefined_subjects($db);
$list = $db->query('SELECT ta.id, u.name teacher_name, sb.' . $subjectColumn . ' AS subject_name, c.name course_name, y.year_name, s.section_name
                    FROM teacher_assignments ta
                    JOIN users u ON u.id=ta.teacher_id
                    JOIN subjects sb ON sb.id=ta.subject_id
                    JOIN courses c ON c.id=ta.course_id
                    JOIN years y ON y.id=ta.year_id
                    JOIN sections s ON s.id=ta.section_id
                    ORDER BY u.name')->fetchAll();

render_header('Teacher Assignments', 'admin', '/admin/teacher_assignments.php');
?>
<section class="panel">
    <h2>Assign Teacher</h2>
    <?php if ($m = flash_get('ok')): ?><div class="alert success"><?php echo e($m); ?></div><?php endif; ?>
    <?php if ($m = flash_get('err')): ?><div class="alert error"><?php echo e($m); ?></div><?php endif; ?>
    <form method="post" class="grid-form">
        <input type="hidden" name="action" value="add">
        <div><label>Teacher</label><select name="teacher_id" required><option value="">Select</option><?php foreach ($teachers as $t): ?><option value="<?php echo (int)$t['id']; ?>"><?php echo e($t['name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Course</label><select name="course_id" required><option value="">Select</option><?php foreach ($courses as $c): ?><option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Year</label><select name="year_id" required><option value="">Select</option><?php foreach ($years as $y): ?><option value="<?php echo (int)$y['id']; ?>"><?php echo e($y['year_name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Section</label><select name="section_id" required><option value="">Select</option><?php foreach ($sections as $s): ?><option value="<?php echo (int)$s['id']; ?>"><?php echo e('Section ' . $s['section_name']); ?></option><?php endforeach; ?></select></div>
        <div class="full"><label>Subject</label><select name="subject_id" required><option value="">Select</option><?php foreach ($subjects as $s): ?><option value="<?php echo (int)$s['id']; ?>"><?php echo e($s['subject_name']); ?></option><?php endforeach; ?></select></div>
        <div class="full"><button class="btn admin">Assign</button></div>
    </form>
</section>
<section class="panel">
    <h2>Assignment List</h2>
    <table><tr><th>Teacher</th><th>Course</th><th>Year</th><th>Section</th><th>Subject</th><th>Action</th></tr>
        <?php foreach ($list as $row): ?>
            <tr>
                <td><?php echo e($row['teacher_name']); ?></td><td><?php echo e($row['course_name']); ?></td><td><?php echo e($row['year_name']); ?></td><td><?php echo e($row['section_name']); ?></td><td><?php echo e($row['subject_name']); ?></td>
                <td><form method="post" data-confirm="Delete assignment?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>"><button class="btn">Delete</button></form></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
