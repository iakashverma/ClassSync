<?php
/**
 * ClassSync - Database Configuration
 * PDO connection + application constants
 */

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'classsync');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application constants
define('BASE_URL', '/ClassSync');
define('UPLOAD_DIR', __DIR__ . '/../uploads/submissions/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx']);
define('ALLOWED_MIME_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);

// PDO Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
