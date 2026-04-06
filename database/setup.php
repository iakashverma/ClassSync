<?php
// Run this file once to set up the database
// Visit: http://localhost/ClassSync/database/setup.php

$host = 'localhost';
$username = 'root';
$password = '';

// Connect without selecting database
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Class Sync - Database Setup</h2>";

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS classsync");
$conn->select_db("classsync");
echo "<p>✅ Database 'classsync' created</p>";

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'teacher', 'student') NOT NULL,
        registration_number VARCHAR(20) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS reports (
        report_id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT NOT NULL,
        subject VARCHAR(100) NOT NULL,
        topic VARCHAR(200) NOT NULL,
        description TEXT,
        homework TEXT,
        date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        date DATE NOT NULL,
        status ENUM('present', 'absent', 'late') NOT NULL DEFAULT 'present',
        marked_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_attendance (student_id, date)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        deadline DATE NOT NULL,
        teacher_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        assignment_id INT NOT NULL,
        student_id INT NOT NULL,
        submission_text TEXT,
        file_path VARCHAR(500) DEFAULT NULL,
        status ENUM('pending', 'submitted', 'late') NOT NULL DEFAULT 'pending',
        submitted_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_submission (assignment_id, student_id)
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message VARCHAR(500) NOT NULL,
        type ENUM('report', 'assignment', 'attendance', 'general') NOT NULL DEFAULT 'general',
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

foreach ($tables as $sql) {
    if ($conn->query($sql)) {
        echo "<p>✅ Table created successfully</p>";
    } else {
        echo "<p>❌ Error: " . $conn->error . "</p>";
    }
}

// Hash password
$hashed = password_hash('class@121', PASSWORD_DEFAULT);

// Check if default users exist
$check = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE email = 'Akash@admin.com'");
$exists = $check->fetch_assoc()['cnt'] > 0;

if (!$exists) {
    // Insert default users
    $conn->query("INSERT INTO users (name, email, password, role, registration_number) VALUES ('Admin', 'Akash@admin.com', '$hashed', 'admin', NULL)");
    echo "<p>✅ Admin user created (Akash@admin.com / class@121)</p>";

    $conn->query("INSERT INTO users (name, email, password, role, registration_number) VALUES ('Shivam', 'Shivam@teacher.com', '$hashed', 'teacher', '123456')");
    echo "<p>✅ Teacher user created (Shivam@teacher.com / 123456 / class@121)</p>";

    $conn->query("INSERT INTO users (name, email, password, role, registration_number) VALUES ('Akku', 'Akku@student.com', '$hashed', 'student', '12345678')");
    echo "<p>✅ Student user created (Akku@student.com / 12345678 / class@121)</p>";

    // Sample reports
    $conn->query("INSERT INTO reports (teacher_id, subject, topic, description, homework, date) VALUES
        (2, 'Database Management', 'Introduction to SQL', 'Covered basic SQL commands including SELECT, INSERT, UPDATE, and DELETE operations.', 'Practice 10 SQL queries from textbook Chapter 3', '2026-04-01'),
        (2, 'Web Technology', 'HTML Forms & Validation', 'Discussed form elements, input types, and client-side validation using JavaScript.', 'Create a registration form with validation', '2026-04-02'),
        (2, 'Data Structures', 'Linked Lists', 'Covered singly linked list operations: insertion, deletion, and traversal.', 'Implement doubly linked list in C', '2026-04-03'),
        (2, 'Database Management', 'Normalization', 'Explained 1NF, 2NF, 3NF, and BCNF with examples.', 'Normalize the given table to 3NF', '2026-04-04')
    ");
    echo "<p>✅ Sample reports added</p>";

    // Sample assignments
    $conn->query("INSERT INTO assignments (title, description, deadline, teacher_id) VALUES
        ('SQL Practice Set', 'Complete 20 SQL queries covering joins, subqueries, and aggregate functions.', '2026-04-15', 2),
        ('Web Portfolio Project', 'Build a personal portfolio website using HTML, CSS, and JavaScript.', '2026-04-20', 2)
    ");
    echo "<p>✅ Sample assignments added</p>";

    // Sample attendance
    $conn->query("INSERT INTO attendance (student_id, date, status, marked_by) VALUES
        (3, '2026-04-01', 'present', 2),
        (3, '2026-04-02', 'present', 2),
        (3, '2026-04-03', 'absent', 2),
        (3, '2026-04-04', 'present', 2)
    ");
    echo "<p>✅ Sample attendance added</p>";

    // Sample notifications
    $conn->query("INSERT INTO notifications (user_id, message, type) VALUES
        (3, 'New report uploaded: Introduction to SQL', 'report'),
        (3, 'Assignment deadline approaching: SQL Practice Set', 'assignment')
    ");
    echo "<p>✅ Sample notifications added</p>";
} else {
    echo "<p>ℹ️ Default users already exist. Skipping seed data.</p>";
}

echo "<h3>✅ Setup Complete!</h3>";
echo "<p><a href='/ClassSync/'>Go to Homepage →</a></p>";
echo "<hr>";
echo "<h4>Default Login Credentials:</h4>";
echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>Role</th><th>Login ID</th><th>Password</th></tr>";
echo "<tr><td>Admin</td><td>Akash@admin.com</td><td>class@121</td></tr>";
echo "<tr><td>Teacher</td><td>123456 or Shivam@teacher.com</td><td>class@121</td></tr>";
echo "<tr><td>Student</td><td>12345678 or Akku@student.com</td><td>class@121</td></tr>";
echo "</table>";

$conn->close();
?>
