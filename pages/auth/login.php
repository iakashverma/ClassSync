<?php
/**
 * ClassSync - Login Page
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session for security
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            header('Location: ' . getRoleDashboard($user['role']));
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

if (isset($_GET['error']) && $_GET['error'] === 'unauthorized') {
    $error = 'You do not have permission to access that page.';
}
if (isset($_GET['registered'])) {
    $success = 'Registration successful! Please login.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassSync | Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-logo">⚡</span>
                <h1>ClassSync</h1>
                <p>Smart Classwork & Assignment Tracking</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-error">
                    <span>❌</span> <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="background: var(--success-bg); border: 1px solid rgba(34,197,94,0.3); border-radius: var(--radius-sm); padding: 12px 16px; color: var(--success); font-size: 0.875rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>✅</span> <?php echo e($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="Enter your email" value="<?php echo e($email ?? ''); ?>" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                           placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 8px;">
                    🔐 Sign In
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="<?php echo BASE_URL; ?>/pages/auth/register.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>
