<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('admin');
$db = get_db();
ensure_predefined_academic_data($db);

function generate_teacher_id(PDO $db): string
{
    for ($i = 0; $i < 50; $i++) {
        $id = (string) random_int(1000, 9999);
        $stmt = $db->prepare('SELECT id FROM users WHERE teacher_unique_id = ? LIMIT 1');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            return $id;
        }
    }
    throw new RuntimeException('Unable to generate unique teacher ID.');
}

function first_name_slug(string $name): string
{
    $first = trim(explode(' ', trim($name))[0] ?? 'user');
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $first));
    return $slug !== '' ? $slug : 'user';
}

$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $courseId = (int) ($_POST['course_id'] ?? 0);
    $yearId = (int) ($_POST['year_id'] ?? 0);
    $sectionId = (int) ($_POST['section_id'] ?? 0);
    $subjectId = (int) ($_POST['subject_id'] ?? 0);

    if ($name === '' || $courseId <= 0 || $yearId <= 0 || $sectionId <= 0 || $subjectId <= 0) {
        flash_set('err', 'Please fill all fields.');
        redirect('/admin/add_teacher.php');
    }

    if (!academic_valid_class_selection($db, $courseId, $yearId, $sectionId, $subjectId)) {
        flash_set('err', 'Invalid predefined academic selection.');
        redirect('/admin/add_teacher.php');
    }

    [$canAssign, $assignMessage] = academic_can_assign_subject_to_class($db, $courseId, $yearId, $sectionId, $subjectId);
    if (!$canAssign) {
        flash_set('err', $assignMessage);
        redirect('/admin/add_teacher.php');
    }

    try {
        $db->beginTransaction();

        $teacherId4 = generate_teacher_id($db);
        $email = first_name_slug($name) . $teacherId4 . '@classsync.com';
        $passwordHash = password_hash('classsync@121', PASSWORD_DEFAULT);

        $insUser = $db->prepare('INSERT INTO users (name, teacher_unique_id, email, password, role) VALUES (?, ?, ?, ?, "teacher")');
        $insUser->execute([$name, $teacherId4, $email, $passwordHash]);
        $teacherUserId = (int) $db->lastInsertId();

        $insAssign = $db->prepare('INSERT INTO teacher_assignments (teacher_id, subject_id, course_id, year_id, section_id) VALUES (?, ?, ?, ?, ?)');
        $insAssign->execute([$teacherUserId, $subjectId, $courseId, $yearId, $sectionId]);

        $db->commit();
        $success = ['id' => $teacherId4, 'email' => $email];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        flash_set('err', 'Could not add teacher. Try a different name or mapping.');
        redirect('/admin/add_teacher.php');
    }
}

$courses = get_predefined_courses($db);
$years = get_predefined_years($db);
$sections = get_predefined_sections($db);
$subjects = get_predefined_subjects($db);

render_header('Add Teacher', 'admin', '/admin/add_teacher.php');
?>
<section class="panel">
    <h2>Add Teacher</h2>
    <?php if ($m = flash_get('err')): ?><div class="alert error"><?php echo e($m); ?></div><?php endif; ?>
    <?php if ($success): ?>
        <div class="alert success">
            Teacher Added Successfully. Teacher ID: <strong><?php echo e($success['id']); ?></strong>,
            Email: <strong><?php echo e($success['email']); ?></strong>,
            Password: <strong>classsync@121</strong>
        </div>
    <?php endif; ?>

    <form method="post" class="grid-form">
        <div class="full"><label>Teacher Name</label><input name="name" required></div>
        <div><label>Course</label><select name="course_id" required><option value="">Select</option><?php foreach ($courses as $c): ?><option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Year</label><select name="year_id" required><option value="">Select</option><?php foreach ($years as $y): ?><option value="<?php echo (int)$y['id']; ?>"><?php echo e($y['year_name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Section</label><select name="section_id" required><option value="">Select</option><?php foreach ($sections as $s): ?><option value="<?php echo (int)$s['id']; ?>"><?php echo e('Section ' . $s['section_name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Subject</label><select name="subject_id" required><option value="">Select</option><?php foreach ($subjects as $sb): ?><option value="<?php echo (int)$sb['id']; ?>"><?php echo e($sb['subject_name']); ?></option><?php endforeach; ?></select></div>
        <div class="full"><button class="btn admin" type="submit">Add Teacher</button></div>
    </form>
</section>
<?php render_footer();
