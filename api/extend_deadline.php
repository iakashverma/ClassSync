<?php
/**
 * ClassSync API - Extend Deadline
 * Teacher extends a classwork deadline via AJAX
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Must be logged in as teacher
if (!isLoggedIn() || $_SESSION['user_role'] !== 'teacher') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$classworkId = (int)($input['classwork_id'] ?? 0);
$newDeadline = $input['new_deadline'] ?? '';
$teacherId = $_SESSION['user_id'];

if ($classworkId <= 0 || empty($newDeadline)) {
    echo json_encode(['success' => false, 'error' => 'Missing classwork ID or new deadline.']);
    exit;
}

// Validate the new deadline is in the future
try {
    $dlDate = new DateTime($newDeadline);
    if ($dlDate <= new DateTime()) {
        echo json_encode(['success' => false, 'error' => 'New deadline must be in the future.']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Invalid deadline format.']);
    exit;
}

// Verify teacher owns this classwork
$stmt = $pdo->prepare("SELECT * FROM classwork WHERE id = ? AND teacher_id = ?");
$stmt->execute([$classworkId, $teacherId]);
$classwork = $stmt->fetch();

if (!$classwork) {
    echo json_encode(['success' => false, 'error' => 'Classwork not found or access denied.']);
    exit;
}

// 🔁 EXTEND DEADLINE (CORE FEATURE)
$formattedDeadline = $dlDate->format('Y-m-d H:i:s');
$stmt = $pdo->prepare("UPDATE classwork SET deadline = ?, status = 'active' WHERE id = ? AND teacher_id = ?");
$stmt->execute([$formattedDeadline, $classworkId, $teacherId]);

echo json_encode([
    'success' => true,
    'message' => 'Deadline extended to ' . $dlDate->format('M d, Y h:i A'),
    'new_deadline' => $formattedDeadline
]);
