<?php
// register.php - Registration for teachers and students
require_once 'config/database.php';
require_once 'includes/auth.php';

// if already logged in, go to dashboard
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

// handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $uid = isset($_POST['uid']) ? trim($_POST['uid']) : null;
    $regNo = isset($_POST['reg_no']) ? trim($_POST['reg_no']) : null;
    
    // basic checks
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif ($role == 'teacher') {
        // teacher needs 6-digit UID
        if (empty($uid)) {
            $error = "UID is required for teachers.";
        } elseif (!is_numeric($uid) || strlen($uid) != 6) {
            $error = "UID must be exactly 6 digits.";
        }
    } elseif ($role == 'student') {
        // student needs 8-digit reg number
        if (empty($regNo)) {
            $error = "Registration number is required for students.";
        } elseif (!is_numeric($regNo) || strlen($regNo) != 8) {
            $error = "Registration number must be exactly 8 digits.";
        }
    } else {
        $error = "Invalid role selected.";
    }
    
    // check if email already exists
    if (empty($error)) {
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $error = "This email is already registered.";
        }
        mysqli_stmt_close($checkStmt);
    }
    
    // if no errors, register the user
    if (empty($error)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        if ($role == 'teacher') {
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, uid) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashedPassword, $role, $uid);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, reg_no) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashedPassword, $role, $regNo);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | ClassSync</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-logo">
                <i class="fas fa-graduation-cap"></i>
                <h1>ClassSync</h1>
                <p>Create your account</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <!-- Role Selection Tabs -->
                <div class="role-tabs">
                    <div class="role-tab active" data-role="teacher" onclick="switchRole('teacher')">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Teacher
                    </div>
                    <div class="role-tab" data-role="student" onclick="switchRole('student')">
                        <i class="fas fa-user-graduate"></i>
                        Student
                    </div>
                </div>
                <input type="hidden" name="role" id="role-input" value="teacher">
                
                <!-- Teacher specific field -->
                <div id="teacher-fields">
                    <div class="form-group">
                        <label for="uid">Teacher UID (6 digits)</label>
                        <input type="text" name="uid" id="uid" class="form-control" 
                               placeholder="e.g., 100002" maxlength="6">
                    </div>
                </div>
                
                <!-- Student specific field -->
                <div id="student-fields" style="display: none;">
                    <div class="form-group">
                        <label for="reg_no">Registration Number (8 digits)</label>
                        <input type="text" name="reg_no" id="reg_no" class="form-control" 
                               placeholder="e.g., 20230002" maxlength="8">
                    </div>
                </div>
                
                <!-- Common fields -->
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" 
                           placeholder="Enter your name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" 
                           placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="Create a password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            
            <div class="auth-links">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
