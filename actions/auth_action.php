<?php
session_start();
require_once '../config/database.php';

$action = $_POST['action'] ?? '';

// ========================
// LOGIN
// ========================
if ($action === 'login') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_id) || empty($password)) {
        header("Location: /ClassSync/login.php?error=invalid");
        exit();
    }

    // Smart Login Detection
    $is_numeric = ctype_digit($login_id);
    $length = strlen($login_id);

    if ($is_numeric && $length === 6) {
        // 6-digit → Teacher
        $stmt = $conn->prepare("SELECT * FROM users WHERE registration_number = ? AND role = 'teacher'");
        $stmt->bind_param("s", $login_id);
    } elseif ($is_numeric && $length === 8) {
        // 8-digit → Student
        $stmt = $conn->prepare("SELECT * FROM users WHERE registration_number = ? AND role = 'student'");
        $stmt->bind_param("s", $login_id);
    } else {
        // Email login - could be any role
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $login_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['reg_no'] = $user['registration_number'];

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: /ClassSync/admin/");
                    break;
                case 'teacher':
                    header("Location: /ClassSync/teacher/");
                    break;
                case 'student':
                    header("Location: /ClassSync/student/");
                    break;
                default:
                    header("Location: /ClassSync/");
            }
            exit();
        } else {
            header("Location: /ClassSync/login.php?error=invalid");
            exit();
        }
    } else {
        header("Location: /ClassSync/login.php?error=not_found");
        exit();
    }

    $stmt->close();
}

// ========================
// REGISTRATION
// ========================
if ($action === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $reg_no = trim($_POST['registration_number'] ?? '');

    // Validate role
    if (!in_array($role, ['teacher', 'student'])) {
        header("Location: /ClassSync/register.php?error=failed");
        exit();
    }

    // Validate registration number length
    if ($role === 'teacher' && (strlen($reg_no) !== 6 || !ctype_digit($reg_no))) {
        header("Location: /ClassSync/register.php?error=reg_teacher");
        exit();
    }

    if ($role === 'student' && (strlen($reg_no) !== 8 || !ctype_digit($reg_no))) {
        header("Location: /ClassSync/register.php?error=reg_student");
        exit();
    }

    // Validate password
    if (strlen($password) < 6) {
        header("Location: /ClassSync/register.php?error=password");
        exit();
    }

    // Check if email or reg_no already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR registration_number = ?");
    $check->bind_param("ss", $email, $reg_no);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        header("Location: /ClassSync/register.php?error=exists");
        exit();
    }
    $check->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'student') {
        $course_id = $_POST['course_id'] ?? null;
        $year = $_POST['year'] ?? null;
        $section_id = $_POST['section_id'] ?? null;
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, registration_number, course_id, year, section_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssisi", $name, $email, $hashed_password, $role, $reg_no, $course_id, $year, $section_id);
    } else {
        $department = $_POST['department'] ?? null;
        $subject_id = $_POST['subject_id'] ?? null;
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, registration_number, department, subject_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $name, $email, $hashed_password, $role, $reg_no, $department, $subject_id);
    }

    if ($stmt->execute()) {
        header("Location: /ClassSync/login.php?success=registered");
    } else {
        header("Location: /ClassSync/register.php?error=failed");
    }

    $stmt->close();
    exit();
}

// If no valid action
header("Location: /ClassSync/login.php");
exit();
?>
