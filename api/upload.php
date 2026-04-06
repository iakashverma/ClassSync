<?php
/**
 * ClassSync API - File Upload
 * Handles student assignment submissions via AJAX
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Must be logged in as student
if (!isLoggedIn() || $_SESSION['user_role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$studentId = $_SESSION['user_id'];
$classworkId = (int)($_POST['classwork_id'] ?? 0);

if ($classworkId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid classwork ID.']);
    exit;
}

// Check classwork exists
$stmt = $pdo->prepare("SELECT * FROM classwork WHERE id = ?");
$stmt->execute([$classworkId]);
$classwork = $stmt->fetch();

if (!$classwork) {
    echo json_encode(['success' => false, 'error' => 'Classwork not found.']);
    exit;
}

// ⏳ DEADLINE CHECK (CORE FEATURE)
if (isDeadlinePassed($classwork['deadline'])) {
    echo json_encode(['success' => false, 'error' => '🚫 Time Up – Submission Closed. The deadline has passed.']);
    exit;
}

// Check if already submitted
$checkSub = $pdo->prepare("SELECT id FROM submissions WHERE classwork_id = ? AND student_id = ?");
$checkSub->execute([$classworkId, $studentId]);
if ($checkSub->fetch()) {
    echo json_encode(['success' => false, 'error' => 'You have already submitted this assignment.']);
    exit;
}

// Validate file
if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded.']);
    exit;
}

$file = $_FILES['file'];
$errors = validateUploadedFile($file);

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

// Create upload directory if not exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Generate secure filename
$secureFilename = generateSecureFilename($file['name']);
$destPath = UPLOAD_DIR . $secureFilename;

// Move file
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file. Please try again.']);
    exit;
}

// Insert submission record
try {
    $stmt = $pdo->prepare("
        INSERT INTO submissions (classwork_id, student_id, file_path, original_filename, file_size, status)
        VALUES (?, ?, ?, ?, ?, 'submitted')
    ");
    $stmt->execute([
        $classworkId,
        $studentId,
        $secureFilename,
        $file['name'],
        $file['size']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Assignment submitted successfully!',
        'submission_id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    // Clean up uploaded file on DB error
    if (file_exists($destPath)) {
        unlink($destPath);
    }
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
}
