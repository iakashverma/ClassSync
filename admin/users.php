<?php
require_once __DIR__ . '/../includes/layout.php';
require_login('admin');
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        flash_set('err', 'Direct user creation is disabled. Use Add Teacher or Add Student pages.');
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM users WHERE id=? AND role IN ('teacher','student')")->execute([$id]);
            flash_set('ok', 'User deleted.');
        }
    }

    redirect('/admin/users.php');
}

$users = $db->query("SELECT id, name, email, role, created_at FROM users WHERE role IN ('teacher','student') ORDER BY role, name")->fetchAll();
render_header('Users', 'admin', '/admin/users.php');
?>
<section class="panel">
    <h2>User Creation Policy</h2>
    <?php if ($m = flash_get('ok')): ?><div class="alert success"><?php echo e($m); ?></div><?php endif; ?>
    <?php if ($m = flash_get('err')): ?><div class="alert error"><?php echo e($m); ?></div><?php endif; ?>
    <div class="alert info">Use the dedicated onboarding pages so every user is created with predefined academic mapping.</div>
    <div class="admin-actions">
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/add_teacher.php">
            <h3>Add Teacher</h3>
            <p>Create teacher account with auto 4-digit ID and assignment mapping.</p>
        </a>
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/add_student.php">
            <h3>Add Student</h3>
            <p>Create student account with auto 6-digit ID and class assignment.</p>
        </a>
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/view_records.php">
            <h3>View Records</h3>
            <p>Review all teacher and student records in one place.</p>
        </a>
    </div>
</section>
<section class="panel">
    <h2>User List</h2>
    <p class="block-sub">Deletion is available for cleanup. Use dedicated pages for creating users.</p>
    <table><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Action</th></tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo e($u['name']); ?></td><td><?php echo e($u['email']); ?></td><td><?php echo e($u['role']); ?></td><td><?php echo e($u['created_at']); ?></td>
                <td><form method="post" data-confirm="Delete user?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>"><button class="btn">Delete</button></form></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
<?php render_footer();
