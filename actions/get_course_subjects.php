<?php
// AJAX endpoint: Returns subjects for a given course_id
require_once '../config/database.php';

header('Content-Type: application/json');

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, subject_name FROM course_subjects WHERE course_id = ? ORDER BY subject_name ASC");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

$stmt->close();
echo json_encode($subjects);
?>
