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
        
        if ($role === 'student') {
            $course_id = $_POST['course_id'] ?? null;
            $year = $_POST['year'] ?? null;
            $section_id = $_POST['section_id'] ?? null;
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, registration_number, course_id, year, section_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssisi", $name, $email, $password, $role, $reg_no, $course_id, $year, $section_id);
        } else {
            $department = $_POST['department'] ?? null;
            $subject_id = $_POST['subject_id'] ?? null;
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, registration_number, department, subject_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $name, $email, $password, $role, $reg_no, $department, $subject_id);
        }

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

// Fetch Courses
$courses_result = $conn->query("SELECT * FROM courses");
$courses = [];
while ($row = $courses_result->fetch_assoc()) { $courses[] = $row; }

// Fetch Sections
$sections_result = $conn->query("SELECT * FROM sections");
$sections = [];
while ($row = $sections_result->fetch_assoc()) { $sections[] = $row; }

// Fetch Subjects
$subjects_result = $conn->query("SELECT * FROM course_subjects");
$subjects = [];
while ($row = $subjects_result->fetch_assoc()) { $subjects[] = $row; }

// Get users
$filter_role = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

$query = "
    SELECT u.*, c.course_name, sec.section_name, sub.subject_name 
    FROM users u 
    LEFT JOIN courses c ON u.course_id = c.course_id 
    LEFT JOIN sections sec ON u.section_id = sec.section_id 
    LEFT JOIN course_subjects sub ON u.subject_id = sub.id 
    WHERE 1=1
";
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
                            <select name="role" id="role-selector" onchange="toggleRoleFields()" required>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Registration Number</label>
                        <input type="text" name="registration_number" placeholder="6 digits for teacher, 8 for student">
                    </div>

                    <!-- Teacher Fields -->
                    <div id="teacher-fields" style="background:#f8fafc;padding:15px;border-radius:8px;margin-bottom:15px;border-left:4px solid #3b82f6;">
                        <h4 style="margin:0 0 10px 0;color:#1e293b;">👨‍🏫 Teacher Academic Details</h4>
                        <div class="form-row">
                            <div class="form-group" style="margin:0;">
                                <label>Department</label>
                                <input type="text" name="department" placeholder="e.g. CS, IT">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label>Subject (Primary)</label>
                                <select name="subject_id">
                                    <option value="">Select Target Subject</option>
                                    <?php foreach($subjects as $subject): ?>
                                        <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['subject_name']) ?> (<?= htmlspecialchars($subject['course_id']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Student Fields -->
                    <div id="student-fields" style="display:none;background:#f0fdf4;padding:15px;border-radius:8px;margin-bottom:15px;border-left:4px solid #22c55e;">
                        <h4 style="margin:0 0 10px 0;color:#166534;">👨‍🎓 Student Academic Details</h4>
                        <div class="form-row">
                            <div class="form-group" style="margin:0;">
                                <label>Course</label>
                                <select id="student-course" name="course_id" onchange="filterSections()">
                                    <option value="">Select Target Course</option>
                                    <?php foreach($courses as $course): ?>
                                        <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label>Year</label>
                                <select id="student-year" name="year" onchange="filterSections()">
                                    <option value="">Select Target Year</option>
                                    <option value="1st">1st Year</option>
                                    <option value="2nd">2nd Year</option>
                                    <option value="3rd">3rd Year</option>
                                    <option value="4th">4th Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top:15px;">
                            <label>Section</label>
                            <select id="student-section" name="section_id">
                                <option value="">Select Target Section</option>
                                <?php foreach($sections as $section): ?>
                                    <option value="<?= $section['section_id'] ?>" data-course="<?= $section['course_id'] ?>" data-year="<?= $section['year'] ?>"><?= htmlspecialchars($section['section_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
                                <th>Roles & Details</th>
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
                                <td>
                                    <span class="badge badge-info"><?php echo ucfirst($u['role']); ?></span>
                                    <?php if ($u['role'] === 'student'): ?>
                                        <div style="font-size:12px;color:#64748b;margin-top:4px;"><?php echo htmlspecialchars($u['course_name'] ?? '-'); ?> • Sec <?php echo htmlspecialchars($u['section_name'] ?? '-'); ?></div>
                                    <?php elseif ($u['role'] === 'teacher'): ?>
                                        <div style="font-size:12px;color:#64748b;margin-top:4px;"><?php echo htmlspecialchars($u['department'] ?? '-'); ?> • <?php echo htmlspecialchars($u['subject_name'] ?? '-'); ?></div>
                                    <?php endif; ?>
                                </td>
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
    <script>
        function toggleRoleFields() {
            var role = document.getElementById('role-selector').value;
            document.getElementById('teacher-fields').style.display = (role === 'teacher') ? 'block' : 'none';
            document.getElementById('student-fields').style.display = (role === 'student') ? 'block' : 'none';
        }
        
        function filterSections() {
            var courseId = document.getElementById('student-course').value;
            var year = document.getElementById('student-year').value;
            var sectionSelect = document.getElementById('student-section');
            var options = sectionSelect.getElementsByTagName('option');
            
            sectionSelect.value = ""; // Reset selected
            for(var i = 1; i < options.length; i++) {
                var optCourse = options[i].getAttribute('data-course');
                var optYear = options[i].getAttribute('data-year');
                
                if (courseId && year) {
                    if (optCourse == courseId && optYear == year) {
                        options[i].style.display = '';
                    } else {
                        options[i].style.display = 'none';
                    }
                } else {
                    options[i].style.display = '';
                }
            }
        }
    </script>
</body>
</html>
