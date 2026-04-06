<?php
$page_title = 'Home';
include 'includes/header.php';

// Get live stats from database if available
$stats = ['teachers' => 0, 'students' => 0, 'reports' => 0, 'assignments' => 0];
try {
    require_once 'config/database.php';
    $stats['teachers'] = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='teacher'")->fetch_assoc()['c'];
    $stats['students'] = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='student'")->fetch_assoc()['c'];
    $stats['reports'] = $conn->query("SELECT COUNT(*) as c FROM reports")->fetch_assoc()['c'];
    $stats['assignments'] = $conn->query("SELECT COUNT(*) as c FROM assignments")->fetch_assoc()['c'];
} catch (Exception $e) {}
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        <div class="container">
            <div class="hero-badge">🚀 Smart Classroom Platform</div>
            <h1>Digitize Your<br><span class="hero-highlight">Classroom Experience</span></h1>
            <p>Track daily class work, attendance, and assignments in one place. A simple and organized platform for teachers and students.</p>
            <div class="hero-buttons">
                <a href="/ClassSync/register.php" class="btn btn-primary">
                    <span>Get Started</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="/ClassSync/login.php" class="btn btn-outline">Login</a>
            </div>
        </div>
    </section>

    <!-- Stats Counter Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-row">
                <div class="stat-item fade-up">
                    <div class="stat-icon">👨‍🏫</div>
                    <span class="stat-number"><?php echo $stats['teachers']; ?>+</span>
                    <span class="stat-text">Teachers</span>
                </div>
                <div class="stat-item fade-up">
                    <div class="stat-icon">👨‍🎓</div>
                    <span class="stat-number"><?php echo $stats['students']; ?>+</span>
                    <span class="stat-text">Students</span>
                </div>
                <div class="stat-item fade-up">
                    <div class="stat-icon">📋</div>
                    <span class="stat-number"><?php echo $stats['reports']; ?>+</span>
                    <span class="stat-text">Reports Created</span>
                </div>
                <div class="stat-item fade-up">
                    <div class="stat-icon">📝</div>
                    <span class="stat-number"><?php echo $stats['assignments']; ?>+</span>
                    <span class="stat-text">Assignments</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header fade-up">
                <span class="section-badge">✨ Features</span>
                <h2 class="section-title">Everything You Need</h2>
                <p class="section-subtitle">Powerful tools to manage your classroom digitally</p>
            </div>
            <div class="features-grid">
                <div class="feature-card fade-up">
                    <div class="feature-icon-wrap blue"><div class="icon">📚</div></div>
                    <h3>Daily Class Reports</h3>
                    <p>Teachers add daily reports with subject, topic, description and homework. Students view and filter easily.</p>
                </div>
                <div class="feature-card fade-up">
                    <div class="feature-icon-wrap green"><div class="icon">📊</div></div>
                    <h3>Attendance Tracking</h3>
                    <p>Mark and monitor student attendance with percentage tracking and low attendance alerts below 75%.</p>
                </div>
                <div class="feature-card fade-up">
                    <div class="feature-icon-wrap purple"><div class="icon">📝</div></div>
                    <h3>Assignment Management</h3>
                    <p>Upload assignments in PDF/DOC format, track submissions, and manage deadlines in one place.</p>
                </div>
                <div class="feature-card fade-up">
                    <div class="feature-icon-wrap orange"><div class="icon">📅</div></div>
                    <h3>Academic Timeline</h3>
                    <p>View your complete learning history organized by date and subject for easy revision.</p>
                </div>
                <div class="feature-card fade-up">
                    <div class="feature-icon-wrap red"><div class="icon">⚠️</div></div>
                    <h3>Missed Class Recovery</h3>
                    <p>Students can check missed lectures and access notes & homework to stay on track.</p>
                </div>
                <div class="feature-card fade-up">
                    <div class="feature-icon-wrap yellow"><div class="icon">🔔</div></div>
                    <h3>Notifications</h3>
                    <p>Get notified about new reports, assignments, and upcoming deadlines automatically.</p>
                </div>
                <div class="feature-card fade-up">
                    <div class="feature-icon-wrap teal"><div class="icon">🔍</div></div>
                    <h3>Search & Filter</h3>
                    <p>Quickly find reports by subject, topic, or date using built-in search and filter tools.</p>
                </div>
                <div class="feature-card fade-up">
                    <div class="feature-icon-wrap dark"><div class="icon">🔐</div></div>
                    <h3>Role-Based Access</h3>
                    <p>Separate dashboards for Admin, Teacher, and Student with secure login and access control.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-header fade-up">
                <span class="section-badge">🛠️ Process</span>
                <h2 class="section-title">How It Works</h2>
                <p class="section-subtitle">Get started in 3 simple steps</p>
            </div>
            <div class="steps-grid">
                <div class="step-card fade-up">
                    <div class="step-number">1</div>
                    <div class="step-icon">📝</div>
                    <h3>Register</h3>
                    <p>Create your account by selecting your role — Teacher or Student. Enter your registration number, email, and password.</p>
                </div>
                <div class="step-arrow fade-up">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </div>
                <div class="step-card fade-up">
                    <div class="step-number">2</div>
                    <div class="step-icon">🔑</div>
                    <h3>Login</h3>
                    <p>Use your registration number or email to log in. The system automatically detects your role and redirects you.</p>
                </div>
                <div class="step-arrow fade-up">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </div>
                <div class="step-card fade-up">
                    <div class="step-number">3</div>
                    <div class="step-icon">🎯</div>
                    <h3>Start Using</h3>
                    <p>Teachers can add reports, mark attendance, and create assignments. Students can view and submit work.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- User Roles Section -->
    <section class="user-roles" id="roles">
        <div class="container">
            <div class="section-header fade-up">
                <span class="section-badge">👥 Roles</span>
                <h2 class="section-title">User Roles</h2>
                <p class="section-subtitle">Each role has its own dashboard and set of tools</p>
            </div>
            <div class="roles-grid">
                <div class="role-card role-admin fade-up">
                    <div class="role-header">
                        <span class="role-emoji">👨‍💻</span>
                        <h3>Admin</h3>
                    </div>
                    <ul class="role-features">
                        <li>✅ Full system control</li>
                        <li>✅ Manage all users</li>
                        <li>✅ View all reports & attendance</li>
                        <li>✅ Monitor assignments & submissions</li>
                        <li>✅ Access teacher & student panels</li>
                    </ul>
                </div>
                <div class="role-card role-teacher fade-up">
                    <div class="role-header">
                        <span class="role-emoji">👨‍🏫</span>
                        <h3>Teacher</h3>
                    </div>
                    <ul class="role-features">
                        <li>✅ Add daily class reports</li>
                        <li>✅ Mark student attendance</li>
                        <li>✅ Create & manage assignments</li>
                        <li>✅ View student submissions</li>
                        <li>✅ Download submitted files</li>
                    </ul>
                </div>
                <div class="role-card role-student fade-up">
                    <div class="role-header">
                        <span class="role-emoji">👨‍🎓</span>
                        <h3>Student</h3>
                    </div>
                    <ul class="role-features">
                        <li>✅ View daily class reports</li>
                        <li>✅ Check attendance percentage</li>
                        <li>✅ Submit assignments (PDF/DOC)</li>
                        <li>✅ Track academic timeline</li>
                        <li>✅ Recover missed class notes</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Technology Stack Section -->
    <section class="tech-stack">
        <div class="container">
            <div class="section-header fade-up">
                <span class="section-badge">⚙️ Tech</span>
                <h2 class="section-title">Technology Stack</h2>
                <p class="section-subtitle">Built with industry-standard web technologies</p>
            </div>
            <div class="tech-grid fade-up">
                <div class="tech-item"><div class="tech-icon">🐘</div><span>PHP</span></div>
                <div class="tech-item"><div class="tech-icon">🗄️</div><span>MySQL</span></div>
                <div class="tech-item"><div class="tech-icon">🌐</div><span>HTML5</span></div>
                <div class="tech-item"><div class="tech-icon">🎨</div><span>CSS3</span></div>
                <div class="tech-item"><div class="tech-icon">⚡</div><span>JavaScript</span></div>
                <div class="tech-item"><div class="tech-icon">🖥️</div><span>XAMPP</span></div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <div class="section-header fade-up">
                <span class="section-badge">ℹ️ About</span>
                <h2 class="section-title">About Class Sync</h2>
            </div>
            <div class="about-content fade-up">
                <p>Class Sync is a web-based classroom management system designed to replace traditional physical registers with a modern, organized, and accessible digital platform. It helps teachers record daily classroom activities, allows students to track their learning progress, and enables administrators to manage the entire system efficiently.</p>
                <p class="mt-2">The system has been developed as a part of an MCA program, showcasing full-stack web development with PHP, MySQL, and a clean, user-focused interface. It demonstrates the practical application of database management, authentication systems, and role-based access control in a real-world academic environment.</p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
        </div>
        <div class="container fade-up">
            <h2>Ready to Get Started?</h2>
            <p>Join Class Sync today and experience a smarter way to manage your classroom.</p>
            <div class="hero-buttons">
                <a href="/ClassSync/register.php" class="btn btn-primary">Create Account</a>
                <a href="/ClassSync/login.php" class="btn btn-outline">Login Now</a>
            </div>
        </div>
    </section>

<!-- Scroll Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fadeEls = document.querySelectorAll('.fade-up');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    fadeEls.forEach(el => observer.observe(el));
});
</script>

<?php include 'includes/footer.php'; ?>