<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('admin');
$db = get_db();
ensure_predefined_academic_data($db);

function generate_student_id(PDO $db): string
{
    for ($i = 0; $i < 50; $i++) {
        $id = (string) random_int(100000, 999999);
        $stmt = $db->prepare('SELECT id FROM users WHERE student_unique_id = ? LIMIT 1');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            return $id;
        }
    }
    throw new RuntimeException('Unable to generate unique student ID.');
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

    if ($name === '' || $courseId <= 0 || $yearId <= 0 || $sectionId <= 0) {
        flash_set('err', 'Please fill all fields.');
        redirect('/admin/add_student.php');
    }

    if (!academic_valid_class_selection($db, $courseId, $yearId, $sectionId)) {
        flash_set('err', 'Invalid predefined academic selection.');
        redirect('/admin/add_student.php');
    }

    try {
        $db->beginTransaction();

        $studentId6 = generate_student_id($db);
        $email = first_name_slug($name) . $studentId6 . '@classsync.com';
        $passwordHash = password_hash('classsync@121', PASSWORD_DEFAULT);

        $insUser = $db->prepare('INSERT INTO users (name, student_unique_id, email, password, role) VALUES (?, ?, ?, ?, "student")');
        $insUser->execute([$name, $studentId6, $email, $passwordHash]);
        $studentUserId = (int) $db->lastInsertId();

        $insAssign = $db->prepare('INSERT INTO student_assignments (student_id, course_id, year_id, section_id) VALUES (?, ?, ?, ?)');
        $insAssign->execute([$studentUserId, $courseId, $yearId, $sectionId]);

        $db->commit();
        $success = ['id' => $studentId6, 'email' => $email];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        flash_set('err', 'Could not add student. Try again.');
        redirect('/admin/add_student.php');
    }
}

$courses = get_predefined_courses($db);
$years = get_predefined_years($db);
$sections = get_predefined_sections($db);

render_header('Add Student', 'admin', '/admin/add_student.php');
?>
<section class="panel">
    <h2>Add Student</h2>
    <?php if ($m = flash_get('err')): ?><div class="alert error"><?php echo e($m); ?></div><?php endif; ?>
    <?php if ($success): ?>
        <div class="alert success">
            Student Added Successfully. Student ID: <strong><?php echo e($success['id']); ?></strong>,
            Email: <strong><?php echo e($success['email']); ?></strong>,
            Password: <strong>classsync@121</strong>
        </div>
    <?php endif; ?>

    <form method="post" class="grid-form">
        <div class="full"><label>Student Name</label><input name="name" required></div>
        <div><label>Course</label><select name="course_id" required><option value="">Select</option><?php foreach ($courses as $c): ?><option value="<?php echo (int)$c['id']; ?>"><?php echo e($c['name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Year</label><select name="year_id" required><option value="">Select</option><?php foreach ($years as $y): ?><option value="<?php echo (int)$y['id']; ?>"><?php echo e($y['year_name']); ?></option><?php endforeach; ?></select></div>
        <div><label>Section</label><select name="section_id" required><option value="">Select</option><?php foreach ($sections as $s): ?><option value="<?php echo (int)$s['id']; ?>"><?php echo e('Section ' . $s['section_name']); ?></option><?php endforeach; ?></select></div>
        <div class="full"><button class="btn admin" type="submit">Add Student</button></div>
    </form>
</section>
<?php render_footer();
