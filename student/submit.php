<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('student');
$db = get_db();
ensure_predefined_academic_data($db);
$subjectColumn = academic_subject_column($db);
$studentId = (int) current_user()['id'];
$assignment = get_student_assignment($studentId);

if (!$assignment) {
    render_header('Submit Work', 'student', '/student/submit.php');
    echo '<section class="panel"><div class="alert error">No class assignment found.</div></section>';
    render_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classworkId = (int) ($_POST['classwork_id'] ?? 0);
    $type = $_POST['type'] ?? 'text';

    $cwStmt = $db->prepare('SELECT * FROM classwork WHERE id=? AND course_id=? AND year_id=? AND section_id=? LIMIT 1');
    $cwStmt->execute([$classworkId, $assignment['course_id'], $assignment['year_id'], $assignment['section_id']]);
    $cw = $cwStmt->fetch();

    if (!$cw) {
        flash_set('err', 'Unauthorized classwork access.');
    } elseif (is_deadline_passed($cw['deadline'])) {
        flash_set('err', 'Submission closed: deadline passed.');
    } else {
        if ($type === 'text') {
            $content = trim($_POST['content'] ?? '');
            if ($content === '') {
                flash_set('err', 'Please enter your answer.');
                redirect('/student/submit.php');
            }

            $stmt = $db->prepare('INSERT INTO submissions (classwork_id, student_id, type, content, file_path)
                                  VALUES (?, ?, "text", ?, NULL)
                                  ON DUPLICATE KEY UPDATE type=VALUES(type), content=VALUES(content), file_path=VALUES(file_path), submitted_at=CURRENT_TIMESTAMP');
            $stmt->execute([$classworkId, $studentId, $content]);
            flash_set('ok', 'Text submission saved.');

            $db->prepare('INSERT INTO attendance (student_id, classwork_id, status, submission_status)
                          VALUES (?, ?, "Present", "Submitted")
                          ON DUPLICATE KEY UPDATE submission_status="Submitted"')->execute([$studentId, $classworkId]);
        } elseif ($type === 'pdf') {
            if (!isset($_FILES['pdf_file'])) {
                flash_set('err', 'Please upload a PDF file.');
                redirect('/student/submit.php');
            }

            [$ok, $msg] = validate_pdf_upload($_FILES['pdf_file']);
            if (!$ok) {
                flash_set('err', $msg);
                redirect('/student/submit.php');
            }

            $filename = save_pdf_upload($_FILES['pdf_file']);
            $stmt = $db->prepare('INSERT INTO submissions (classwork_id, student_id, type, content, file_path)
                                  VALUES (?, ?, "pdf", NULL, ?)
                                  ON DUPLICATE KEY UPDATE type=VALUES(type), content=VALUES(content), file_path=VALUES(file_path), submitted_at=CURRENT_TIMESTAMP');
            $stmt->execute([$classworkId, $studentId, $filename]);
            flash_set('ok', 'PDF submission saved.');

            $db->prepare('INSERT INTO attendance (student_id, classwork_id, status, submission_status)
                          VALUES (?, ?, "Present", "Submitted")
                          ON DUPLICATE KEY UPDATE submission_status="Submitted"')->execute([$studentId, $classworkId]);
        }
    }

    redirect('/student/submit.php');
}

$classworkStmt = $db->prepare('SELECT c.id, c.title, c.deadline, sb.' . $subjectColumn . ' AS subject_name
                               FROM classwork c
                               JOIN subjects sb ON sb.id=c.subject_id
                               WHERE c.course_id=? AND c.year_id=? AND c.section_id=?
                               ORDER BY c.deadline ASC');
$classworkStmt->execute([$assignment['course_id'], $assignment['year_id'], $assignment['section_id']]);
$classworks = $classworkStmt->fetchAll();

render_header('Submit Work', 'student', '/student/submit.php');
?>
<section class="panel">
    <h2>Submit Homework</h2>
    <?php if ($m = flash_get('ok')): ?><div class="alert success"><?php echo e($m); ?></div><?php endif; ?>
    <?php if ($m = flash_get('err')): ?><div class="alert error"><?php echo e($m); ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="grid-form">
        <div class="full">
            <label>Classwork</label>
            <select name="classwork_id" required>
                <option value="">Select classwork</option>
                <?php foreach ($classworks as $cw): ?>
                    <?php $closed = strtotime($cw['deadline']) < time(); ?>
                    <option value="<?php echo (int)$cw['id']; ?>" <?php echo $closed ? 'disabled' : ''; ?>>
                        <?php echo e($cw['subject_name'].' - '.$cw['title'].' | Deadline: '.$cw['deadline'].($closed ? ' (Closed)' : '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Submission Type</label>
            <select name="type" id="submissionType">
                <option value="text">Typed Text</option>
                <option value="pdf">PDF Upload</option>
            </select>
        </div>
        <div id="textInput" class="full">
            <label>Answer</label>
            <textarea name="content"></textarea>
        </div>
        <div id="pdfInput" class="full" style="display:none;">
            <label>PDF File</label>
            <input type="file" name="pdf_file" accept="application/pdf">
        </div>
        <div class="full"><button class="btn student">Submit</button></div>
    </form>
</section>
<script>
const typeSel = document.getElementById('submissionType');
const textInput = document.getElementById('textInput');
const pdfInput = document.getElementById('pdfInput');
function updateInputs() {
    if (typeSel.value === 'pdf') {
        textInput.style.display = 'none';
        pdfInput.style.display = 'block';
    } else {
        textInput.style.display = 'block';
        pdfInput.style.display = 'none';
    }
}
typeSel.addEventListener('change', updateInputs);
updateInputs();
</script>
<?php render_footer();
