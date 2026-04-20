<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if (is_logged_in() && current_user()['role'] === 'teacher') {
    redirect('/teacher/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = login_user_with_identifier($identifier, $password, 'teacher');
    if (!$user) {
        $error = 'Invalid teacher credentials.';
    } else {
        redirect('/teacher/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login - ClassSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css?v=20260418">
</head>
<body class="dashboard teacher auth-page">
<main class="auth-layout">
    <section class="auth-side auth-teacher">
        <p class="auth-eyebrow">ClassSync</p>
        <h1>Teacher Workspace</h1>
        <p>Track attendance, post assignments, and review student progress with tools designed for everyday classroom flow.</p>
        <ul class="auth-points">
            <li>Simple course and section control</li>
            <li>Fast attendance and grading updates</li>
            <li>Centralized communication with students</li>
        </ul>
    </section>
    <section class="auth-card panel">
        <h2>Teacher Login</h2>
        <p class="auth-help">Use your email or 4-digit Teacher ID with your password.</p>
        <?php if ($error): ?><div class="alert error"><?php echo e($error); ?></div><?php endif; ?>
        <form method="post" class="auth-form">
            <div>
                <label>Email or 4-digit Teacher ID</label>
                <input type="text" name="identifier" autocomplete="username" required>
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" autocomplete="current-password" required>
            </div>
            <button class="btn teacher" type="submit">Login</button>
            <a class="btn auth-back" href="<?php echo BASE_URL; ?>/index.php">Back to Home</a>
        </form>
    </section>
</main>
</body>
</html>
