<?php
/**
 * ClassSync - Registration Page
 * Students: 8-digit Registration Number + Name + Password
 * Teachers: 6-digit UID + Name + Password
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . getRoleDashboard($_SESSION['user_role']));
    exit;
}

$error = '';
$formData = ['name' => '', 'email' => '', 'role' => '', 'reg_number' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = trim($_POST['role'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $regNumber = trim($_POST['reg_number'] ?? '');

    $formData = ['name' => $name, 'email' => $email, 'role' => $role, 'reg_number' => $regNumber];

    // Validation
    if (empty($role) || !in_array($role, ['student', 'teacher'])) {
        $error = 'Please select whether you are a Student or Teacher.';
    } elseif (empty($name)) {
        $error = 'Please enter your name.';
    } elseif (empty($email)) {
        $error = 'Please enter your email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($password) || strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif ($role === 'student' && (empty($regNumber) || !preg_match('/^\d{8}$/', $regNumber))) {
        $error = 'Student Registration Number must be exactly 8 digits.';
    } elseif ($role === 'teacher' && (empty($regNumber) || !preg_match('/^\d{6}$/', $regNumber))) {
        $error = 'Teacher UID must be exactly 6 digits.';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered.';
        } else {
            // Check if reg_number already exists for the same role
            $stmt = $pdo->prepare("SELECT id FROM users WHERE reg_number = ? AND role = ?");
            $stmt->execute([$regNumber, $role]);
            if ($stmt->fetch()) {
                $error = ($role === 'student' ? 'Registration Number' : 'UID') . ' already exists.';
            } else {
                // Insert user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, reg_number) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPassword, $role, $regNumber]);

                header('Location: ' . BASE_URL . '/pages/auth/login.php?registered=1');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassSync | Register</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <script src="<?php echo BASE_URL; ?>/assets/js/app.js" defer></script>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 480px;">
            <div class="auth-header">
                <span class="auth-logo">⚡</span>
                <h1>Join ClassSync</h1>
                <p>Create your account to get started</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-error">
                    <span>❌</span> <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <!-- Role Selection -->
                <label class="form-label">I am a...</label>
                <div class="role-select">
                    <div class="role-option <?php echo $formData['role'] === 'student' ? 'selected' : ''; ?>"
                         onclick="selectRole('student')">
                        <span class="role-icon">🎓</span>
                        <span class="role-name">Student</span>
                    </div>
                    <div class="role-option <?php echo $formData['role'] === 'teacher' ? 'selected' : ''; ?>"
                         onclick="selectRole('teacher')">
                        <span class="role-icon">👨‍🏫</span>
                        <span class="role-name">Teacher</span>
                    </div>
                </div>
                <input type="hidden" name="role" id="roleInput" value="<?php echo e($formData['role']); ?>">

                <!-- Student Fields -->
                <div id="studentFields" style="display: <?php echo $formData['role'] === 'student' ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label class="form-label" for="studentRegNo">8-Digit Registration Number</label>
                        <input type="text" id="studentRegNo" name="reg_number" class="form-control"
                               placeholder="e.g. 20240001" maxlength="8" pattern="\d{8}"
                               value="<?php echo $formData['role'] === 'student' ? e($formData['reg_number']) : ''; ?>">
                        <small class="form-text">Enter your 8-digit student registration number</small>
                    </div>
                </div>

                <!-- Teacher Fields -->
                <div id="teacherFields" style="display: <?php echo $formData['role'] === 'teacher' ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label class="form-label" for="teacherUID">6-Digit Unique ID (UID)</label>
                        <input type="text" id="teacherUID" name="reg_number" class="form-control"
                               placeholder="e.g. 100001" maxlength="6" pattern="\d{6}"
                               value="<?php echo $formData['role'] === 'teacher' ? e($formData['reg_number']) : ''; ?>">
                        <small class="form-text">Enter your 6-digit teacher UID</small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control"
                           placeholder="Enter your full name" value="<?php echo e($formData['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="Enter your email" value="<?php echo e($formData['email']); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Min 6 characters" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirm_password" class="form-control"
                               placeholder="Re-enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 8px;">
                    ✨ Create Account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="<?php echo BASE_URL; ?>/pages/auth/login.php">Sign in here</a>
            </div>
        </div>
    </div>

    <script>
        // Handle registration number field sync
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const role = document.getElementById('roleInput').value;
            if (!role) {
                e.preventDefault();
                alert('Please select whether you are a Student or Teacher.');
                return;
            }

            // Ensure the correct reg_number is sent
            if (role === 'student') {
                const teacherInput = document.getElementById('teacherUID');
                if (teacherInput) teacherInput.removeAttribute('name');
            } else {
                const studentInput = document.getElementById('studentRegNo');
                if (studentInput) studentInput.removeAttribute('name');
            }
        });
    </script>
</body>
</html>
