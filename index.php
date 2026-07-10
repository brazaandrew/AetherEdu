<?php
declare(strict_types=1);

require_once __DIR__ . '/src/Helpers/env.php';
require_once __DIR__ . '/src/Helpers/database.php';
require_once __DIR__ . '/src/Helpers/session.php';
require_once __DIR__ . '/src/Services/TenantService.php';

loadEnv(__DIR__ . '/.env');
startSecureSession();

resolve_tenant();

// Clear active school selection
if (isset($_GET['school']) && $_GET['school'] === 'clear') {
    unset($_SESSION['active_school_id']);
    unset($_SESSION['active_school_db']);
    unset($_SESSION['active_school_name']);
    unset($_SESSION['user']);
    header('Location: index.php');
    exit;
}

// Redirect if already logged in
if (isset($_SESSION['user']) && isset($_SESSION['active_school_db'])) {
    header('Location: public/dashboard.php');
    exit;
}

$tenantService = new TenantService();
$schools = $tenantService->listSchools();

$isTenantActive = isset($_SESSION['active_school_db']);
$schoolName = $_SESSION['active_school_name'] ?? 'The Light Christian Academy';
$schoolAcronym = '';
foreach (explode(' ', $schoolName) as $w) {
    $schoolAcronym .= strtoupper($w[0] ?? '');
}
if (empty($schoolAcronym)) {
    $schoolAcronym = 'LMS';
}

if ($isTenantActive):
    // Fetch custom school landing page settings from database
    $landingSettings = [];
    try {
        $stmt = db()->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch()) {
            $landingSettings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // Fallback
    }
    
    $heroSubtitle = $landingSettings['school_hero_subtitle'] ?? 'Providing quality education rooted in Christian values. Enroll students, manage grades, and enhance educational outcomes.';
    $aboutText = $landingSettings['school_about_text'] ?? 'We deliver an integrated digital workspace that connects students, teachers, and administrators. Our system simplifies course planning, progress evaluation, and secure data access.';
    $contactEmail = $landingSettings['school_contact_email'] ?? 'info@school.edu';
    $contactPhone = $landingSettings['school_contact_phone'] ?? '+63 912 345 6789';
    $contactAddress = $landingSettings['school_contact_address'] ?? '123 Academic St, Manila, Philippines';
    
    $dbHeroImage = $landingSettings['school_hero_image'] ?? '';
    $heroImage = !empty($dbHeroImage) ? 'public/' . $dbHeroImage : 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?w=800';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?= htmlspecialchars($schoolAcronym) ?> - <?= htmlspecialchars($schoolName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap');
        
        :root {
            --primary: #2563EB;
            --primary-dark: #1E40AF;
            --accent: #14B8A6;
            --dark: #0F172A;
            --light-bg: #F8FAFC;
            --text-main: #1E293B;
            --text-sub: #64748B;
            --border-color: #E2E8F0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-main);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--dark);
        }
        
        /* Modern Glassmorphic Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--dark) !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .navbar-brand img {
            border-radius: 8px;
            object-fit: cover;
        }
        
        .nav-link {
            color: var(--text-sub) !important;
            font-weight: 600;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }
        
        .nav-link:hover {
            color: var(--primary) !important;
        }
        
        .btn-portal-login {
            background-color: var(--primary);
            color: #FFFFFF !important;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.55rem 1.5rem;
            border-radius: 50px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-portal-login:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 6px 12px -1px rgba(37, 99, 235, 0.3);
            transform: translateY(-1px);
        }
        
        /* Premium Hero Section */
        .hero-section {
            padding: 9rem 0 6rem;
            background: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.05) 0%, rgba(20, 184, 166, 0.02) 90%);
            border-bottom: 1px solid var(--border-color);
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.15;
            color: var(--dark);
            margin-bottom: 1.25rem;
        }
        
        .hero-subtitle {
            font-size: 1.12rem;
            line-height: 1.6;
            color: var(--text-sub);
            margin-bottom: 2.25rem;
            max-width: 520px;
        }
        
        .btn-hero-primary {
            background-color: var(--primary);
            color: white !important;
            padding: 0.8rem 2.25rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.98rem;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-hero-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.35);
        }
        
        .btn-hero-outline {
            border: 2px solid var(--border-color);
            background: transparent;
            color: var(--text-main) !important;
            padding: 0.7rem 2.25rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.98rem;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-left: 0.75rem;
        }
        
        .btn-hero-outline:hover {
            background: #FFFFFF;
            border-color: var(--primary);
            color: var(--primary) !important;
            transform: translateY(-2px);
        }
        
        .hero-image-wrap {
            position: relative;
            z-index: 5;
        }
        
        .hero-image-wrap img {
            border-radius: var(--border-radius-md, 12px);
            box-shadow: var(--shadow-premium, 0 20px 40px rgba(15,23,42,0.08));
            border: 1px solid var(--border-color);
            max-height: 420px;
            object-fit: cover;
            width: 100%;
        }
        
        /* Features Section */
        .features-section {
            padding: 7rem 0;
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
        }
        
        .section-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dark);
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .section-subtitle {
            font-size: 1.05rem;
            color: var(--text-sub);
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .feature-card {
            background: var(--light-bg);
            padding: 2.5rem 2rem;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            transition: all 0.25s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg, 0 10px 20px rgba(15,23,42,0.05));
            border-color: var(--primary);
        }
        
        .feature-icon {
            width: 56px;
            height: 56px;
            background: rgba(37, 99, 235, 0.08);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 1.5rem;
        }
        
        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        
        .feature-description {
            font-size: 0.94rem;
            line-height: 1.6;
            color: var(--text-sub);
        }
        
        /* About section split details */
        .about-section {
            padding: 7rem 0;
            background-color: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
        }
        
        /* Stats Section */
        .stats-section {
            background: var(--dark);
            color: white;
            padding: 5rem 0;
        }
        
        .stat-card {
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--accent);
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.8;
            font-weight: 600;
        }
        
        /* Footer */
        .footer {
            background: #0B0F19;
            color: #94A3B8;
            padding: 5rem 0 2rem;
            border-top: 1px solid #1E293B;
        }
        
        .footer h5, .footer h6 {
            color: #FFFFFF;
        }
        
        .footer a {
            color: #94A3B8;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .footer a:hover {
            color: #FFFFFF;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 800;
            font-size: 1.25rem;
        }
        
        .footer-logo img {
            border-radius: 6px;
        }
        
        @media (max-width: 768px) {
            .hero-title { font-size: 2.25rem; }
            .hero-subtitle { font-size: 1.05rem; }
            .btn-hero-primary, .btn-hero-outline { display: flex; width: 100%; justify-content: center; margin: 0.5rem 0 !important; }
            .section-title { font-size: 1.75rem; }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="<?= htmlspecialchars(get_school_logo()) ?>" alt="Logo" style="height: 38px; width: 38px;"><?= htmlspecialchars($schoolAcronym) ?>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list fs-3" style="color: var(--dark);"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="index.php?school=clear"><i class="bi bi-arrow-left-circle me-1"></i>Change School</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a href="public/login.php" class="btn-portal-login">
                            <i class="bi bi-box-arrow-in-right"></i> Login to Portal
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-content">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h1 class="hero-title"><?= htmlspecialchars($schoolName) ?></h1>
                    <p class="hero-subtitle"><?= htmlspecialchars($heroSubtitle) ?></p>
                    <div class="hero-buttons">
                        <a href="public/login.php" class="btn-hero-primary">
                            Get Started <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="#features" class="btn-hero-outline">
                            Learn More
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 hero-image-wrap mt-5 mt-lg-0 text-center">
                    <img src="<?= htmlspecialchars($heroImage) ?>" alt="Welcome Banner Image">
                </div> 
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title">Powerful Features for Modern Learning</h2>
            <p class="section-subtitle">Everything you need to deliver exceptional educational experiences</p>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-book"></i>
                        </div>
                        <h3 class="feature-title">Course Management</h3>
                        <p class="feature-description">
                            Create and organize subjects with ease. Upload materials, assignments, and resources 
                            for students to access anytime, anywhere.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-card-checklist"></i>
                        </div>
                        <h3 class="feature-title">Smart Assessments</h3>
                        <p class="feature-description">
                            Build quizzes with multiple question types. Auto-grading for MCQs and True/False 
                            saves time while providing instant feedback.
                        </p>
                    </div> 
                </div>
                

                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3 class="feature-title">Progress Tracking</h3>
                        <p class="feature-description">
                            Monitor student performance with comprehensive analytics. Track grades, 
                            completion rates, and identify areas for improvement.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3 class="feature-title">Role-Based Access</h3>
                        <p class="feature-description">
                            Secure role management for administrators, teachers, and students. 
                            Each user sees only what they need to see.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h3 class="feature-title">Mobile Responsive</h3>
                        <p class="feature-description">
                            Access the platform from any device. Fully responsive design ensures 
                            seamless experience on desktop, tablet, and mobile.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-number">
                            <i class="bi bi-infinity"></i>
                        </div>
                        <div class="stat-label">Unlimited Courses</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-number">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div class="stat-label">Auto-Grading Modules</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Active Learning Console</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h2 class="mb-3">About Our Learning Platform</h2>
                    <p class="lead text-secondary" style="font-size: 1.05rem; line-height: 1.7;"><?= htmlspecialchars($aboutText) ?></p>
                    <div class="mt-4">
                        <a href="public/login.php" class="btn-portal-login">
                            <i class="bi bi-box-arrow-in-right"></i> Access Login Gate
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="p-4 bg-white rounded-lg shadow-sm border border-light" style="border-radius: var(--border-radius-md, 12px);">
                        <i class="bi bi-mortarboard text-primary" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Transforming Education</h4>
                        <p class="text-muted mb-0">Unifying classrooms, grade tracking, and administrative controls in a secure, isolated cloud database.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="footer-logo mb-3">
                        <img src="<?= htmlspecialchars(get_school_logo()) ?>" alt="Logo" style="height: 38px; width: 38px;"><?= htmlspecialchars($schoolAcronym) ?>
                    </div>
                    <p style="color: #64748B; max-width: 320px;">
                        <?= htmlspecialchars($schoolName) ?> - Shaping minds, building character, and elevating futures.
                    </p>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Quick Navigation</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#features">Key Features</a></li>
                        <li class="mb-2"><a href="#about">About Console</a></li>
                        <li class="mb-2"><a href="public/login.php">Login Gate</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="mb-3">Get in Touch</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-envelope text-primary me-2"></i>
                            <a href="mailto:<?= htmlspecialchars($contactEmail) ?>"><?= htmlspecialchars($contactEmail) ?></a>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone text-primary me-2"></i>
                            <span><?= htmlspecialchars($contactPhone) ?></span>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            <span class="small d-inline-block text-wrap" style="vertical-align: top; max-width: 200px;"><?= htmlspecialchars($contactAddress) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.06);">
            <div class="text-center text-secondary small">
                <p class="mb-0">&copy; <?= date('Y') ?> <?= htmlspecialchars($schoolName) ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
<?php else: ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AetherEdu - Select School Directory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap');
        
        :root {
            --primary: #2563EB;
            --primary-dark: #1E40AF;
            --accent: #14B8A6;
            --dark: #0F172A;
            --light-bg: #F4F8FD;
            --text-main: #1E293B;
            --text-sub: #64748B;
            --border-color: #E2E8F0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }

        .portal-container {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(13, 27, 42, 0.08);
            width: 100%;
            max-width: 1060px;
            min-height: 660px;
            display: flex;
            overflow: hidden;
            margin-bottom: 1.5rem;
            animation: scaleUp 0.5s ease-out;
        }

        @keyframes scaleUp {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Left Hero Panel */
        .portal-hero {
            background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);
            width: 42%;
            color: #ffffff;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .portal-hero::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }

        .portal-hero-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-top: 1rem;
        }

        .portal-hero-logo img {
            height: 80px;
            width: auto;
            margin-bottom: 0.5rem;
        }

        .portal-hero-logo h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .portal-hero-logo p {
            font-size: 0.85rem;
            opacity: 0.9;
            letter-spacing: 0.5px;
        }

        .portal-hero-desc {
            font-size: 0.88rem;
            line-height: 1.6;
            text-align: center;
            opacity: 0.85;
            margin: 1.5rem 0;
        }

        .portal-hero-icons {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
            margin: 1rem 0;
        }

        .portal-hero-icons .icon-bubble {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            backdrop-filter: blur(4px);
            transition: all 0.3s ease;
        }

        .portal-hero-icons .icon-bubble:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .portal-hero-illustration {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            margin-top: 1.5rem;
        }

        .portal-hero-illustration svg {
            max-width: 90%;
            height: auto;
            filter: drop-shadow(0 15px 25px rgba(0, 0, 0, 0.2));
        }

        /* Right Content Panel */
        .portal-content {
            width: 58%;
            padding: 3.5rem 4rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .portal-help-btn {
            position: absolute;
            top: 2rem;
            right: 2.5rem;
            text-decoration: none;
            font-size: 0.88rem;
            color: var(--primary);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: opacity 0.2s ease;
        }

        .portal-help-btn:hover {
            opacity: 0.85;
        }

        .portal-header {
            text-align: center;
            margin-top: 1rem;
            margin-bottom: 2rem;
        }

        .portal-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }

        .portal-header h3 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.4rem;
            font-size: 1.6rem;
        }

        .portal-header p {
            color: var(--text-sub);
            font-size: 0.9rem;
            margin: 0;
        }

        /* Search Box */
        .search-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-wrapper i {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-sub);
            font-size: 1.05rem;
        }

        .search-input {
            width: 100%;
            padding: 0.85rem 1.2rem 0.85rem 2.8rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-size: 0.95rem;
            color: var(--text-main);
            background-color: #FAFBFC;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
        }

        /* Schools List */
        .schools-list {
            max-height: 235px;
            overflow-y: auto;
            padding-right: 0.4rem;
        }

        .schools-dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-top: 5px;
            max-height: 250px;
            overflow-y: auto;
        }

        .schools-dropdown-list .school-card {
            margin-bottom: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            border-bottom: 1px solid var(--border-color) !important;
            padding: 0.85rem 1.25rem !important;
            transition: all 0.2s ease;
        }

        .schools-dropdown-list .school-card:last-child {
            border-bottom: none !important;
        }

        .schools-dropdown-list .school-card:hover {
            background-color: #F8FAFC !important;
            transform: none !important;
            box-shadow: none !important;
        }

        .school-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            text-decoration: none !important;
            margin-bottom: 0.85rem;
            background-color: #ffffff;
            transition: all 0.25s ease;
        }

        .school-card:hover {
            border-color: var(--primary);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.05);
            transform: translateY(-1px);
        }

        .school-card-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .school-logo-badge {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #ffffff;
            font-size: 1.15rem;
        }

        .school-logo-blue {
            background: linear-gradient(135deg, #2563EB 0%, #1E40AF 100%);
        }

        .school-logo-teal {
            background: linear-gradient(135deg, #14B8A6 0%, #0F766E 100%);
        }

        .school-info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            margin: 0 0 0.15rem;
        }

        .school-info p {
            font-size: 0.8rem;
            color: var(--text-sub);
            margin: 0;
        }

        .school-arrow-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgba(37, 99, 235, 0.05);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .school-card:hover .school-arrow-btn {
            background-color: var(--primary);
            color: #ffffff;
        }

        /* OR Divider */
        .or-divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--text-sub);
            font-size: 0.75rem;
            font-weight: 600;
            margin: 1.25rem 0;
            letter-spacing: 1px;
        }

        .or-divider::before,
        .or-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border-color);
        }

        .or-divider:not(:empty)::before {
            margin-right: 1rem;
        }

        .or-divider:not(:empty)::after {
            margin-left: 1rem;
        }

        /* Add Org Button */
        .btn-add-org {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem 1.2rem;
            border: 1px dashed var(--primary);
            border-radius: 12px;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.9rem;
            background-color: transparent;
            text-decoration: none !important;
            transition: all 0.25s ease;
        }

        .btn-add-org:hover {
            background-color: rgba(37, 99, 235, 0.03);
            border-style: solid;
            color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* Footer Section */
        .portal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.75rem;
            color: var(--text-sub);
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .portal-footer-left {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .portal-footer-left i {
            color: var(--primary);
            font-size: 0.95rem;
        }

        .portal-footer-right {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .portal-footer-right i {
            font-size: 0.9rem;
        }

        .bottom-banner-text {
            font-size: 0.85rem;
            color: var(--text-sub);
            font-weight: 500;
            margin-top: 0.5rem;
        }

        /* Scrollbar styling */
        .schools-list::-webkit-scrollbar {
            width: 5px;
        }
        .schools-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .schools-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .schools-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .portal-container {
                flex-direction: column;
                max-width: 500px;
                min-height: auto;
            }
            .portal-hero {
                width: 100%;
                padding: 2.5rem 2rem;
            }
            .portal-content {
                width: 100%;
                padding: 2.5rem 2rem;
            }
            .portal-help-btn {
                top: 1.5rem;
                right: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="portal-container">
        <!-- Left Panel: Brand & Illustration -->
        <div class="portal-hero">
            <div class="portal-hero-logo">
                <img src="public/image/aetheredu_logo.png" alt="AetherEdu Logo">
                <h2>AetherEdu</h2>
                <p>Empowering Learning. <span class="text-info">Elevating Futures.</span></p>
            </div>
            
            <p class="portal-hero-desc">
                AetherEdu is a modern, cloud-based learning management system that helps schools, teachers, and students connect, collaborate, and achieve excellence.
            </p>
            
            <div class="portal-hero-icons">
                <div class="icon-bubble" title="Academics"><i class="bi bi-mortarboard"></i></div>
                <div class="icon-bubble" title="Collaboration"><i class="bi bi-people"></i></div>
                <div class="icon-bubble" title="Analytics"><i class="bi bi-graph-up"></i></div>
                <div class="icon-bubble" title="Documentation"><i class="bi bi-file-text"></i></div>
            </div>
            
            <div class="portal-hero-illustration">
                <!-- High-fidelity inline SVG dashboard laptop mockup -->
                <svg viewBox="0 0 320 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="140" cy="185" rx="90" ry="8" fill="black" fill-opacity="0.25"/>
                    <ellipse cx="250" cy="180" rx="40" ry="5" fill="black" fill-opacity="0.15"/>
                    
                    <rect x="70" y="55" width="150" height="98" rx="8" fill="#0A0F1D"/>
                    <rect x="75" y="60" width="140" height="88" rx="4" fill="#1E293B"/>
                    <rect x="80" y="65" width="30" height="78" rx="2" fill="#0F172A" fill-opacity="0.8"/>
                    
                    <circle cx="150" cy="95" r="20" stroke="#14B8A6" stroke-width="6"/>
                    <circle cx="150" cy="95" r="20" stroke="#2563EB" stroke-width="6" stroke-dasharray="80 120"/>
                    <rect x="110" y="125" width="22" height="12" rx="2" fill="#2563EB"/>
                    <rect x="136" y="125" width="22" height="12" rx="2" fill="#14B8A6"/>
                    <rect x="162" y="125" width="22" height="12" rx="2" fill="#475569"/>
                    
                    <path d="M50 152H240L250 162C250 164.209 248.209 166 246 166H44C41.7909 166 40 164.209 40 162L50 152Z" fill="#E2E8F0"/>
                    <path d="M60 154H230L235 158H55L60 154Z" fill="#94A3B8"/>
                    <rect x="125" y="159" width="30" height="5" rx="1.5" fill="#64748B"/>
                    
                    <path d="M225 150H295C297.761 150 300 152.239 300 155V173C300 175.761 297.761 178 295 178H225V150Z" fill="#1E3A8A"/>
                    <rect x="220" y="152" width="6" height="24" rx="2" fill="#2563EB"/>
                    <rect x="225" y="174" width="70" height="4" fill="#E2E8F0"/>
                    
                    <path d="M230 125H290C292.761 125 295 127.239 295 130V148C295 150.761 292.761 153 290 153H230V125Z" fill="#0D9488"/>
                    <rect x="225" y="127" width="6" height="24" rx="2" fill="#14B8A6"/>
                    <rect x="230" y="149" width="60" height="4" fill="#E2E8F0"/>
                    
                    <path d="M260 88L225 102L260 116L295 102L260 88Z" fill="#1E293B" stroke="#0F172A" stroke-width="2"/>
                    <path d="M242 109V118C242 121.314 250.059 124 260 124C269.941 124 278 121.314 278 118V109" fill="#1E293B" stroke="#0F172A" stroke-width="2"/>
                    <path d="M260 102L235 110L233 125" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="233" cy="125" r="2" fill="#F59E0B"/>
                    
                    <circle cx="280" cy="190" r="10" fill="#E2E8F0"/>
                    <path d="M280 180C280 180 275 170 276 160C277 150 280 152 280 152C280 152 283 150 284 160C285 170 280 180 280 180Z" fill="#10B981"/>
                    <path d="M280 180C280 180 288 175 292 165C296 155 293 157 293 157C293 157 291 159 288 168C285 177 280 180 280 180Z" fill="#059669"/>
                    <path d="M280 180C280 180 272 175 268 165C264 155 267 157 267 157C267 157 269 159 272 168C275 177 280 180 280 180Z" fill="#059669"/>
                </svg>
            </div>
        </div>

        <!-- Right Panel: School Selector -->
        <div class="portal-content">
            <a href="mailto:support@aetheredu.com" class="portal-help-btn">
                <i class="bi bi-question-circle"></i> Need help?
            </a>
            
            <div class="portal-header">
                <div class="portal-header-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h3>Welcome to AetherEdu</h3>
                <p>Select your school or organization to continue</p>
            </div>

            <!-- Search School Directory -->
            <div class="search-wrapper position-relative">
                <label for="schoolSearch" class="form-label fw-semibold text-start w-100 mb-2" style="font-size: 0.92rem; color: var(--text-main);">Search your school here</label>
                <div class="position-relative">
                    <i class="bi bi-search"></i>
                    <input type="text" id="schoolSearch" class="search-input" placeholder="Type school name..." autocomplete="off">
                </div>
                
                <!-- Floating School Selector List -->
                <div class="schools-dropdown-list shadow border rounded-3 mt-1" id="schoolDropdownList" style="display: none;">
                    <?php if (empty($schools)): ?>
                        <div class="py-3 text-center text-muted" style="font-size: 0.9rem;">
                            <i class="bi bi-building-exclamation fs-4 d-block mb-1"></i>
                            No schools registered.
                        </div>
                    <?php else: ?>
                        <?php foreach ($schools as $idx => $school): 
                            $firstChar = strtoupper(substr($school['name'], 0, 1));
                            $badgeClass = ($idx % 2 === 0) ? 'school-logo-blue' : 'school-logo-teal';
                        ?>
                            <a href="index.php?school=<?= urlencode($school['domain']) ?>" class="school-card school-item" data-name="<?= htmlspecialchars(strtolower($school['name'])) ?>">
                                <div class="school-card-left">
                                    <div class="school-logo-badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($firstChar) ?>
                                    </div>
                                    <div class="school-info">
                                        <h4><?= htmlspecialchars($school['name']) ?></h4>
                                        <?php
                                        $host = $_SERVER['HTTP_HOST'] ?? 'lms.local';
                                        $suffix = 'lms.local';
                                        if (strpos($host, '.') !== false && !in_array($host, ['127.0.0.1', '::1'])) {
                                            $parts = explode('.', $host, 2);
                                            $suffix = $parts[1] ?? 'lms.local';
                                        }
                                        ?>
                                        <p><?= htmlspecialchars($school['domain']) ?>.<?= htmlspecialchars($suffix) ?></p>
                                    </div>
                                </div>
                                <div class="school-arrow-btn">
                                    <i class="bi bi-arrow-right"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        <div class="py-3 text-center text-muted" id="noSchoolResults" style="display: none; font-size: 0.9rem;">
                            <i class="bi bi-search fs-4 d-block mb-1"></i>
                            No matching schools found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Divider -->
            <div class="or-divider">OR</div>

            <!-- Request Organization / Admin Portal -->
            <a href="public/super-admin.php" class="btn-add-org">
                <i class="bi bi-plus-lg"></i> Request to Add New Organization
            </a>

            <!-- Footer Meta -->
            <div class="portal-footer">
                <div class="portal-footer-left">
                    <i class="bi bi-patch-check-fill"></i>
                    <span><strong>Secure. Reliable. Trusted.</strong><br>Your data is protected with enterprise-grade security.</span>
                </div>
                <div class="portal-footer-right text-end">
                    <span>AetherEdu SaaS Multi-tenant v1.0<br>&copy; 2025 AetherEdu. All rights reserved.</span>
                    <i class="bi bi-cloud-check ms-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bottom-banner-text text-center">
        Making Education Smarter. Together. <span class="text-primary">&hearts;</span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('schoolSearch');
            const dropdown = document.getElementById('schoolDropdownList');
            
            if (searchInput && dropdown) {
                // Show dropdown when focused
                searchInput.addEventListener('focus', function() {
                    dropdown.style.display = 'block';
                    filterSchools();
                });
                
                // Hide dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.style.display = 'none';
                    }
                });
                
                // Filter schools list as cashier types
                searchInput.addEventListener('input', filterSchools);
                
                function filterSchools() {
                    const query = searchInput.value.toLowerCase().trim();
                    let hasVisibleItems = false;
                    
                    document.querySelectorAll('#schoolDropdownList .school-item').forEach(function(item) {
                        const name = item.getAttribute('data-name') || '';
                        if (name.includes(query)) {
                            item.style.setProperty('display', 'flex', 'important');
                            hasVisibleItems = true;
                        } else {
                            item.style.setProperty('display', 'none', 'important');
                        }
                    });
                    
                    const noResults = document.getElementById('noSchoolResults');
                    if (noResults) {
                        noResults.style.display = hasVisibleItems ? 'none' : 'block';
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php endif; ?>
