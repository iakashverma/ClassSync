<?php
$page_title = 'Register';
$extra_css = ['auth.css'];
include 'includes/auth_check.php';
redirectIfLoggedIn();

require_once 'config/database.php';

// Fetch Courses
$courses_result = $conn->query("SELECT * FROM courses");
$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

// Fetch Sections
$sections_result = $conn->query("SELECT * FROM sections");
$sections = [];
while ($row = $sections_result->fetch_assoc()) {
    $sections[] = $row;
}

// Fetch Subjects
$subjects_result = $conn->query("SELECT * FROM subjects");
$subjects = [];
while ($row = $subjects_result->fetch_assoc()) {
    $subjects[] = $row;
}

include 'includes/header.php';
?>

    <div class="auth-container">
        <div class="auth-box">
            <h2>Create Account</h2>
            <p class="subtitle">Select your role and fill in the details</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php
                    $errors = [
                        'exists' => 'An account with this email or registration number already exists.',
                        'password' => 'Password must be at least 6 characters.',
                        'reg_teacher' => 'Teacher Registration Number must be exactly 6 digits.',
                        'reg_student' => 'Student Registration Number must be exactly 8 digits.',
                        'failed' => 'Registration failed. Please try again.'
                    ];
                    echo $errors[$_GET['error']] ?? 'An error occurred.';
                    ?>
                </div>
            <?php endif; ?>

            <!-- Role Selector -->
            <div class="role-selector">
                <div class="role-tab active" id="tab-teacher" onclick="switchRole('teacher')">
                    <span class="role-icon">👨‍🏫</span>
                    Teacher
                </div>
                <div class="role-tab" id="tab-student" onclick="switchRole('student')">
                    <span class="role-icon">👨‍🎓</span>
                    Student
                </div>
            </div>

            <!-- Teacher Registration Form -->
            <form action="/ClassSync/actions/auth_action.php" method="POST" id="teacher-form" onsubmit="return validateRegistration('teacher')">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="role" value="teacher" id="role-teacher">

                <div class="form-group">
                    <label for="teacher-name">Full Name</label>
                    <input type="text" id="teacher-name" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="teacher-reg-no">Registration Number (6-digit)</label>
                    <input type="text" id="teacher-reg-no" name="registration_number" placeholder="e.g. 123456" maxlength="6" pattern="[0-9]{6}" required>
                    <span class="input-hint">Must be exactly 6 digits</span>
                </div>

                <div class="form-group">
                    <label for="teacher-email">Email</label>
                    <input type="email" id="teacher-email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="teacher-department">Department</label>
                    <input type="text" id="teacher-department" name="department" placeholder="e.g. CS, IT" required>
                </div>

                <div class="form-group">
                    <label for="teacher-subject">Subject (Primary)</label>
                    <select id="teacher-subject" name="subject_id" required>
                        <option value="">Select Primary Subject</option>
                        <?php foreach($subjects as $subject): ?>
                            <option value="<?= $subject['subject_id'] ?>"><?= htmlspecialchars($subject['subject_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="teacher-password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="teacher-password" name="password" placeholder="Create a password" minlength="6" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('teacher-password')">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Register as Teacher</button>
            </form>

            <!-- Student Registration Form -->
            <form action="/ClassSync/actions/auth_action.php" method="POST" id="student-form" style="display:none;" onsubmit="return validateRegistration('student')">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="role" value="student" id="role-student">

                <div class="form-group">
                    <label for="student-name">Full Name</label>
                    <input type="text" id="student-name" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="student-reg-no">Registration Number (8-digit)</label>
                    <input type="text" id="student-reg-no" name="registration_number" placeholder="e.g. 12345678" maxlength="8" pattern="[0-9]{8}" required>
                    <span class="input-hint">Must be exactly 8 digits</span>
                </div>

                <div class="form-group">
                    <label for="student-email">Email</label>
                    <input type="email" id="student-email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label for="student-course">Course</label>
                    <select id="student-course" name="course_id" required onchange="filterSections()">
                        <option value="">Select Course</option>
                        <?php foreach($courses as $course): ?>
                            <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="student-year">Year</label>
                    <select id="student-year" name="year" required onchange="filterSections()">
                        <option value="">Select Year</option>
                        <option value="1st">1st Year</option>
                        <option value="2nd">2nd Year</option>
                        <option value="3rd">3rd Year</option>
                        <option value="4th">4th Year</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="student-section">Section</label>
                    <select id="student-section" name="section_id" required>
                        <option value="">Select Section</option>
                        <?php foreach($sections as $section): ?>
                            <option value="<?= $section['section_id'] ?>" data-course="<?= $section['course_id'] ?>" data-year="<?= $section['year'] ?>"><?= htmlspecialchars($section['section_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="student-password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="student-password" name="password" placeholder="Create a password" minlength="6" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('student-password')">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Register as Student</button>
            </form>

            <div class="auth-links">
                <p>Already have an account? <a href="/ClassSync/login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <script>
        function filterSections() {
            var courseId = document.getElementById('student-course').value;
            var year = document.getElementById('student-year').value;
            var sectionSelect = document.getElementById('student-section');
            var options = sectionSelect.getElementsByTagName('option');
            
            sectionSelect.value = ""; // Reset selected

            for(var i = 1; i < options.length; i++) { // Skip the first "Select Section" option
                var optCourse = options[i].getAttribute('data-course');
                var optYear = options[i].getAttribute('data-year');
                
                if (courseId && year) {
                    if (optCourse == courseId && optYear == year) {
                        options[i].style.display = '';
                    } else {
                        options[i].style.display = 'none';
                    }
                } else {
                    options[i].style.display = ''; // Show all if course/year not fully selected
                }
            }
        }
    </script>

<?php include 'includes/footer.php'; ?>
