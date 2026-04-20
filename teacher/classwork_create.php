<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('teacher');
$db = get_db();
ensure_predefined_academic_data($db);
$subjectColumn = academic_subject_column($db);
$teacherId = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignmentId = (int) ($_POST['assignment_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $homework = trim($_POST['homework_instructions'] ?? '');
    $deadline = $_POST['deadline'] ?? '';

    $aStmt = $db->prepare('SELECT * FROM teacher_assignments WHERE id=? AND teacher_id=?');
    $aStmt->execute([$assignmentId, $teacherId]);
    $assignment = $aStmt->fetch();

    if ($assignment && $title !== '' && $description !== '' && $homework !== '' && strtotime($deadline) > time()) {
        $stmt = $db->prepare('INSERT INTO classwork (teacher_id, assignment_id, course_id, year_id, section_id, subject_id, title, description, homework_instructions, deadline)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $teacherId,
            $assignmentId,
            $assignment['course_id'],
            $assignment['year_id'],
            $assignment['section_id'],
            $assignment['subject_id'],
            $title,
            $description,
            $homework,
            date('Y-m-d H:i:s', strtotime($deadline)),
        ]);
        flash_set('ok', 'Classwork posted.');
    } else {
        flash_set('err', 'Invalid input or deadline must be future date/time.');
    }

    redirect('/teacher/classwork_create.php');
}

$assignments = $db->prepare('SELECT ta.id, c.name course_name, y.year_name, s.section_name, sb.' . $subjectColumn . ' AS subject_name
                             FROM teacher_assignments ta
                             JOIN courses c ON c.id=ta.course_id
                             JOIN years y ON y.id=ta.year_id
                             JOIN sections s ON s.id=ta.section_id
                             JOIN subjects sb ON sb.id=ta.subject_id
                             WHERE ta.teacher_id=? ORDER BY c.name,y.year_name,s.section_name');
$assignments->execute([$teacherId]);
$list = $assignments->fetchAll();

$recent = $db->prepare('SELECT c.*, sb.' . $subjectColumn . ' AS subject_name, sec.section_name, y.year_name, co.name course_name
                        FROM classwork c
                        JOIN subjects sb ON sb.id=c.subject_id
                        JOIN sections sec ON sec.id=c.section_id
                        JOIN years y ON y.id=c.year_id
                        JOIN courses co ON co.id=c.course_id
                        WHERE c.teacher_id=? ORDER BY c.created_at DESC LIMIT 20');
$recent->execute([$teacherId]);
$recentRows = $recent->fetchAll();

render_header('Create Classwork', 'teacher', '/teacher/classwork_create.php');
?>
<section class="panel">
    <h2>Upload Daily Classwork</h2>
    <?php if ($m = flash_get('ok')): ?><div class="alert success"><?php echo e($m); ?></div><?php endif; ?>
    <?php if ($m = flash_get('err')): ?><div class="alert error"><?php echo e($m); ?></div><?php endif; ?>
    <form method="post" class="grid-form">
        <div class="full">
            <label>Assigned Class</label>
            <select name="assignment_id" required>
                <option value="">Select assignment</option>
                <?php foreach ($list as $a): ?>
                    <option value="<?php echo (int)$a['id']; ?>"><?php echo e($a['course_name'].' '.$a['year_name'].' '.$a['section_name'].' - '.$a['subject_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label>Title</label><input name="title" required></div>
        <div><label>Deadline (date+time)</label><input type="datetime-local" name="deadline" required></div>
        <div class="full"><label>Description</label><textarea name="description" required></textarea></div>
        <div class="full"><label>Homework Instructions</label><textarea name="homework_instructions" required></textarea></div>
        <div class="full"><button class="btn teacher">Post Classwork</button></div>
    </form>
</section>
<section class="panel">
    <h2>Recent Classwork</h2>
    <table><tr><th>Class</th><th>Subject</th><th>Title</th><th>Deadline</th></tr>
        <?php foreach ($recentRows as $r): ?>
            <tr><td><?php echo e($r['course_name'].' '.$r['year_name'].' '.$r['section_name']); ?></td><td><?php echo e($r['subject_name']); ?></td><td><?php echo e($r['title']); ?></td><td><?php echo e($r['deadline']); ?></td></tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
