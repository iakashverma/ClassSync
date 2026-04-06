<?php
/**
 * ClassSync - Teacher: Download Submissions
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('teacher');

$teacherId = $_SESSION['user_id'];

// Download single file
if (isset($_GET['id'])) {
    $submissionId = (int)$_GET['id'];

    $stmt = $pdo->prepare("
        SELECT s.*, c.teacher_id
        FROM submissions s
        JOIN classwork c ON s.classwork_id = c.id
        WHERE s.id = ? AND c.teacher_id = ?
    ");
    $stmt->execute([$submissionId, $teacherId]);
    $sub = $stmt->fetch();

    if (!$sub) {
        setFlash('error', 'Submission not found.');
        header('Location: ' . BASE_URL . '/pages/teacher/my_classwork.php');
        exit;
    }

    $filePath = UPLOAD_DIR . $sub['file_path'];

    if (!file_exists($filePath)) {
        setFlash('error', 'File not found on server.');
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/pages/teacher/my_classwork.php');
        exit;
    }

    // Serve file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);

    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $sub['original_filename'] . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache');
    readfile($filePath);
    exit;
}

// Download all submissions for a classwork as ZIP
if (isset($_GET['classwork_id']) && isset($_GET['all'])) {
    $classworkId = (int)$_GET['classwork_id'];

    // Verify teacher owns this classwork
    $stmt = $pdo->prepare("SELECT * FROM classwork WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$classworkId, $teacherId]);
    $classwork = $stmt->fetch();

    if (!$classwork) {
        setFlash('error', 'Classwork not found.');
        header('Location: ' . BASE_URL . '/pages/teacher/my_classwork.php');
        exit;
    }

    // Get all submissions
    $stmt = $pdo->prepare("
        SELECT s.*, u.name as student_name
        FROM submissions s
        JOIN users u ON s.student_id = u.id
        WHERE s.classwork_id = ?
    ");
    $stmt->execute([$classworkId]);
    $submissions = $stmt->fetchAll();

    if (empty($submissions)) {
        setFlash('error', 'No submissions to download.');
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/pages/teacher/my_classwork.php');
        exit;
    }

    // Create ZIP
    $zipFile = tempnam(sys_get_temp_dir(), 'classsync_') . '.zip';
    $zip = new ZipArchive();

    if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
        setFlash('error', 'Could not create ZIP file.');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    foreach ($submissions as $sub) {
        $filePath = UPLOAD_DIR . $sub['file_path'];
        if (file_exists($filePath)) {
            $ext = pathinfo($sub['original_filename'], PATHINFO_EXTENSION);
            $zipName = $sub['student_name'] . '_' . $sub['original_filename'];
            $zip->addFile($filePath, $zipName);
        }
    }

    $zip->close();

    // Serve ZIP
    $safeTopicName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $classwork['topic']);
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="submissions_' . $safeTopicName . '.zip"');
    header('Content-Length: ' . filesize($zipFile));
    readfile($zipFile);
    unlink($zipFile);
    exit;
}

// No valid params
header('Location: ' . BASE_URL . '/pages/teacher/my_classwork.php');
exit;
