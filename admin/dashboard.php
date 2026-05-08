<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/academic.php';
require_login('admin');

$db = get_db();
ensure_predefined_academic_data($db);

function safe_count(PDO $db, string $table): int
{
    try {
        return (int) $db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

$totalUsers = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalTeachers = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
$totalStudents = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();

$fixedCourses = get_predefined_courses($db);
$fixedYears = get_predefined_years($db);
$fixedSections = get_predefined_sections($db);
$fixedSubjects = get_predefined_subjects($db);

$totalCourses = count($fixedCourses);
$totalYears = count($fixedYears);
$totalSections = count($fixedSections);
$totalSubjects = count($fixedSubjects);

$totalTeacherAssignments = safe_count($db, 'teacher_assignments');
$totalStudentAssignments = safe_count($db, 'student_assignments');
$totalClasswork = safe_count($db, 'classwork');
$totalReports = safe_count($db, 'reports');

$setupSteps = 7;
$setupCompleted = 0;
if ($totalCourses === count(academic_courses())) {
    $setupCompleted++;
}
if ($totalYears === count(academic_years())) {
    $setupCompleted++;
}
if ($totalSections === count(academic_sections())) {
    $setupCompleted++;
}
if ($totalSubjects === count(academic_subjects())) {
    $setupCompleted++;
}
if ($totalTeachers > 0) {
    $setupCompleted++;
}
if ($totalStudents > 0) {
    $setupCompleted++;
}
if ($totalTeacherAssignments > 0 || $totalStudentAssignments > 0) {
    $setupCompleted++;
}
$setupPercent = (int) round(($setupCompleted / $setupSteps) * 100);

render_header('Admin Dashboard', 'admin', '/admin/dashboard.php');
?>
<section class="panel admin-hero">
    <div>
        <p class="admin-hero-tag">Control Center</p>
        <h2>Welcome, <?php echo e(current_user()['name'] ?? 'Admin'); ?></h2>
        <p class="admin-hero-text">Monitor the platform, keep user records updated, and use a strict predefined academic
            structure across all mappings.</p>
    </div>
    <div class="admin-hero-meta">
        <div class="admin-hero-meta-label">Today</div>
        <div class="admin-hero-meta-value"><?php echo date('d M Y'); ?></div>
    </div>
</section>

<div class="stats admin-stats">
    <div class="stat-card stat-accent">
        <div class="label">Total Users</div>
        <div class="value"><?php echo $totalUsers; ?></div>
        <div class="sub">Teachers + Students + Admin</div>
    </div>
    <div class="stat-card">7

        <div class="label">Teachers</div>
        <div class="value"><?php echo $totalTeachers; ?></div>
        <div class="sub">Registered faculty members</div>
    </div>
    <div class="stat-card">
        <div class="label">Students</div>
        <div class="value"><?php echo $totalStudents; ?></div>
        <div class="sub">Registered student accounts</div>
    </div>
    <div class="stat-card">
        <div class="label">Subjects</div>
        <div class="value"><?php echo $totalSubjects; ?></div>
        <div class="sub">Configured in academic structure</div>
    </div>
    <div class="stat-card">
        <div class="label">Classwork</div>
        <div class="value"><?php echo $totalClasswork; ?></div>
        <div class="sub">Published by teachers</div>
    </div>
    <div class="stat-card">
        <div class="label">Weekly Reports</div>
        <div class="value"><?php echo $totalReports; ?></div>
        <div class="sub">Generated student reports</div>
    </div>
</div>

<section class="panel">
    <h2>Quick Actions</h2>
    <div class="admin-actions">
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/add_teacher.php">
            <h3>Add Teacher</h3>
            <p>Create teacher account with automatic 4-digit ID.</p>
        </a>
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/add_student.php">
            <h3>Add Student</h3>
            <p>Create student account with automatic 6-digit ID.</p>
        </a>
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/view_records.php">
            <h3>View Records</h3>
            <p>Open the complete roster and assignment mapping.</p>
        </a>
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/courses.php">
            <h3>View Courses</h3>
            <p>Review fixed course options used system-wide.</p>
        </a>
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/years.php">
            <h3>View Years</h3>
            <p>Review fixed years (1st to 4th) for all courses.</p>
        </a>
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/sections.php">
            <h3>View Sections</h3>
            <p>Review fixed section set (A, B, C).</p>
        </a>
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/subjects.php">
            <h3>View Subjects</h3>
            <p>Review predefined subjects and class coverage.</p>
        </a>
        <a class="action-card" href="<?php echo BASE_URL; ?>/admin/reports.php">
            <h3>Reports</h3>
            <p>Review generated weekly performance reports.</p>
        </a>
    </div>
</section>

<section class="admin-columns">
    <article class="panel">
        <h2>Setup Status</h2>
        <div class="setup-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"
            aria-valuenow="<?php echo $setupPercent; ?>">
            <span style="width: <?php echo $setupPercent; ?>%;"></span>
        </div>
        <p class="setup-text"><?php echo $setupPercent; ?>% complete
            (<?php echo $setupCompleted; ?>/<?php echo $setupSteps; ?> integrity checks passed)</p>
        <div class="setup-grid">
            <div class="mini-stat"><span>Courses</span><strong><?php echo $totalCourses; ?></strong></div>
            <div class="mini-stat"><span>Years</span><strong><?php echo $totalYears; ?></strong></div>
            <div class="mini-stat"><span>Sections</span><strong><?php echo $totalSections; ?></strong></div>
            <div class="mini-stat"><span>Subjects</span><strong><?php echo $totalSubjects; ?></strong></div>
            <div class="mini-stat"><span>Teacher
                    Assignments</span><strong><?php echo $totalTeacherAssignments; ?></strong></div>
            <div class="mini-stat"><span>Student
                    Assignments</span><strong><?php echo $totalStudentAssignments; ?></strong></div>
            <div class="mini-stat"><span>Default Admin Password</span><strong>classsync@121</strong></div>
        </div>
    </article>

    <article class="panel">
        <h2>Admin Checklist</h2>
        <ul class="admin-checklist">
            <li class="<?php echo $totalCourses === count(academic_courses()) ? 'done' : ''; ?>">Courses fixed to BCA,
                MCA, BTech, MTech</li>
            <li class="<?php echo $totalYears === count(academic_years()) ? 'done' : ''; ?>">Years fixed to 1st, 2nd,
                3rd, 4th</li>
            <li class="<?php echo $totalSections === count(academic_sections()) ? 'done' : ''; ?>">Sections fixed to A,
                B, C</li>
            <li class="<?php echo $totalSubjects === count(academic_subjects()) ? 'done' : ''; ?>">Subjects fixed to
                6-system list</li>
            <li class="<?php echo $totalTeachers > 0 ? 'done' : ''; ?>">Add teacher accounts</li>
            <li class="<?php echo $totalStudents > 0 ? 'done' : ''; ?>">Add student accounts</li>
            <li class="<?php echo $totalTeacherAssignments > 0 ? 'done' : ''; ?>">Map 5 subjects per class to teachers
            </li>
            <li class="<?php echo $totalStudentAssignments > 0 ? 'done' : ''; ?>">Map students to course-year-section
            </li>
            <li class="<?php echo $totalReports > 0 ? 'done' : ''; ?>">Verify report generation flow</li>
        </ul>
    </article>
</section>
<?php render_footer();
