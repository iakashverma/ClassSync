<?php
/**
 * ClassSync API - Get Student Stats
 * Returns progress data for dashboard charts
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized.']);
    exit;
}

$studentId = (int)($_GET['student_id'] ?? $_SESSION['user_id']);

// Only allow students to get their own stats, or admins/teachers to get any
if ($_SESSION['user_role'] === 'student' && $studentId !== $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Access denied.']);
    exit;
}

$progress = getStudentProgress($pdo, $studentId);

echo json_encode([
    'success' => true,
    'data' => $progress
]);
