<?php
// index.php - Public Homepage / Dashboard Router
// Shows public homepage to visitors, redirects logged-in users to their dashboard
require_once 'config/database.php';
require_once 'includes/auth.php';

// If logged in, send to dashboard
if (isLoggedIn()) {
    $role = getUserRole();
    if ($role == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($role == 'teacher') {
        header("Location: teacher/dashboard.php");
    } elseif ($role == 'student') {
        header("Location: student/dashboard.php");
    }
    exit();
}

// --- PUBLIC HOMEPAGE ---

// Fetch public study materials
$materials = mysqli_query($conn, "SELECT sm.*, s.subject_name, u.name as teacher_name 
                                   FROM study_materials sm 
                                   JOIN subjects s ON sm.subject_id = s.id 
                                   JOIN users u ON sm.teacher_id = u.id 
                                   WHERE sm.is_public = 1 
                                   ORDER BY sm.created_at DESC LIMIT 12");

// Fetch public video lectures
$videos = mysqli_query($conn, "SELECT vl.*, s.subject_name, u.name as teacher_name 
                                FROM video_lectures vl 
                                JOIN subjects s ON vl.subject_id = s.id 
                                JOIN users u ON vl.teacher_id = u.id 
                                WHERE vl.is_public = 1 
                                ORDER BY vl.created_at DESC LIMIT 12");

// Fetch subjects for filter tabs
$subjects = mysqli_query($conn, "SELECT * FROM subjects ORDER BY subject_name");
$subjectList = [];
while ($s = mysqli_fetch_assoc($subjects)) {
    $subjectList[] = $s;
}

// Count stats
$totalMaterials = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM study_materials WHERE is_public=1"))['c'];
$totalVideos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM video_lectures WHERE is_public=1"))['c'];
$totalTeachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='teacher'"))['c'];
$totalSubjects = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM subjects"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ClassSync - Your centralized hub for college classwork, assignments, study materials, and video lectures. Access educational content for free.">
    <title>ClassSync — College Classwork & Study Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/homepage.css">
</head>
<body class="homepage-body">

    <!-- ===================== PUBLIC NAVBAR ===================== -->
    <nav class="public-nav" id="publicNav">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <i class="fas fa-graduation-cap"></i>
                <span>ClassSync</span>
            </a>
            <div class="nav-links" id="navLinks">
                <a href="#home" class="nav-link-pub">Home</a>
                <a href="#features" class="nav-link-pub">Features</a>
                <a href="#materials" class="nav-link-pub">Study Materials</a>
                <a href="#videos" class="nav-link-pub">Video Lectures</a>
                <a href="#how-it-works" class="nav-link-pub">How It Works</a>
            </div>
            <div class="nav-actions">
                <a href="login.php" class="btn-nav btn-nav-outline">Login</a>
                <a href="register.php" class="btn-nav btn-nav-solid">Register</a>
            </div>
            <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileNav()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- ===================== HERO SECTION ===================== -->
    <section class="hero" id="home">
        <div class="hero-bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-bolt"></i> Free Educational Resources
            </div>
            <h1 class="hero-title">
                Your College <span class="gradient-text">Study Hub</span><br>
                All in One Place
            </h1>
            <p class="hero-subtitle">
                Access study materials, video lectures, and assignments — organized by subject, 
                uploaded by your teachers. Start learning without signing up.
            </p>
            <div class="hero-buttons">
                <a href="#materials" class="btn-hero btn-hero-primary">
                    <i class="fas fa-book-open"></i> Explore Materials
                </a>
                <a href="#videos" class="btn-hero btn-hero-outline">
                    <i class="fas fa-play-circle"></i> Watch Lectures
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-number"><?php echo $totalMaterials; ?></span>
                    <span class="hero-stat-label">Study Materials</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-number"><?php echo $totalVideos; ?></span>
                    <span class="hero-stat-label">Video Lectures</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-number"><?php echo $totalTeachers; ?></span>
                    <span class="hero-stat-label">Teachers</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="hero-stat-number"><?php echo $totalSubjects; ?></span>
                    <span class="hero-stat-label">Subjects</span>
                </div>
            </div>
        </div>
        <div class="hero-scroll-indicator">
            <span>Scroll to explore</span>
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- ===================== FEATURES SECTION ===================== -->
    <section class="section features-section" id="features">
        <div class="container">
            <div class="section-header scroll-reveal">
                <span class="section-badge"><i class="fas fa-star"></i> Why ClassSync?</span>
                <h2 class="section-title">Everything You Need to <span class="gradient-text">Excel</span></h2>
                <p class="section-subtitle">A complete platform built for modern education — connecting teachers and students seamlessly.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card scroll-reveal" style="--delay: 0.1s">
                    <div class="feature-icon" style="--icon-bg: linear-gradient(135deg, #6366f1, #8b5cf6)">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <h3>Study Materials</h3>
                    <p>Download PDF and DOC study materials uploaded by verified teachers — all free and organized by subject.</p>
                </div>
                <div class="feature-card scroll-reveal" style="--delay: 0.2s">
                    <div class="feature-icon" style="--icon-bg: linear-gradient(135deg, #ec4899, #f43f5e)">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3>Video Lectures</h3>
                    <p>Watch curated video lectures from YouTube and other platforms — no login needed to start learning.</p>
                </div>
                <div class="feature-card scroll-reveal" style="--delay: 0.3s">
                    <div class="feature-icon" style="--icon-bg: linear-gradient(135deg, #0ea5e9, #06b6d4)">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>Assignment Tracking</h3>
                    <p>Teachers create assignments with deadlines; students submit online with real-time countdown timers.</p>
                </div>
                <div class="feature-card scroll-reveal" style="--delay: 0.4s">
                    <div class="feature-icon" style="--icon-bg: linear-gradient(135deg, #10b981, #14b8a6)">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Role-Based Access</h3>
                    <p>Separate dashboards for admins, teachers, and students — each with tailored functionality.</p>
                </div>
                <div class="feature-card scroll-reveal" style="--delay: 0.5s">
                    <div class="feature-icon" style="--icon-bg: linear-gradient(135deg, #f59e0b, #f97316)">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Deadline Management</h3>
                    <p>Live countdown timers on every assignment ensure you never miss a deadline again.</p>
                </div>
                <div class="feature-card scroll-reveal" style="--delay: 0.6s">
                    <div class="feature-icon" style="--icon-bg: linear-gradient(135deg, #8b5cf6, #a855f7)">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Fully Responsive</h3>
                    <p>Access ClassSync from any device — optimized for desktops, tablets, and mobile phones.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== STUDY MATERIALS SECTION ===================== -->
    <section class="section materials-section" id="materials">
        <div class="container">
            <div class="section-header scroll-reveal">
                <span class="section-badge"><i class="fas fa-book"></i> Study Materials</span>
                <h2 class="section-title">Download <span class="gradient-text">Free Resources</span></h2>
                <p class="section-subtitle">PDF and DOC files uploaded by teachers — browse by subject and download instantly.</p>
            </div>

            <!-- Subject Filter Tabs -->
            <div class="filter-tabs scroll-reveal" id="materialFilters">
                <button class="filter-tab active" data-filter="all" onclick="filterContent('materials', 'all', this)">
                    <i class="fas fa-th-large"></i> All Subjects
                </button>
                <?php foreach ($subjectList as $sub): ?>
                <button class="filter-tab" data-filter="<?php echo $sub['id']; ?>" onclick="filterContent('materials', '<?php echo $sub['id']; ?>', this)">
                    <?php echo htmlspecialchars($sub['subject_name']); ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Materials Grid -->
            <div class="content-grid" id="materialsGrid">
                <?php if (mysqli_num_rows($materials) > 0): ?>
                    <?php while ($mat = mysqli_fetch_assoc($materials)): ?>
                    <div class="material-card scroll-reveal" data-subject="<?php echo $mat['subject_id']; ?>">
                        <div class="material-icon <?php echo $mat['file_type'] == 'pdf' ? 'pdf' : 'doc'; ?>">
                            <i class="fas <?php echo $mat['file_type'] == 'pdf' ? 'fa-file-pdf' : 'fa-file-word'; ?>"></i>
                            <span class="file-badge"><?php echo strtoupper($mat['file_type']); ?></span>
                        </div>
                        <div class="material-info">
                            <h4 class="material-title"><?php echo htmlspecialchars($mat['title']); ?></h4>
                            <span class="material-subject">
                                <i class="fas fa-book"></i> <?php echo htmlspecialchars($mat['subject_name']); ?>
                            </span>
                            <?php if (!empty($mat['description'])): ?>
                                <p class="material-desc"><?php echo htmlspecialchars(mb_strimwidth($mat['description'], 0, 100, '...')); ?></p>
                            <?php endif; ?>
                            <div class="material-meta">
                                <span><i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($mat['teacher_name']); ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($mat['created_at'])); ?></span>
                            </div>
                        </div>
                        <a href="uploads/materials/<?php echo rawurlencode($mat['file_path']); ?>" class="material-download" download>
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state-public">
                        <i class="fas fa-folder-open"></i>
                        <h3>No Study Materials Yet</h3>
                        <p>Teachers will soon upload study materials here. Check back later!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===================== VIDEO LECTURES SECTION ===================== -->
    <section class="section videos-section" id="videos">
        <div class="container">
            <div class="section-header scroll-reveal">
                <span class="section-badge"><i class="fas fa-play-circle"></i> Video Lectures</span>
                <h2 class="section-title">Learn From <span class="gradient-text">Expert Teachers</span></h2>
                <p class="section-subtitle">Watch curated video lectures — handpicked and organized by your teachers.</p>
            </div>

            <!-- Subject Filter Tabs -->
            <div class="filter-tabs scroll-reveal" id="videoFilters">
                <button class="filter-tab active" data-filter="all" onclick="filterContent('videos', 'all', this)">
                    <i class="fas fa-th-large"></i> All Subjects
                </button>
                <?php foreach ($subjectList as $sub): ?>
                <button class="filter-tab" data-filter="<?php echo $sub['id']; ?>" onclick="filterContent('videos', '<?php echo $sub['id']; ?>', this)">
                    <?php echo htmlspecialchars($sub['subject_name']); ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Videos Grid -->
            <div class="content-grid videos-grid" id="videosGrid">
                <?php if (mysqli_num_rows($videos) > 0): ?>
                    <?php while ($vid = mysqli_fetch_assoc($videos)): ?>
                    <?php
                        // Extract YouTube thumbnail
                        $thumbnail = $vid['thumbnail_url'];
                        if (empty($thumbnail)) {
                            $thumbnail = getYouTubeThumbnail($vid['video_url']);
                        }
                    ?>
                    <div class="video-card scroll-reveal" data-subject="<?php echo $vid['subject_id']; ?>">
                        <div class="video-thumbnail">
                            <?php if ($thumbnail): ?>
                                <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="<?php echo htmlspecialchars($vid['title']); ?>">
                            <?php else: ?>
                                <div class="video-placeholder">
                                    <i class="fas fa-play-circle"></i>
                                </div>
                            <?php endif; ?>
                            <a href="<?php echo htmlspecialchars($vid['video_url']); ?>" target="_blank" class="video-play-overlay">
                                <i class="fas fa-play"></i>
                            </a>
                        </div>
                        <div class="video-info">
                            <h4 class="video-title"><?php echo htmlspecialchars($vid['title']); ?></h4>
                            <span class="video-subject">
                                <i class="fas fa-book"></i> <?php echo htmlspecialchars($vid['subject_name']); ?>
                            </span>
                            <?php if (!empty($vid['description'])): ?>
                                <p class="video-desc"><?php echo htmlspecialchars(mb_strimwidth($vid['description'], 0, 80, '...')); ?></p>
                            <?php endif; ?>
                            <div class="video-meta">
                                <span><i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($vid['teacher_name']); ?></span>
                                <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($vid['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state-public">
                        <i class="fas fa-video-slash"></i>
                        <h3>No Video Lectures Yet</h3>
                        <p>Video lectures will be added by teachers soon. Stay tuned!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===================== HOW IT WORKS ===================== -->
    <section class="section how-section" id="how-it-works">
        <div class="container">
            <div class="section-header scroll-reveal">
                <span class="section-badge"><i class="fas fa-lightbulb"></i> How It Works</span>
                <h2 class="section-title">Simple Steps to <span class="gradient-text">Get Started</span></h2>
                <p class="section-subtitle">Whether you're a teacher or student, getting started is easy.</p>
            </div>
            <div class="steps-grid">
                <div class="step-card scroll-reveal" style="--delay: 0.1s">
                    <div class="step-number">1</div>
                    <div class="step-icon"><i class="fas fa-eye"></i></div>
                    <h3>Browse Freely</h3>
                    <p>Explore study materials and video lectures on the homepage — no account required.</p>
                </div>
                <div class="step-connector scroll-reveal"><i class="fas fa-arrow-right"></i></div>
                <div class="step-card scroll-reveal" style="--delay: 0.2s">
                    <div class="step-number">2</div>
                    <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                    <h3>Create Account</h3>
                    <p>Register as a Teacher or Student when you need to upload content or submit assignments.</p>
                </div>
                <div class="step-connector scroll-reveal"><i class="fas fa-arrow-right"></i></div>
                <div class="step-card scroll-reveal" style="--delay: 0.3s">
                    <div class="step-number">3</div>
                    <div class="step-icon"><i class="fas fa-rocket"></i></div>
                    <h3>Start Learning</h3>
                    <p>Teachers upload resources, students access assignments — all in one organized platform.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== CTA SECTION ===================== -->
    <section class="section cta-section">
        <div class="container">
            <div class="cta-box scroll-reveal">
                <div class="cta-content">
                    <h2>Ready to Join ClassSync?</h2>
                    <p>Create your account today and get access to assignments, submissions, and teacher dashboards.</p>
                    <div class="cta-buttons">
                        <a href="register.php" class="btn-hero btn-hero-primary">
                            <i class="fas fa-user-plus"></i> Create Free Account
                        </a>
                        <a href="login.php" class="btn-hero btn-hero-outline">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
    <footer class="public-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <i class="fas fa-graduation-cap"></i>
                        <span>ClassSync</span>
                    </div>
                    <p>Your centralized college hub for classwork, assignments, study materials, and video lectures.</p>
                </div>
                <div class="footer-links-col">
                    <h4>Quick Links</h4>
                    <a href="#home">Home</a>
                    <a href="#features">Features</a>
                    <a href="#materials">Study Materials</a>
                    <a href="#videos">Video Lectures</a>
                </div>
                <div class="footer-links-col">
                    <h4>Account</h4>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                </div>
                <div class="footer-links-col">
                    <h4>For Teachers</h4>
                    <a href="login.php">Upload Materials</a>
                    <a href="login.php">Add Video Lectures</a>
                    <a href="login.php">Create Assignments</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> ClassSync. Built for education.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
    <script src="assets/js/homepage.js"></script>
</body>
</html>

<?php
// Helper function to extract YouTube thumbnail
function getYouTubeThumbnail($url) {
    $videoId = '';
    // Match youtube.com/watch?v=ID
    if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $videoId = $matches[1];
    }
    // Match youtu.be/ID
    elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $videoId = $matches[1];
    }
    // Match youtube.com/embed/ID
    elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $videoId = $matches[1];
    }
    
    if ($videoId) {
        return "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";
    }
    return null;
}
?>
