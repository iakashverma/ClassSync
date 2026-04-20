<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassSync - Daily College Class Work Report System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css?v=20260418d">
    <script defer src="<?php echo BASE_URL; ?>/js/app.js"></script>
</head>
<body class="landing">
    <nav class="top-nav">
        <div class="brand">ClassSync</div>
        <div class="links">
            <a class="btn admin" href="<?php echo BASE_URL; ?>/admin/login.php">Admin Login</a>
            <a class="btn teacher" href="<?php echo BASE_URL; ?>/teacher/login.php">Teacher Login</a>
            <a class="btn student" href="<?php echo BASE_URL; ?>/student/login.php">Student Login</a>
        </div>
    </nav>

    <section class="hero" id="top">
        <div class="hero-copy">
            <p class="hero-kicker">Secure Digital Academic Workflow</p>
            <h1> Daily College Class Work <span> Report Book System</span></h1>
            <p>
                ClassSync unifies assignment delivery, attendance, submissions, and performance reporting
                into one focused platform for administrators, teachers, and students.
            </p>
            <div class="hero-actions">
                <a class="btn primary" href="#">Explore Platform</a>
                <a class="btn ghost" href="#">Join Now</a>
            </div>
            <!-- <div class="hero-metrics">
                <div class="hero-metric">
                    <span class="hero-metric-value">3</span>
                    <span class="hero-metric-label">Role-based portals</span>
                </div>
                <div class="hero-metric">
                    <span class="hero-metric-value">1</span>
                    <span class="hero-metric-label">Unified academic flow</span>
                </div>
                <div class="hero-metric">
                    <span class="hero-metric-value">24x7</span>
                    <span class="hero-metric-label">Always-available access</span>
                </div>
            </div> -->
        </div>
        <aside class="hero-card">
            <h3>Platform Snapshot</h3>
            <p>Designed to reduce manual effort while preserving strong course, year, and section discipline.</p>
            <!-- <div class="hero-insights">
                <article class="hero-insight insight-track">
                    <span class="hero-insight-icon" aria-hidden="true">01</span>
                    <h4>Track</h4>
                    <p>See attendance and submission status clearly for every class group.</p>
                </article>
                <article class="hero-insight insight-coordinate">
                    <span class="hero-insight-icon" aria-hidden="true">02</span>
                    <h4>Coordinate</h4>
                    <p>Give teachers and students dedicated workspaces aligned with their responsibilities.</p>
                </article>
                <article class="hero-insight insight-report">
                    <span class="hero-insight-icon" aria-hidden="true">03</span>
                    <h4>Report</h4>
                    <p>Generate weekly progress insights without reconciling scattered records manually.</p>
                </article>
            </div> -->
        </aside>
    </section>

    <!-- <section class="sections">
        <div class="block landing-block" id="features">
            <div class="section-head">
                <p class="section-label">Capabilities</p>
                <h2>Everything Needed for Daily Academic Delivery</h2>
                <p class="block-sub">Purpose-built modules keep classroom operations consistent, traceable, and easy to manage.</p>
            </div>
            <div class="feature-grid">
                <article class="landing-feature-card">
                    <span class="feature-icon">A</span>
                    <h3>Classwork Publishing</h3>
                    <p>Create structured tasks with descriptions, guidance, and strict deadlines.</p>
                </article>
                <article class="landing-feature-card">
                    <span class="feature-icon">S</span>
                    <h3>Flexible Submission</h3>
                    <p>Accept typed responses or PDF uploads with cleaner tracking of pending work.</p>
                </article>
                <article class="landing-feature-card">
                    <span class="feature-icon">R</span>
                    <h3>Attendance and Review</h3>
                    <p>Mark attendance, review submissions, and provide feedback from one interface.</p>
                </article>
                <article class="landing-feature-card">
                    <span class="feature-icon">I</span>
                    <h3>Insights and Alerts</h3>
                    <p>Surface missed tasks, class trends, and weekly summaries with less manual effort.</p>
                </article>
            </div>
        </div>

        <div class="block landing-block" id="workflow">
            <div class="section-head">
                <p class="section-label">Workflow</p>
                <h2>A Simple End-to-End Academic Cycle</h2>
                <p class="block-sub">Each role sees only the actions they need, making daily execution faster and cleaner.</p>
            </div>
            <ol class="lp-workflow">
                <li>
                    <h3>1. Configure Class Context</h3>
                    <p>Admin maps users to course, year, and section so every task route is predefined.</p>
                </li>
                <li>
                    <h3>2. Publish Classwork</h3>
                    <p>Teacher posts assignments with clear instructions and submission deadlines.</p>
                </li>
                <li>
                    <h3>3. Submit and Validate</h3>
                    <p>Student submits work on time while deadline rules keep records consistent.</p>
                </li>
                <li>
                    <h3>4. Review and Report</h3>
                    <p>Teacher evaluates outcomes and the platform compiles performance visibility for admin.</p>
                </li>
            </ol>
        </div>

        <div class="block landing-block" id="roles">
            <div class="section-head">
                <p class="section-label">Role Coverage</p>
                <h2>Focused Dashboards for Every User Type</h2>
                <p class="block-sub">Clear responsibilities reduce confusion and improve accountability across departments.</p>
            </div>
            <div class="role-grid">
                <article class="role-card role-admin">
                    <h3>Admin</h3>
                    <p class="role-tagline">Control structure, users, and reporting standards.</p>
                    <ul class="role-list">
                        <li>Maintain class mapping and user assignments</li>
                        <li>Monitor participation and submission health</li>
                        <li>Review weekly academic trends</li>
                    </ul>
                </article>
                <article class="role-card role-teacher">
                    <h3>Teacher</h3>
                    <p class="role-tagline">Deliver classwork and evaluate progress efficiently.</p>
                    <ul class="role-list">
                        <li>Publish assignments with strict deadlines</li>
                        <li>Track attendance and submission quality</li>
                        <li>Provide feedback and maintain continuity</li>
                    </ul>
                </article>
                <article class="role-card role-student">
                    <h3>Student</h3>
                    <p class="role-tagline">Stay organized with clear tasks and timelines.</p>
                    <ul class="role-list">
                        <li>View assigned classwork by section</li>
                        <li>Submit text or PDF work on time</li>
                        <li>Track attendance and completion status</li>
                    </ul>
                </article>
            </div>
        </div>

        <div class="block landing-block" id="about">
            <div class="about-panel">
                <div>
                    <p class="section-label">Why ClassSync</p>
                    <h2>Built for Consistency, Transparency, and Speed</h2>
                    <p class="block-sub">ClassSync streamlines routine coordination, reduces manual errors, and helps teams focus on teaching outcomes.</p>
                </div>
                <a class="btn primary" href="<?php echo BASE_URL; ?>/admin/login.php">Open Admin Portal</a>
            </div>
        </div>
    </section> -->

    <footer class="footer landing-footer">
        <p>© 2026 ClassSync | Daily College Class Work Report Book System .<br>
     Crafted with ❤️ by Akash Verma</p>
    </footer>
</body>
</html>
