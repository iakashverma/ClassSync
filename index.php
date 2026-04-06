<?php
$page_title = 'Home';
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Digitize Your Classroom Experience</h1>
        <p>Track daily class work, attendance, and assignments in one place. A simple and organized platform for
            teachers and students.</p>
        <div class="hero-buttons">
            <a href="/ClassSync/register.php" class="btn btn-primary">Get Started</a>
            <a href="/ClassSync/login.php" class="btn btn-outline">Login</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features" id="features">
    <div class="container">
        <h2 class="section-title">Features</h2>
        <p class="section-subtitle">Everything you need to manage your classroom digitally</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon">📚</div>
                <h3>Daily Class Reports</h3>
                <p>Teachers can add daily reports with subject, topic, description, and homework. Students can view and
                    filter easily.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📊</div>
                <h3>Attendance Tracking</h3>
                <p>Mark and monitor student attendance with percentage tracking and low attendance alerts.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📝</div>
                <h3>Assignment Management</h3>
                <p>Upload assignments, track submissions, and manage deadlines all in one place.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📅</div>
                <h3>Academic Timeline</h3>
                <p>View your complete learning history organized by date and subject for easy revision.</p>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about" id="about">
    <div class="container">
        <h2 class="section-title">About Class Sync</h2>
        <div class="about-content">
            <p>ClassSync is a modern, web-based classroom management system built to replace traditional paper registers
                with a smart, organized, and accessible digital solution. It simplifies how academic records are
                maintained and accessed in educational environments.

                With ClassSync, teachers can efficiently record daily classroom activities, students can monitor their
                learning progress, and administrators can manage operations seamlessly—all in one unified platform.

                This project was developed as part of an MCA program, showcasing full-stack web development using PHP,
                MySQL, and a clean, user-focused interface.

                Crafted by ❤️ Akash Verma</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>