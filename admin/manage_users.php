<?php
// admin/manage_users.php - Add and delete users
require_once '../config/database.php';
require_once '../includes/auth.php';
requireRole('admin');

$pageTitle = "Manage Users";
$error = "";
$success = "";

// handle delete user
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    // don't let admin delete themselves
    if ($deleteId == getUserId()) {
        $error = "You cannot delete your own account!";
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $deleteId);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "User deleted successfully.";
        } else {
            $error = "Failed to delete user.";
        }
        mysqli_stmt_close($stmt);
    }
}

// handle add user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // check if email exists
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        $checkResult = mysqli_stmt_get_result($check);
        
        if (mysqli_num_rows($checkResult) > 0) {
            $error = "Email already exists.";
        } else {
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashedPass, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "User added successfully!";
            } else {
                $error = "Failed to add user.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check);
    }
}

// fetch all users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY role, name");

require_once '../includes/header.php';
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>

<!-- Add User Form -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fas fa-user-plus"></i> Add New User</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Full name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email address" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" class="form-control" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-2">
                <i class="fas fa-plus"></i> Add User
            </button>
        </form>
    </div>
</div>

<!-- Users List -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-users"></i> All Users</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>UID / Reg No</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; while ($user = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php
                            if ($user['role'] == 'admin') echo '<span class="badge badge-purple">Admin</span>';
                            elseif ($user['role'] == 'teacher') echo '<span class="badge badge-info">Teacher</span>';
                            else echo '<span class="badge badge-success">Student</span>';
                            ?>
                        </td>
                        <td>
                            <?php 
                            if (!empty($user['uid'])) echo $user['uid'];
                            elseif (!empty($user['reg_no'])) echo $user['reg_no'];
                            else echo '-';
                            ?>
                        </td>
                        <td>
                            <?php if ($user['id'] != getUserId()): ?>
                                <a href="manage_users.php?delete=<?php echo $user['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            <?php else: ?>
                                <span class="text-muted text-sm">Current User</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
