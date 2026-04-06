<?php
/**
 * ClassSync API - Check Deadline
 * Returns whether a classwork deadline has passed
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized.']);
    exit;
}

$classworkId = (int)($_GET['classwork_id'] ?? 0);

if ($classworkId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid classwork ID.']);
    exit;
}

$stmt = $pdo->prepare("SELECT deadline FROM classwork WHERE id = ?");
$stmt->execute([$classworkId]);
$classwork = $stmt->fetch();

if (!$classwork) {
    echo json_encode(['success' => false, 'error' => 'Classwork not found.']);
    exit;
}

$passed = isDeadlinePassed($classwork['deadline']);
$remaining = getTimeRemaining($classwork['deadline']);

echo json_encode([
    'success' => true,
    'passed' => $passed,
    'remaining' => $remaining,
    'deadline' => $classwork['deadline']
]);
