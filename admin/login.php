<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

function sync_default_admin_credentials(): void
{
    $email = 'admin@classsync.com';
    $legacyEmail = 'admin@classsync.local';
    $passwordHash = '$2y$10$ZiiEa.9RS8ZTQ9rbhVtlSO2ZU/Ot0CdRto4XfwiPplRqfFNM1AR.2';

    $db = get_db();

    $update = $db->prepare(
        "UPDATE users
         SET email = ?, password = ?
         WHERE role = 'admin' AND email IN (?, ?)"
    );
    $update->execute([$email, $passwordHash, $email, $legacyEmail]);

    $insert = $db->prepare(
        "INSERT INTO users (name, email, password, role)
         SELECT 'System Admin', ?, ?, 'admin'
         WHERE NOT EXISTS (SELECT 1 FROM users WHERE role = 'admin')"
    );
    $insert->execute([$email, $passwordHash]);
}

try {
    sync_default_admin_credentials();
} catch (Throwable $e) {
    // Keep page available even if database is temporarily unreachable.
}

if (is_logged_in() && current_user()['role'] === 'admin') {
    redirect('/admin/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = login_user($email, $password);
    if (!$user || $user['role'] !== 'admin') {
        $error = 'Invalid admin credentials.';
        if ($user) {
            logout_user();
        }
    } else {
        redirect('/admin/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ClassSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css?v=20260418">
</head>
<body class="dashboard admin auth-page">
<main class="auth-layout">
    <section class="auth-side auth-admin">
        <p class="auth-eyebrow">ClassSync</p>
        <h1>Admin Control Portal</h1>
        <p>Manage users, monitor platform activity, and keep every class operation synchronized from one secure workspace.</p>
        <ul class="auth-points">
            <li>Role-based access and reports</li>
            <li>Attendance and assignment management</li>
            <li>Centralized administration tools</li>
        </ul>
    </section>
    <section class="auth-card panel">
        <h2>Admin Login</h2>
        <p class="auth-help">Sign in with your admin email and password.</p>
        <?php if ($error): ?><div class="alert error"><?php echo e($error); ?></div><?php endif; ?>
        <form method="post" class="auth-form">
            <div>
                <label>Email</label>
                <input type="email" name="email" autocomplete="email" required>
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" autocomplete="current-password" required>
            </div>
            <button class="btn admin" type="submit">Login</button>
            <a class="btn auth-back" href="<?php echo BASE_URL; ?>/index.php">Back to Home</a>
        </form>
        <p class="auth-note">Default admin: admin@classsync.com / classsync@121</p>
    </section>
</main>
</body>
</html>
