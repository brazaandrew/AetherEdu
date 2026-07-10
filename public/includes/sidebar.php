<?php
// Sidebar Navigation Component
require_once __DIR__ . '/../../src/Helpers/database.php';
$sidebarSchoolLogo = get_school_logo();
$sidebarSchoolName = $_SESSION['active_school_name'] ?? 'The Light Christian Academy';
$sidebarSchoolAcronym = '';
foreach (explode(' ', $sidebarSchoolName) as $w) {
    $sidebarSchoolAcronym .= strtoupper($w[0] ?? '');
}
if (empty($sidebarSchoolAcronym)) {
    $sidebarSchoolAcronym = 'LMS';
}
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-brand">
            <div class="brand-icon">
                <img src="<?= htmlspecialchars($sidebarSchoolLogo) ?>" alt="Logo">
            </div>
            <div class="sidebar-brand-text">
                <h4><?= htmlspecialchars($sidebarSchoolAcronym) ?></h4>
                <small><?= htmlspecialchars($sidebarSchoolName) ?></small>
            </div>
        </a>
    </div>
    
    <nav class="sidebar-menu nav flex-column">
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>
        
        <?php if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'teacher'): ?>
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">Academic</small>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'active' : '' ?>" href="subjects.php">
            <i class="bi bi-book"></i>
            <span>Subjects</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'activities.php' ? 'active' : '' ?>" href="activities.php">
            <i class="bi bi-file-earmark-text"></i>
            <span>Activities</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'quizzes.php' ? 'active' : '' ?>" href="quizzes.php">
            <i class="bi bi-card-checklist"></i>
            <span>Quizzes</span>
        </a>
        

        
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">Grades</small>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'teacher-grades.php' ? 'active' : '' ?>" href="teacher-grades.php">
            <i class="bi bi-award"></i>
            <span>Grade Management</span>
        </a>
        <?php endif; ?>
        
        <?php if ($_SESSION['user']['role'] === 'student'): ?>
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">Portal</small>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'my-subjects.php' ? 'active' : '' ?>" href="my-subjects.php">
            <i class="bi bi-book-fill"></i>
            <span>My Subjects</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'enroll.php' ? 'active' : '' ?>" href="enroll.php">
            <i class="bi bi-key-fill"></i>
            <span>Enroll in Subjects</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'grades.php' ? 'active' : '' ?>" href="grades.php">
            <i class="bi bi-graph-up"></i>
            <span>My Grades</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : '' ?>" href="attendance.php">
            <i class="bi bi-list-check"></i>
            <span>My Attendance</span>
        </a>
        <?php endif; ?>
        
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">Administration</small>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'grades.php' ? 'active' : '' ?>" href="grades.php">
            <i class="bi bi-graph-up"></i>
            <span>Grades</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'generate-qr.php' ? 'active' : '' ?>" href="generate-qr.php">
            <i class="bi bi-qr-code"></i>
            <span>QR Attendance</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'grade-periods.php' ? 'active' : '' ?>" href="grade-periods.php">
            <i class="bi bi-calendar-check"></i>
            <span>Grade Periods</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>" href="users.php">
            <i class="bi bi-people"></i>
            <span>Users</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" href="reports.php">
            <i class="bi bi-file-bar-graph"></i>
            <span>Reports</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'batch-student-upload.php' ? 'active' : '' ?>" href="batch-student-upload.php">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            <span>Batch Upload</span>
        </a>
        <?php endif; ?>
        
        <?php if ($_SESSION['user']['role'] === 'it_personnel'): ?>
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">IT Dept</small>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>" href="users.php">
            <i class="bi bi-people"></i>
            <span>Manage Users</span>
        </a>
        <?php endif; ?>
        
        <?php if (in_array($_SESSION['user']['role'], ['admin', 'registrar'])): ?>
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">Registrar</small>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'enrollment.php' ? 'active' : '' ?>" href="enrollment.php">
            <i class="bi bi-person-plus"></i>
            <span>Enrollment</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'student-list.php' ? 'active' : '' ?>" href="student-list.php">
            <i class="bi bi-people-fill"></i>
            <span>Student List</span>
        </a>
        <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['school-forms.php','sf9.php','sf10.php']) ? 'active' : '' ?>" href="school-forms.php">
            <i class="bi bi-file-earmark-ruled"></i>
            <span>School Forms</span>
        </a>
        <?php endif; ?>

        
        <?php if (in_array($_SESSION['user']['role'], ['admin', 'teacher'])): ?>
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">Attendance</small>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'take-attendance.php' ? 'active' : '' ?>" href="take-attendance.php">
            <i class="bi bi-clipboard-check"></i>
            <span>Take Attendance</span>
        </a>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : '' ?>" href="attendance.php">
            <i class="bi bi-list-check"></i>
            <span>Attendance Records</span>
        </a>
        <?php endif; ?>
        
        <?php if ($_SESSION['user']['role'] === 'teacher'): ?>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'teacher-time-log.php' ? 'active' : '' ?>" href="teacher-time-log.php">
            <i class="bi bi-clock-history"></i>
            <span>Time Log</span>
        </a>
        <?php endif; ?>
        
        <?php if (in_array($_SESSION['user']['role'], ['admin', 'hr', 'teacher', 'librarian', 'cashier', 'nurse', 'it_personnel'])): ?>
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">Human Resources</small>
        <?php if (in_array($_SESSION['user']['role'], ['admin', 'hr'])): ?>
        <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['hr-dashboard.php', 'employee-201-file.php']) ? 'active' : '' ?>" href="hr-dashboard.php">
            <i class="bi bi-person-vcard"></i>
            <span>Employee 201 Files</span>
        </a>
        <?php endif; ?>
        <?php if (in_array($_SESSION['user']['role'], ['teacher', 'librarian', 'cashier', 'nurse', 'it_personnel', 'hr'])): ?>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'my-201-file.php' ? 'active' : '' ?>" href="my-201-file.php">
            <i class="bi bi-folder2-open"></i>
            <span>My 201 File</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php if (!in_array($_SESSION['user']['role'], ['nurse', 'hr', 'registrar'])): ?>
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">Library</small>
        <?php if (in_array($_SESSION['user']['role'], ['admin', 'librarian'])): ?>
        <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['library.php', 'library-add.php', 'library-edit.php', 'library-borrowings.php']) ? 'active' : '' ?>" href="library.php">
            <i class="bi bi-book"></i>
            <span>Library Management</span>
        </a>
        <?php endif; ?>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'library-catalog.php' ? 'active' : '' ?>" href="library-catalog.php">
            <i class="bi bi-collection"></i>
            <span>Library Catalog</span>
        </a>
        <?php if ($_SESSION['user']['role'] === 'student'): ?>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'my-borrowings.php' ? 'active' : '' ?>" href="my-borrowings.php">
            <i class="bi bi-bookmark"></i>
            <span>My Borrowings</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>
        
        <!-- Finance Menu -->
        <?php if (!in_array($_SESSION['user']['role'], ['nurse', 'hr', 'registrar'])): ?>
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">Finance</small>
        <?php if (in_array($_SESSION['user']['role'], ['admin', 'cashier'])): ?>
        <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['finance.php', 'finance-fee-types.php', 'finance-payments.php', 'finance-reports.php']) ? 'active' : '' ?>" href="finance.php">
            <i class="bi bi-cash-stack"></i>
            <span>Finance Management</span>
        </a>
        <?php endif; ?>
        <?php if ($_SESSION['user']['role'] === 'student'): ?>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'my-balance.php' ? 'active' : '' ?>" href="my-balance.php">
            <i class="bi bi-wallet2"></i>
            <span>My Balance</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>
        
        <!-- Clinic Menu -->
        <?php if (!in_array($_SESSION['user']['role'], ['registrar', 'hr', 'librarian', 'cashier', 'it_personnel'])): ?>
        <div class="sidebar-divider"></div>
        <small class="sidebar-heading">Clinic</small>
        <?php if (in_array($_SESSION['user']['role'], ['admin', 'nurse'])): ?>
        <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['clinic.php', 'clinic-student-profile.php']) ? 'active' : '' ?>" href="clinic.php">
            <i class="bi bi-heart-pulse"></i>
            <span>School Clinic</span>
        </a>
        <?php endif; ?>
        <?php if ($_SESSION['user']['role'] === 'student'): ?>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'my-clinic-profile.php' ? 'active' : '' ?>" href="my-clinic-profile.php">
            <i class="bi bi-file-medical"></i>
            <span>My Clinic Profile</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>
        
        <div class="sidebar-divider"></div>
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>" href="profile.php">
            <i class="bi bi-person-circle"></i>
            <span>My Profile</span>
        </a>
        
        <a class="nav-link text-danger" href="logout.php">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>

        <div class="sidebar-divider"></div>
        <a class="nav-link" href="#" id="sidebarCollapseToggle">
            <i class="bi bi-chevron-bar-left"></i>
            <span>Collapse Menu</span>
        </a>
    </nav>
</div>

<!-- Mobile Sidebar Overlay Backdrop -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Mobile Toggle -->
<button class="mobile-toggle" id="mobileToggle" aria-label="Toggle navigation menu">
    <i class="bi bi-list"></i>
</button>

<script>
// Sidebar collapse/expand toggle functionality
(function() {
    const sidebar = document.getElementById('sidebar');
    const collapseToggle = document.getElementById('sidebarCollapseToggle');
    
    // Load preference from localStorage
    if (localStorage.getItem('sidebar-collapsed') === 'true') {
        if (sidebar) sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
        if (collapseToggle) {
            const icon = collapseToggle.querySelector('i');
            if (icon) icon.className = 'bi bi-chevron-bar-right';
        }
    }
    
    if (collapseToggle && sidebar) {
        collapseToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
            
            const icon = collapseToggle.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.className = 'bi bi-chevron-bar-right';
            } else {
                icon.className = 'bi bi-chevron-bar-left';
            }
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        });
    }
})();

// Mobile sidebar drawer toggle functionality
(function() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('mobileToggle');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('show');
        if (toggle) {
            toggle.classList.add('sidebar-is-open');
            const icon = toggle.querySelector('i');
            if (icon) icon.className = 'bi bi-x';
        }
        if (overlay) overlay.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent background scroll
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('show');
        if (toggle) {
            toggle.classList.remove('sidebar-is-open');
            const icon = toggle.querySelector('i');
            if (icon) icon.className = 'bi bi-list';
        }
        if (overlay) overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (toggle) {
        toggle.addEventListener('click', function() {
            if (sidebar && sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    // Close when clicking overlay
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close when clicking a nav link on mobile (auto-navigate)
    if (sidebar) {
        sidebar.querySelectorAll('.nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });
    }

    // Close sidebar on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
})();

// Dynamic Table Responsive Labels Helper
(function() {
    function setupResponsiveTables() {
        document.querySelectorAll("table").forEach(function(table) {
            if (table.closest('.modal') || table.classList.contains('no-responsive')) return;
            
            let headerElements = table.querySelectorAll("thead th, thead td");
            if (headerElements.length === 0) {
                const firstRow = table.querySelector("tr");
                if (firstRow) {
                    headerElements = firstRow.querySelectorAll("th, td");
                }
            }
            
            const headers = Array.from(headerElements).map(function(el) {
                const hasCheckbox = el.querySelector("input[type='checkbox']");
                if (hasCheckbox) return "Select";
                
                let text = el.textContent.trim();
                text = text.replace(/\s+/g, ' ');
                
                if (!text && el.querySelector("i")) {
                    const icon = el.querySelector("i");
                    if (icon.classList.contains("bi-trash") || icon.classList.contains("bi-pencil")) return "Actions";
                }
                
                return text;
            });
            
            table.querySelectorAll("tbody tr").forEach(function(row) {
                row.querySelectorAll("td").forEach(function(td, index) {
                    if (headers[index] && !td.getAttribute("data-label")) {
                        td.setAttribute("data-label", headers[index]);
                    }
                });
            });
        });
    }
    
    // Run on load
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", setupResponsiveTables);
    } else {
        setupResponsiveTables();
    }
    
    // Also run for dynamic content (e.g. Bootstrap tabs)
    document.addEventListener("shown.bs.tab", setupResponsiveTables);
})();
</script>