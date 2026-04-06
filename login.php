<?php
// login.php - User login page
require_once 'config/database.php';
require_once 'includes/auth.php';

// if already logged in, go to dashboard
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = "";

// handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // basic validation
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // check if user exists
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // verify password
            if (password_verify($password, $row['password'])) {
                // set session variables
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['role'] = $row['role'];
                
                // redirect to dashboard
                header("Location: index.php");
                exit();
            } else {
                $error = "Wrong password. Please try again.";
            }
        } else {
            $error = "No account found with that email.";
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
    <title>Login | ClassSync</title>
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
                <p>Daily Classwork & Assignment Tracker</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" 
                           placeholder="Enter your email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="auth-links">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>
