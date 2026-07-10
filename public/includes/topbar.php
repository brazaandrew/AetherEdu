<?php
// Securely get DB and User ID references
$userId = $_SESSION['user']['id'] ?? 0;
$notifications = [];
$unreadCount = 0;

// Load NotificationService for due-date reminders
$_notifServicePath = __DIR__ . '/../../src/Services/NotificationService.php';
if (file_exists($_notifServicePath)) {
    require_once $_notifServicePath;
}

if ($userId > 0) {
    try {
        $db = db();
        // Self-healing database schema: create notifications table if missing
        $db->exec("CREATE TABLE IF NOT EXISTS `notifications` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `title` VARCHAR(255) NOT NULL,
            `message` TEXT NOT NULL,
            `link` VARCHAR(255) DEFAULT '#',
            `icon` VARCHAR(50) DEFAULT 'bi-info-circle',
            `is_read` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_user_read` (`user_id`, `is_read`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        // Auto-seed welcome notifications for first-time displays
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
        $checkStmt->execute([$userId]);
        if ($checkStmt->fetchColumn() == 0) {
            $welcomeNotifs = [
                [
                    'title' => 'Welcome to eLMS Portal!',
                    'message' => 'Your workspace has been modernized with premium SaaS templates.',
                    'link' => 'dashboard.php',
                    'icon' => 'bi-info-circle-fill'
                ],
                [
                    'title' => 'Academic Term Settings Active',
                    'message' => 'Check your subject allocations and grades parameters in the menu.',
                    'link' => $_SESSION['user']['role'] === 'student' ? 'my-subjects.php' : 'subjects.php',
                    'icon' => 'bi-calendar-event'
                ]
            ];
            $insertStmt = $db->prepare("INSERT INTO notifications (user_id, title, message, link, icon) VALUES (?, ?, ?, ?, ?)");
            foreach ($welcomeNotifs as $wn) {
                $insertStmt->execute([$userId, $wn['title'], $wn['message'], $wn['link'], $wn['icon']]);
            }
        }

        // Due-date reminders for students (rate-limited: once per 30 min)
        if (($_SESSION['user']['role'] ?? '') === 'student' && class_exists('NotificationService')) {
            NotificationService::checkDueDateReminders($userId);
        }

        // Fetch notifications
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count unread
        $countStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $countStmt->execute([$userId]);
        $unreadCount = (int)$countStmt->fetchColumn();
    } catch (Exception $e) {
        // Fallback
    }
}
?>
<!-- Premium Top Bar Component -->
<div class="top-bar">
    <div class="topbar-left">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb-nav d-none d-sm-block" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></li>
            </ol>
        </nav>
    </div>
    
    <div class="topbar-right">
        <!-- Search bar -->
        <div class="topbar-search d-none d-md-block">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" placeholder="Search portal...">
        </div>
        
        <!-- Session Countdown Timer -->
        <div class="session-timer-badge d-none" id="sessionTimerBadge" title="Click to extend session">
            <i class="bi bi-clock-history"></i>
            <span id="sessionTimerText">--:--</span>
        </div>
        
        <!-- Dark Mode Toggle -->
        <button class="btn-icon" id="themeToggle" aria-label="Toggle dark mode">
            <i class="bi bi-sun-fill" id="themeToggleIcon"></i>
        </button>
        
        <!-- Notifications -->
        <div class="dropdown">
            <button class="btn-icon position-relative" type="button" id="notifDropdown" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem; padding: 0.25rem 0.4rem;">
                        <?= $unreadCount ?>
                    </span>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2" aria-labelledby="notifDropdown" style="width: 290px;" id="notifDropdownList">
                <li class="dropdown-header fw-bold text-primary pb-2 border-bottom mb-1 d-flex justify-content-between align-items-center">
                    <span>Notifications</span>
                    <?php if ($unreadCount > 0): ?>
                        <a href="#" onclick="markAllAsRead(event)" class="text-decoration-none small text-muted" id="markAllReadBtn" style="font-size: 0.72rem; font-weight: normal;">Mark all as read</a>
                    <?php endif; ?>
                </li>
                <?php if (empty($notifications)): ?>
                    <li class="p-3 text-center text-muted small" id="noNotificationsItem">
                        <i class="bi bi-bell-slash fs-4 d-block mb-1"></i>
                        No new notifications
                    </li>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                        <li>
                            <a class="dropdown-item text-wrap small py-2 rounded d-flex align-items-start gap-2 <?= $n['is_read'] ? 'opacity-60' : 'fw-semibold' ?>" 
                               href="<?= htmlspecialchars($n['link'] ?? '#') ?>" 
                               onclick="markAsRead(<?= $n['id'] ?>)">
                                <i class="bi <?= htmlspecialchars($n['icon'] ?? 'bi-info-circle') ?> text-primary mt-1"></i>
                                <div>
                                    <div class="notif-title" style="font-size: 0.82rem;"><?= htmlspecialchars($n['title']) ?></div>
                                    <div class="text-muted" style="font-size: 0.72rem; line-height: 1.3; font-weight: normal;"><?= htmlspecialchars($n['message']) ?></div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Profile Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 rounded-pill shadow-sm" type="button" data-bs-toggle="dropdown">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 30px; height: 30px; font-size: 0.82rem;">
                    <?= strtoupper(substr($_SESSION['user']['name'] ?? 'U', 0, 1)) ?>
                </div>
                <span class="d-none d-md-inline fw-semibold"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'User') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                <li><a class="dropdown-item py-2" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                <li><a class="dropdown-item py-2" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<script>
// Dark Mode Toggle Logic
(function() {
    const themeToggle = document.getElementById('themeToggle');
    const themeToggleIcon = document.getElementById('themeToggleIcon');
    
    // Read preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        if (themeToggleIcon) themeToggleIcon.className = 'bi bi-moon-stars-fill';
    } else {
        document.body.classList.remove('dark-mode');
        if (themeToggleIcon) themeToggleIcon.className = 'bi bi-sun-fill';
    }
    
    if (themeToggle && themeToggleIcon) {
        themeToggle.addEventListener('click', function() {
            let currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            let nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', nextTheme);
            document.documentElement.setAttribute('data-bs-theme', nextTheme);
            localStorage.setItem('theme', nextTheme);
            
            if (nextTheme === 'dark') {
                document.body.classList.add('dark-mode');
                themeToggleIcon.className = 'bi bi-moon-stars-fill';
            } else {
                document.body.classList.remove('dark-mode');
                themeToggleIcon.className = 'bi bi-sun-fill';
            }
        });
    }
})();

// Notifications Interactive AJAX Handlers
function markAsRead(id) {
    const params = new URLSearchParams();
    params.append('action', 'mark_read');
    params.append('id', id);

    fetch('ajax-notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    });
}

function markAllAsRead(e) {
    if (e) e.preventDefault();
    
    const params = new URLSearchParams();
    params.append('action', 'mark_all_read');

    fetch('ajax-notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Remove the notification badge counter
            const badge = document.querySelector('#notifDropdown .badge');
            if (badge) badge.remove();
            
            // Remove bold font weight and fade items
            document.querySelectorAll('#notifDropdownList .dropdown-item').forEach(item => {
                item.classList.add('opacity-60');
                item.classList.remove('fw-semibold');
            });
            
            // Hide the mark all read link
            const markAllBtn = document.getElementById('markAllReadBtn');
            if (markAllBtn) markAllBtn.remove();
        }
    });
}
</script>

<!-- Inactivity Warning Modal -->
<div class="session-modal-overlay" id="sessionWarningModal">
    <div class="session-modal-container">
        <div class="session-modal-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="session-modal-title">Session Expiring Soon</div>
        <div class="session-modal-text">
            Your session is about to expire due to inactivity. You will be automatically logged out in <span id="sessionCountdownWarning">120</span> seconds.
        </div>
        <div class="session-modal-buttons">
            <button class="session-btn session-btn-secondary" onclick="logoutSession()">Logout</button>
            <button class="session-btn session-btn-primary" onclick="extendSession()">Stay Logged In</button>
        </div>
    </div>
</div>

<script>
// Session Timer Script
(function() {
    const sessionLifetime = <?php echo (int)(function_exists('env') ? env('SESSION_LIFETIME', 3600) : 3600); ?>;
    let timeRemaining = <?php
        $lifetime = function_exists('env') ? (int)env('SESSION_LIFETIME', 3600) : 3600;
        if (isset($_SESSION['last_activity'])) {
            $remaining = (int)($_SESSION['last_activity'] + $lifetime - time());
            // Clamp between 0 and lifetime
            echo max(0, min($remaining, $lifetime));
        } else {
            // No last_activity yet (brand new login) — give full session lifetime
            echo $lifetime;
        }
    ?>;

    // Grace period: never show the warning within 10 seconds of page load
    // This prevents false-positive triggers on fresh logins / new devices
    const PAGE_LOAD_TIME = Date.now();
    const GRACE_PERIOD_MS = 10000; // 10 seconds

    // Ensure time remaining is bounded correctly
    timeRemaining = Math.max(0, Math.min(timeRemaining, sessionLifetime));

    const timerBadge = document.getElementById('sessionTimerBadge');
    const timerText = document.getElementById('sessionTimerText');
    const warningModal = document.getElementById('sessionWarningModal');
    const warningCountdown = document.getElementById('sessionCountdownWarning');
    
    const warningThreshold = sessionLifetime > 300 ? 120 : Math.floor(sessionLifetime / 3);
    let userWasActive = false;
    let activityInterval = null;
    let countdownInterval = null;

    // Format seconds to MM:SS or HH:MM:SS
    function formatTime(secs) {
        if (secs >= 3600) {
            const h = Math.floor(secs / 3600);
            const m = Math.floor((secs % 3600) / 60);
            const s = secs % 60;
            return `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        }
        const m = Math.floor(secs / 60);
        const s = secs % 60;
        return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }

    // Update Visual Timer Badge State
    function updateBadgeState(secs) {
        if (timerText) timerText.textContent = formatTime(secs);
        if (timerBadge) {
            if (secs <= 60) {
                timerBadge.className = 'session-timer-badge d-none danger';
            } else if (secs <= warningThreshold) {
                timerBadge.className = 'session-timer-badge d-none warning';
            } else {
                timerBadge.className = 'session-timer-badge d-none';
            }
        }
    }

    // Reset countdown and hide warning modal
    function resetTimer(newRemaining) {
        timeRemaining = newRemaining;
        updateBadgeState(timeRemaining);
        if (warningModal) warningModal.classList.remove('show');
    }

    // Extend Session via AJAX
    window.extendSession = function() {
        fetch('ajax-session-keepalive.php')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    resetTimer(data.remaining);
                } else {
                    window.location.href = 'logout.php?expired=1';
                }
            })
            .catch(() => {
                // If endpoint fails, fall back to locally extending
                resetTimer(sessionLifetime);
            });
    };

    // Logout session
    window.logoutSession = function() {
        window.location.href = 'logout.php?expired=1';
    };

    // Event handler to extend session when badge is clicked
    if (timerBadge) {
        timerBadge.addEventListener('click', extendSession);
    }

    // Detect user activity to auto-extend session
    const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
    function registerActivity() {
        userWasActive = true;
    }
    
    activityEvents.forEach(event => {
        document.addEventListener(event, registerActivity, { passive: true });
    });

    // Activity check loop (every 1 minute)
    activityInterval = setInterval(function() {
        // If user was active and we're not in the warning zone, extend session automatically
        if (userWasActive && timeRemaining > warningThreshold) {
            userWasActive = false;
            fetch('ajax-session-keepalive.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        resetTimer(data.remaining);
                    }
                });
        }
    }, 60000);

    // Main Countdown Timer loop (every 1 second)
    countdownInterval = setInterval(function() {
        if (timeRemaining > 0) {
            timeRemaining--;
            updateBadgeState(timeRemaining);

            // Handle warning modal presentation
            // Only show warning AFTER the grace period has passed (prevents false triggers on fresh logins)
            const elapsedSinceLoad = Date.now() - PAGE_LOAD_TIME;
            if (timeRemaining <= warningThreshold && elapsedSinceLoad >= GRACE_PERIOD_MS) {
                if (warningModal) warningModal.classList.add('show');
                if (warningCountdown) {
                    warningCountdown.textContent = timeRemaining;
                }
            } else {
                if (warningModal) warningModal.classList.remove('show');
            }
        } else {
            clearInterval(countdownInterval);
            clearInterval(activityInterval);
            logoutSession();
        }
    }, 1000);

    // Initial load
    updateBadgeState(timeRemaining);
})();
</script>
