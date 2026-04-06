<?php
$page_title = 'Manage Users';
$extra_css = ['dashboard.css'];
require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkRole('admin');

$active_page = 'users';

// Handle add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_user') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $reg_no = trim($_POST['registration_number']) ?: null;

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, registration_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $password, $role, $reg_no);
        $stmt->execute();
        $stmt->close();
        header("Location: /ClassSync/admin/manage_users.php?success=added");
        exit();
    }

    if ($_POST['action'] === 'delete_user') {
        $uid = intval($_POST['user_id']);
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->close();
        header("Location: /ClassSync/admin/manage_users.php?success=deleted");
        exit();
    }
}

// Get users
$filter_role = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

if ($filter_role && in_array($filter_role, ['teacher', 'student', 'admin'])) {
    $query .= " AND role = ?";
    $params[] = $filter_role;
    $types .= "s";
}
if ($search) {
    $search_param = "%$search%";
    $query .= " AND (name LIKE ? OR email LIKE ? OR registration_number LIKE ?)";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Class Sync</title>
    <link rel="stylesheet" href="/ClassSync/assets/css/style.css">
    <link rel="stylesheet" href="/ClassSync/assets/css/dashboard.css">
    <link rel="stylesheet" href="/ClassSync/assets/css/auth.css">
</head>
<body>
    <div class="dashboard-page">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="top-bar">
                <div>
                    <button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('active')">☰</button>
                    <h1>Manage Users</h1>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">User <?php echo $_GET['success']; ?> successfully!</div>
            <?php endif; ?>

            <!-- Add User Form -->
            <div class="dashboard-form">
                <h3>➕ Add New User</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_user">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" minlength="6" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" required>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Registration Number</label>
                        <input type="text" name="registration_number" placeholder="6 digits for teacher, 8 for student">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-blue">Add User</button>
                    </div>
                </form>
            </div>

            <!-- Filters -->
            <form method="GET" class="filter-bar">
                <input type="text" name="search" placeholder="Search by name, email or reg no..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="role">
                    <option value="">All Roles</option>
                    <option value="teacher" <?php echo $filter_role === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                    <option value="student" <?php echo $filter_role === 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="admin" <?php echo $filter_role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
                <button type="submit" class="btn btn-blue btn-sm">Filter</button>
                <a href="/ClassSync/admin/manage_users.php" class="btn btn-sm" style="background:#e2e8f0;color:#333;">Clear</a>
            </form>

            <!-- Users Table -->
            <div class="card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Reg No.</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($u = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['name']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><span class="badge badge-info"><?php echo ucfirst($u['role']); ?></span></td>
                                <td><?php echo $u['registration_number'] ?? '-'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <?php if ($u['role'] !== 'admin'): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Delete this user?')">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="btn btn-red btn-sm">Delete</button>
                                    </form>
                                    <?php else: ?>
                                    <span style="color:#94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="/ClassSync/assets/js/main.js"></script>
</body>
</html>
