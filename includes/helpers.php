<?php

require_once __DIR__ . '/db.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function flash_set(string $key, string $value): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'][$key] = $value;
}

function flash_get(string $key): ?string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function now_dt(): string
{
    return date('Y-m-d H:i:s');
}

function is_deadline_passed(string $deadline): bool
{
    return strtotime(now_dt()) > strtotime($deadline);
}

function validate_pdf_upload(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [false, 'File upload failed.'];
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return [false, 'PDF size must be under 5MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if ($mime !== 'application/pdf') {
        return [false, 'Only PDF files are allowed.'];
    }

    return [true, 'ok'];
}

function save_pdf_upload(array $file): string
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }

    $filename = uniqid('sub_', true) . '.pdf';
    $path = UPLOAD_DIR . $filename;
    move_uploaded_file($file['tmp_name'], $path);

    return $filename;
}

function get_student_assignment(int $studentId): ?array
{
    $stmt = get_db()->prepare('SELECT * FROM student_assignments WHERE student_id = ? LIMIT 1');
    $stmt->execute([$studentId]);
    return $stmt->fetch() ?: null;
}

function calculate_weekly_metrics(int $studentId, string $weekStart): array
{
    $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

    $attStmt = get_db()->prepare(
        'SELECT COUNT(*) AS total, SUM(CASE WHEN a.status = "Present" THEN 1 ELSE 0 END) AS present_count
         FROM attendance a
         JOIN classwork c ON c.id = a.classwork_id
         WHERE a.student_id = ? AND DATE(c.created_at) BETWEEN ? AND ?'
    );
    $attStmt->execute([$studentId, $weekStart, $weekEnd]);
    $att = $attStmt->fetch();

    $subStmt = get_db()->prepare(
        'SELECT COUNT(*) AS total, SUM(CASE WHEN s.id IS NOT NULL THEN 1 ELSE 0 END) AS done_count
         FROM classwork c
         JOIN student_assignments sa ON sa.course_id = c.course_id AND sa.year_id = c.year_id AND sa.section_id = c.section_id
         LEFT JOIN submissions s ON s.classwork_id = c.id AND s.student_id = sa.student_id
         WHERE sa.student_id = ? AND DATE(c.created_at) BETWEEN ? AND ?'
    );
    $subStmt->execute([$studentId, $weekStart, $weekEnd]);
    $sub = $subStmt->fetch();

    $attTotal = (int) ($att['total'] ?? 0);
    $presentCount = (int) ($att['present_count'] ?? 0);
    $subTotal = (int) ($sub['total'] ?? 0);
    $subDone = (int) ($sub['done_count'] ?? 0);

    $attendancePct = $attTotal > 0 ? round(($presentCount / $attTotal) * 100, 2) : 0;
    $submissionPct = $subTotal > 0 ? round(($subDone / $subTotal) * 100, 2) : 0;

    return [$attendancePct, $submissionPct];
}

function upsert_weekly_report(int $studentId, string $weekStart, string $remarks = ''): void
{
    [$attPct, $subPct] = calculate_weekly_metrics($studentId, $weekStart);

    $stmt = get_db()->prepare(
        'INSERT INTO reports (student_id, week_start, attendance_percentage, submission_rate, remarks)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE attendance_percentage = VALUES(attendance_percentage), submission_rate = VALUES(submission_rate), remarks = VALUES(remarks)'
    );
    $stmt->execute([$studentId, $weekStart, $attPct, $subPct, $remarks]);
}
