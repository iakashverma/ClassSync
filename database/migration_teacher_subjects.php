<?php
// Migration: Teacher Course & Multi-Subject Selection
// Run once: http://localhost/ClassSync/database/migration_teacher_subjects.php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'classsync';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Migration: Teacher Course & Subject Selection</h2>";

// ============================================
// 1. Create course_subjects table (maps subjects to courses)
// ============================================
$conn->query("
    CREATE TABLE IF NOT EXISTS course_subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        subject_name VARCHAR(100) NOT NULL,
        FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
        UNIQUE KEY unique_course_subject (course_id, subject_name)
    ) ENGINE=InnoDB
");
echo "<p>✅ Table 'course_subjects' created</p>";

// ============================================
// 2. Create teacher_subjects junction table (teacher <-> course_subject)
// ============================================
$conn->query("
    CREATE TABLE IF NOT EXISTS teacher_subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT NOT NULL,
        course_subject_id INT NOT NULL,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (course_subject_id) REFERENCES course_subjects(id) ON DELETE CASCADE,
        UNIQUE KEY unique_teacher_subject (teacher_id, course_subject_id)
    ) ENGINE=InnoDB
");
echo "<p>✅ Table 'teacher_subjects' created</p>";

// ============================================
// 3. Add course_id to users table for teachers (if not exists)
// ============================================
$col_check = $conn->query("SHOW COLUMNS FROM users LIKE 'teacher_course_id'");
if ($col_check->num_rows === 0) {
    $conn->query("ALTER TABLE users ADD COLUMN teacher_course_id INT NULL AFTER department");
    echo "<p>✅ Column 'teacher_course_id' added to users table</p>";
} else {
    echo "<p>ℹ️ Column 'teacher_course_id' already exists</p>";
}

// ============================================
// 4. Ensure required courses exist (BCA, MCA, BTech, MTech)
// ============================================
$required_courses = ['BCA', 'MCA', 'BTech', 'MTech'];
foreach ($required_courses as $cname) {
    $check = $conn->prepare("SELECT course_id FROM courses WHERE course_name = ?");
    $check->bind_param("s", $cname);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows === 0) {
        $ins = $conn->prepare("INSERT INTO courses (course_name) VALUES (?)");
        $ins->bind_param("s", $cname);
        $ins->execute();
        echo "<p>✅ Course '$cname' added</p>";
        $ins->close();
    } else {
        echo "<p>ℹ️ Course '$cname' already exists</p>";
    }
    $check->close();
}

// ============================================
// 5. Populate course_subjects mappings
// ============================================
$course_subjects = [
    'BCA' => [
        'Programming', 'Python', 'Java', 'Data Structures', 'DBMS',
        'Operating Systems', 'Computer Networks', 'Web Development',
        'Software Engineering', 'Mathematics'
    ],
    'MCA' => [
        'Data Structures (Advanced)', 'DBMS', 'Operating Systems',
        'Computer Networks', 'Software Engineering', 'Artificial Intelligence',
        'Machine Learning', 'Cloud Computing', 'Mobile App Development',
        'Cyber Security', 'Data Science'
    ],
    'BTech' => [
        'Programming', 'Data Structures', 'DBMS', 'Operating Systems',
        'Computer Networks', 'Software Engineering', 'Mathematics (Advanced)',
        'Artificial Intelligence', 'Machine Learning', 'Cyber Security'
    ],
    'MTech' => [
        'Artificial Intelligence', 'Machine Learning', 'Data Science',
        'Cloud Computing', 'Cyber Security', 'Advanced Data Structures',
        'Advanced Software Engineering'
    ]
];

$inserted_count = 0;
foreach ($course_subjects as $course_name => $subjects) {
    // Get course_id
    $stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_name = ?");
    $stmt->bind_param("s", $course_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();

    if (!$course) {
        echo "<p>❌ Course '$course_name' not found!</p>";
        continue;
    }

    $course_id = $course['course_id'];

    foreach ($subjects as $subject_name) {
        // Insert if not exists
        $check = $conn->prepare("SELECT id FROM course_subjects WHERE course_id = ? AND subject_name = ?");
        $check->bind_param("is", $course_id, $subject_name);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if (!$exists) {
            $ins = $conn->prepare("INSERT INTO course_subjects (course_id, subject_name) VALUES (?, ?)");
            $ins->bind_param("is", $course_id, $subject_name);
            $ins->execute();
            $ins->close();
            $inserted_count++;
        }
    }
}
echo "<p>✅ Inserted $inserted_count course-subject mappings</p>";

echo "<h3>✅ Migration Complete!</h3>";
echo "<p><a href='/ClassSync/register.php'>Go to Registration →</a></p>";

$conn->close();
?>
