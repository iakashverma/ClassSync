<?php
/**
 * ClassSync - Utility Functions
 */

/**
 * Check if deadline has passed
 */
function isDeadlinePassed($deadline) {
    return new DateTime() > new DateTime($deadline);
}

/**
 * Get time remaining until deadline (human-readable)
 */
function getTimeRemaining($deadline) {
    $now = new DateTime();
    $dl = new DateTime($deadline);

    if ($now > $dl) {
        return 'Expired';
    }

    $diff = $now->diff($dl);

    if ($diff->days > 0) {
        return $diff->days . 'd ' . $diff->h . 'h remaining';
    } elseif ($diff->h > 0) {
        return $diff->h . 'h ' . $diff->i . 'm remaining';
    } else {
        return $diff->i . 'm remaining';
    }
}

/**
 * Format deadline for display
 */
function formatDeadline($datetime) {
    $dt = new DateTime($datetime);
    return $dt->format('M d, Y \a\t h:i A');
}

/**
 * Format relative time
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->days > 7) return $ago->format('M d, Y');
    if ($diff->days > 0) return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min ago';
    return 'Just now';
}

/**
 * Get submission status for a student on specific classwork
 */
function getSubmissionStatus($pdo, $classworkId, $studentId) {
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE classwork_id = ? AND student_id = ?");
    $stmt->execute([$classworkId, $studentId]);
    $submission = $stmt->fetch();

    if ($submission) {
        return $submission['status']; // 'submitted' or 'late'
    }

    // Check if deadline has passed
    $stmt2 = $pdo->prepare("SELECT deadline FROM classwork WHERE id = ?");
    $stmt2->execute([$classworkId]);
    $classwork = $stmt2->fetch();

    if ($classwork && isDeadlinePassed($classwork['deadline'])) {
        return 'missed';
    }

    return 'pending';
}

/**
 * Get student progress stats
 */
function getStudentProgress($pdo, $studentId) {
    // Total classwork count
    $stmtTotal = $pdo->query("SELECT COUNT(*) FROM classwork");
    $total = (int)$stmtTotal->fetchColumn();

    // Submitted count
    $stmtSubmitted = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE student_id = ? AND status IN ('submitted','late')");
    $stmtSubmitted->execute([$studentId]);
    $submitted = (int)$stmtSubmitted->fetchColumn();

    // Missed count (deadline passed + no submission)
    $stmtMissed = $pdo->prepare("
        SELECT COUNT(*) FROM classwork c
        WHERE c.deadline < NOW()
        AND c.id NOT IN (
            SELECT s.classwork_id FROM submissions s WHERE s.student_id = ?
        )
    ");
    $stmtMissed->execute([$studentId]);
    $missed = (int)$stmtMissed->fetchColumn();

    // Pending
    $pending = $total - $submitted - $missed;
    if ($pending < 0) $pending = 0;

    // Progress percentage
    $progress = ($total > 0) ? round(($submitted / $total) * 100) : 0;

    return [
        'total' => $total,
        'submitted' => $submitted,
        'missed' => $missed,
        'pending' => $pending,
        'progress' => $progress,
    ];
}

/**
 * Sanitize and generate secure filename
 */
function generateSecureFilename($originalName) {
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    return bin2hex(random_bytes(16)) . '.' . $ext;
}

/**
 * Validate uploaded file
 */
function validateUploadedFile($file) {
    $errors = [];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed. Error code: ' . $file['error'];
        return $errors;
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = 'File size exceeds the maximum limit of 10MB.';
    }

    // Check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        $errors[] = 'Invalid file type. Only PDF, DOC, and DOCX files are allowed.';
    }

    // Check MIME type using finfo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        $errors[] = 'File content does not match allowed types.';
    }

    return $errors;
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}

/**
 * Escape HTML output
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
