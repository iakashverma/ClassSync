<?php
$page_title = 'Login';
$extra_css = ['auth.css'];
include 'includes/auth_check.php';
redirectIfLoggedIn();
include 'includes/header.php';
?>

    <div class="auth-container">
        <div class="auth-box">
            <h2>Welcome Back</h2>
            <p class="subtitle">Login to access your dashboard</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php
                    $errors = [
                        'invalid' => 'Invalid credentials. Please try again.',
                        'not_found' => 'Account not found. Please register first.',
                        'unauthorized' => 'You are not authorized to access that page.',
                        'session' => 'Session expired. Please login again.'
                    ];
                    echo $errors[$_GET['error']] ?? 'An error occurred.';
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    $messages = [
                        'registered' => 'Registration successful! Please login.',
                        'logout' => 'You have been logged out.'
                    ];
                    echo $messages[$_GET['success']] ?? 'Success!';
                    ?>
                </div>
            <?php endif; ?>

            <form action="/ClassSync/actions/auth_action.php" method="POST" id="login-form">
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label for="login-id">Registration Number / Email</label>
                    <input type="text" id="login-id" name="login_id" placeholder="Enter Reg No. or Email" required>
                    <span class="input-hint">6-digit for Teacher | 8-digit for Student | Email for Admin</span>
                </div>

                <div class="form-group">
                    <label for="login-password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('login-password')">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Login</button>
            </form>

            <div class="auth-links">
                <p>Don't have an account? <a href="/ClassSync/register.php">Register here</a></p>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
