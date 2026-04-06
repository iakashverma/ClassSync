<?php
/**
 * ClassSync - Admin: Manage Users
 */
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../../includes/header.php';
requireRole('admin');

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $regNumber = trim($_POST['reg_number'] ?? '');

        if (!empty($name) && !empty($email) && !empty($password) && in_array($role, ['teacher', 'student', 'admin'])) {
            // Check duplicate email
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if (!$check->fetch()) {
                $hashedPass = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, reg_number) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPass, $role, $regNumber ?: null]);
                setFlash('success', 'User "' . $name . '" added successfully!');
            } else {
                setFlash('error', 'Email already exists.');
            }
        } else {
            setFlash('error', 'Please fill in all required fields.');
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId > 0 && $userId != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ?");
            $stmt->execute([$userId, $_SESSION['user_id']]);
            setFlash('success', 'User deleted successfully.');
        } else {
            setFlash('error', 'Cannot delete this user.');
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Get all users with optional filter
$roleFilter = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($roleFilter && in_array($roleFilter, ['admin', 'teacher', 'student'])) {
    $query .= " AND role = ?";
    $params[] = $roleFilter;
}
if ($search) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1>👥 Manage Users</h1>
            <p>Add, view, and remove system users</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addUserModal')">
            ➕ Add New User
        </button>
    </div>
</div>

<!-- Filters -->
<div class="filter-bar">
    <div class="search-input">
        <form method="GET" style="display:flex;gap:8px;width:100%">
            <input type="text" name="search" class="form-control" placeholder="Search users..."
                   value="<?php echo e($search); ?>" style="padding-left:40px">
            <input type="hidden" name="role" value="<?php echo e($roleFilter); ?>">
        </form>
    </div>
    <a href="?role=" class="btn btn-sm <?php echo !$roleFilter ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
    <a href="?role=admin" class="btn btn-sm <?php echo $roleFilter === 'admin' ? 'btn-primary' : 'btn-secondary'; ?>">Admins</a>
    <a href="?role=teacher" class="btn btn-sm <?php echo $roleFilter === 'teacher' ? 'btn-primary' : 'btn-secondary'; ?>">Teachers</a>
    <a href="?role=student" class="btn btn-sm <?php echo $roleFilter === 'student' ? 'btn-primary' : 'btn-secondary'; ?>">Students</a>
</div>

<!-- Users Table -->
<div class="card">
    <?php if (empty($users)): ?>
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <h3>No users found</h3>
            <p>No users match your current filters.</p>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>ID/UID</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><strong><?php echo e($u['name']); ?></strong></td>
                        <td style="color:var(--text-secondary)"><?php echo e($u['email']); ?></td>
                        <td><span class="badge badge-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                        <td><?php echo $u['reg_number'] ? e($u['reg_number']) : '—'; ?></td>
                        <td><?php echo timeAgo($u['created_at']); ?></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" id="deleteUser<?php echo $u['id']; ?>" style="display:inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="confirmDelete('Delete user <?php echo e($u['name']); ?>?', 'deleteUser<?php echo $u['id']; ?>')">
                                    🗑️
                                </button>
                            </form>
                            <?php else: ?>
                            <span style="color:var(--text-muted);font-size:0.8rem">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal">
        <div class="modal-header">
            <h2>➕ Add New User</h2>
            <button class="modal-close" onclick="closeModal('addUserModal')">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Create password" required minlength="6">
            </div>

            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                    <option value="">Select role...</option>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Registration No / UID (optional)</label>
                <input type="text" name="reg_number" class="form-control" placeholder="8-digit (student) or 6-digit (teacher)">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
