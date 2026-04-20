<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if (is_logged_in() && current_user()['role'] === 'student') {
    redirect('/student/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = login_user_with_identifier($identifier, $password, 'student');
    if (!$user) {
        $error = 'Invalid student credentials.';
    } else {
        redirect('/student/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - ClassSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css?v=20260418">
</head>
<body class="dashboard student auth-page">
<main class="auth-layout">
    <section class="auth-side auth-student">
        <p class="auth-eyebrow">ClassSync</p>
        <h1>Student Learning Hub</h1>
        <p>Access assignments, attendance, and reports in one clean dashboard built to keep your classwork on track.</p>
        <ul class="auth-points">
            <li>Instant view of academic updates</li>
            <li>Assignment and submission tracking</li>
            <li>Clear class timeline and records</li>
        </ul>
    </section>
    <section class="auth-card panel">
        <h2>Student Login</h2>
        <p class="auth-help">Use your email or 6-digit Student ID with your password.</p>
        <?php if ($error): ?><div class="alert error"><?php echo e($error); ?></div><?php endif; ?>
        <form method="post" class="auth-form">
            <div>
                <label>Email or 6-digit Student ID</label>
                <input type="text" name="identifier" autocomplete="username" required>
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" autocomplete="current-password" required>
            </div>
            <button class="btn student" type="submit">Login</button>
            <a class="btn auth-back" href="<?php echo BASE_URL; ?>/index.php">Back to Home</a>
        </form>
    </section>
</main>
</body>
</html>
