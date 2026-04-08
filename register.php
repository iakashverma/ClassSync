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
            <form action="/ClassSync/actions/auth_action.php" method="POST" id="teacher-form" onsubmit="return validateTeacherRegistration()">
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
                    <label for="teacher-course">Course</label>
                    <select id="teacher-course" name="teacher_course_id" required onchange="loadTeacherSubjects()">
                        <option value="">Select Course</option>
                        <?php foreach($courses as $course): ?>
                            <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="teacher-subjects-group" style="display:none;">
                    <label>Select Subjects <span class="input-hint" style="display:inline;">(choose one or more)</span></label>
                    <div class="multi-select-dropdown" id="subject-dropdown">
                        <div class="multi-select-trigger" id="subject-trigger" onclick="toggleSubjectDropdown()">
                            <span class="multi-select-placeholder" id="subject-placeholder">Select Subjects...</span>
                            <div class="selected-tags" id="selected-tags"></div>
                            <span class="multi-select-arrow">▾</span>
                        </div>
                        <div class="multi-select-options" id="subject-options">
                            <div id="subjects-loading" class="subjects-loading" style="display:none;">
                                <div class="spinner"></div>
                                <span>Loading subjects...</span>
                            </div>
                            <div id="subjects-empty" class="subjects-empty" style="display:none;">
                                No subjects found for this course.
                            </div>
                            <div id="teacher-subjects-container">
                                <!-- Dynamic options loaded via AJAX -->
                            </div>
                        </div>
                    </div>
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
                    <select id="student-course" name="course_id" required onchange="updateYears()">
                        <option value="">Select Course</option>
                        <?php foreach($courses as $course): ?>
                            <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="student-year-group" style="display:none;">
                    <label for="student-year">Year</label>
                    <select id="student-year" name="year" required onchange="filterSections()">
                        <option value="">Select Year</option>
                        <!-- Populated dynamically -->
                    </select>
                </div>

                <div class="form-group" id="student-section-group" style="display:none;">
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
        // ========================================
        // Student: Dynamic Year & Section Loading
        // ========================================
        function updateYears() {
            var courseId = document.getElementById('student-course').value;
            var yearSelect = document.getElementById('student-year');
            var sectionSelect = document.getElementById('student-section');
            var sectionOptions = sectionSelect.getElementsByTagName('option');
            var yearGroup = document.getElementById('student-year-group');
            var sectionGroup = document.getElementById('student-section-group');

            // Reset UI
            yearSelect.innerHTML = '<option value="">Select Year</option>';
            yearSelect.value = '';
            sectionSelect.value = '';
            
            if (!courseId) {
                yearGroup.style.display = 'none';
                sectionGroup.style.display = 'none';
                return;
            }

            yearGroup.style.display = 'block';
            sectionGroup.style.display = 'none';

            // Discover unique years associated with this course from the sections data
            var uniqueYears = new Set();
            for(var i = 1; i < sectionOptions.length; i++) {
                if(sectionOptions[i].getAttribute('data-course') == courseId) {
                    uniqueYears.add(sectionOptions[i].getAttribute('data-year'));
                }
            }

            // Populate Year dropdown
            var sortedYears = Array.from(uniqueYears).sort(); // Sorts naturally for "1st", "2nd", "3rd", "4th"
            sortedYears.forEach(function(yearStr) {
                var opt = document.createElement('option');
                opt.value = yearStr;
                opt.textContent = yearStr + " Year";
                yearSelect.appendChild(opt);
            });
        }

        function filterSections() {
            var courseId = document.getElementById('student-course').value;
            var year = document.getElementById('student-year').value;
            var sectionSelect = document.getElementById('student-section');
            var options = sectionSelect.getElementsByTagName('option');
            var sectionGroup = document.getElementById('student-section-group');
            
            sectionSelect.value = "";
            
            if (!year) {
                sectionGroup.style.display = 'none';
                return;
            }
            
            sectionGroup.style.display = 'block';

            var hasSections = false;
            for(var i = 1; i < options.length; i++) {
                var optCourse = options[i].getAttribute('data-course');
                var optYear = options[i].getAttribute('data-year');
                if (optCourse == courseId && optYear == year) {
                    options[i].style.display = '';
                    hasSections = true;
                } else {
                    options[i].style.display = 'none';
                }
            }
        }

        // ========================================
        // Teacher: Dynamic Subject Loading
        // ========================================
        function loadTeacherSubjects() {
            var courseId = document.getElementById('teacher-course').value;
            var container = document.getElementById('teacher-subjects-container');
            var group = document.getElementById('teacher-subjects-group');
            var loading = document.getElementById('subjects-loading');
            var empty = document.getElementById('subjects-empty');
            var dropdown = document.getElementById('subject-dropdown');

            // Reset
            container.innerHTML = '';
            empty.style.display = 'none';
            updateSelectedTags();

            // Close dropdown
            dropdown.classList.remove('open');

            if (!courseId) {
                group.style.display = 'none';
                return;
            }

            group.style.display = 'block';
            loading.style.display = 'flex';

            fetch('/ClassSync/actions/get_course_subjects.php?course_id=' + courseId)
                .then(function(response) { return response.json(); })
                .then(function(subjects) {
                    loading.style.display = 'none';

                    if (subjects.length === 0) {
                        empty.style.display = 'block';
                        return;
                    }

                    subjects.forEach(function(subject, index) {
                        var option = document.createElement('label');
                        option.className = 'dropdown-option';
                        option.style.animationDelay = (index * 0.03) + 's';

                        var checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.name = 'teacher_subjects[]';
                        checkbox.value = subject.id;
                        checkbox.id = 'subj-' + subject.id;
                        checkbox.setAttribute('data-name', subject.subject_name);
                        checkbox.addEventListener('change', function() {
                            updateSelectedTags();
                        });

                        var checkmark = document.createElement('span');
                        checkmark.className = 'option-checkmark';

                        var text = document.createElement('span');
                        text.className = 'option-label';
                        text.textContent = subject.subject_name;

                        option.appendChild(checkbox);
                        option.appendChild(checkmark);
                        option.appendChild(text);
                        container.appendChild(option);
                    });
                })
                .catch(function(err) {
                    loading.style.display = 'none';
                    empty.textContent = 'Error loading subjects. Please try again.';
                    empty.style.display = 'block';
                });
        }

        // ========================================
        // Dropdown Toggle & Tag Management
        // ========================================
        function toggleSubjectDropdown() {
            var dropdown = document.getElementById('subject-dropdown');
            dropdown.classList.toggle('open');
        }

        function updateSelectedTags() {
            var tagsContainer = document.getElementById('selected-tags');
            var placeholder = document.getElementById('subject-placeholder');
            var checked = document.querySelectorAll('#teacher-subjects-container input[type="checkbox"]:checked');

            tagsContainer.innerHTML = '';

            if (checked.length === 0) {
                placeholder.style.display = 'inline';
                return;
            }

            placeholder.style.display = 'none';

            checked.forEach(function(cb) {
                var tag = document.createElement('span');
                tag.className = 'selected-tag';
                tag.innerHTML = cb.getAttribute('data-name') + '<button type="button" class="tag-remove" data-id="' + cb.id + '">×</button>';
                tagsContainer.appendChild(tag);
            });

            // Attach remove handlers
            tagsContainer.querySelectorAll('.tag-remove').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var cbId = this.getAttribute('data-id');
                    document.getElementById(cbId).checked = false;
                    updateSelectedTags();
                });
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            var dropdown = document.getElementById('subject-dropdown');
            if (dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });

        // ========================================
        // Teacher Registration Validation
        // ========================================
        function validateTeacherRegistration() {
            var regNo = document.getElementById('teacher-reg-no').value;
            if (regNo.length !== 6 || !/^\d{6}$/.test(regNo)) {
                alert('Registration Number must be exactly 6 digits.');
                return false;
            }

            var courseId = document.getElementById('teacher-course').value;
            if (!courseId) {
                alert('Please select a course.');
                return false;
            }

            var checked = document.querySelectorAll('input[name="teacher_subjects[]"]:checked');
            if (checked.length === 0) {
                alert('Please select at least one subject.');
                return false;
            }

            return true;
        }
    </script>

<?php include 'includes/footer.php'; ?>
