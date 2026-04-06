<?php
/**
 * ClassSync - Database Setup Script
 * Run this once to create the database and seed data with proper bcrypt hashes
 * Access: http://localhost/ClassSync/setup.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS classsync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE classsync");

    // Create tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','teacher','student') NOT NULL,
            reg_number VARCHAR(20) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_role (role),
            INDEX idx_email (email)
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS subjects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS teacher_subjects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            teacher_id INT NOT NULL,
            subject_id INT NOT NULL,
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            UNIQUE KEY unique_teacher_subject (teacher_id, subject_id)
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS classwork (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject_id INT NOT NULL,
            teacher_id INT NOT NULL,
            topic VARCHAR(255) NOT NULL,
            description TEXT,
            deadline DATETIME NOT NULL,
            status ENUM('active','closed') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_deadline (deadline),
            INDEX idx_status (status)
        ) ENGINE=InnoDB
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            classwork_id INT NOT NULL,
            student_id INT NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            original_filename VARCHAR(255) NOT NULL,
            file_size INT DEFAULT 0,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('submitted','late','missed') DEFAULT 'submitted',
            FOREIGN KEY (classwork_id) REFERENCES classwork(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_student_classwork (classwork_id, student_id),
            INDEX idx_student (student_id),
            INDEX idx_classwork (classwork_id)
        ) ENGINE=InnoDB
    ");

    // Check if data already seeded
    $check = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($check == 0) {
        // Seed users with proper bcrypt hashes
        $adminPass = password_hash('admin@121', PASSWORD_DEFAULT);
        $teacherPass = password_hash('teacher@121', PASSWORD_DEFAULT);
        $studentPass = password_hash('student@121', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, reg_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Akash', 'akash@admin.compass', $adminPass, 'admin', NULL]);
        $stmt->execute(['Deep', 'deep@teacher.compass', $teacherPass, 'teacher', '100001']);
        $stmt->execute(['Aman', 'aman@student.compass', $studentPass, 'student', '20240001']);

        // Seed subjects
        $pdo->exec("INSERT INTO subjects (subject_name) VALUES ('Mathematics'), ('Physics'), ('Computer Science'), ('English'), ('Chemistry')");

        // Assign subjects to teacher (Deep = id 2)
        $pdo->exec("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (2, 1), (2, 2), (2, 3)");

        // Sample classwork
        $pdo->exec("
            INSERT INTO classwork (subject_id, teacher_id, topic, description, deadline, status) VALUES
            (1, 2, 'Linear Algebra – Matrix Operations', 'Solve the matrix operations problems from Chapter 3. Show all working steps.', DATE_ADD(NOW(), INTERVAL 3 DAY), 'active'),
            (2, 2, 'Newton''s Laws of Motion', 'Write detailed explanations of all three laws with real-world examples.', DATE_ADD(NOW(), INTERVAL 5 DAY), 'active'),
            (3, 2, 'HTML & CSS Basics', 'Create a responsive personal portfolio website using HTML5 and CSS3.', DATE_ADD(NOW(), INTERVAL 7 DAY), 'active')
        ");
    }

    // Create uploads directory
    $uploadDir = __DIR__ . '/uploads/submissions/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Create .htaccess to prevent PHP execution in uploads
    $htaccess = $uploadDir . '.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "php_flag engine off\nOptions -Indexes\n<FilesMatch \"\\.(php|phtml|php3|php4|php5|phps)$\">\n    Deny from all\n</FilesMatch>");
    }

    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>ClassSync Setup</title>';
    echo '<style>body{font-family:"Inter",sans-serif;background:#0a0e1a;color:#e0e6f0;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0}';
    echo '.box{background:rgba(30,41,72,0.8);border:1px solid rgba(99,132,255,0.2);border-radius:16px;padding:40px;text-align:center;max-width:500px}';
    echo 'h1{color:#6384ff;margin-bottom:10px}p{color:#8892b0;line-height:1.8}';
    echo '.success{color:#22c55e;font-size:1.2em;margin:20px 0}';
    echo 'a{display:inline-block;margin-top:20px;padding:12px 30px;background:linear-gradient(135deg,#6384ff,#9333ea);color:#fff;text-decoration:none;border-radius:8px;font-weight:600;transition:transform 0.2s}';
    echo 'a:hover{transform:scale(1.05)}</style></head><body>';
    echo '<div class="box">';
    echo '<h1>⚡ ClassSync Setup</h1>';
    echo '<p class="success">✅ Database created and configured successfully!</p>';
    echo '<p><strong>Default Accounts:</strong><br>';
    echo '👨‍💼 Admin: akash@admin.compass / admin@121<br>';
    echo '👨‍🏫 Teacher: deep@teacher.compass / teacher@121<br>';
    echo '👨‍🎓 Student: aman@student.compass / student@121</p>';
    echo '<a href="/ClassSync/pages/auth/login.php">→ Go to Login</a>';
    echo '</div></body></html>';

} catch (PDOException $e) {
    echo '<div style="color:red;font-family:monospace;padding:40px">';
    echo '<h2>Setup Error</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<p>Make sure XAMPP MySQL is running!</p>';
    echo '</div>';
}
