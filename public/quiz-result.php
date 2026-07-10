<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireStudent();

if (!isset($_SESSION['quiz_result'])) {
    header('Location: my-subjects.php');
    exit;
}

$result = $_SESSION['quiz_result'];
unset($_SESSION['quiz_result']);

$score    = (float)$result['score'];
$maxScore = (float)$result['max_score'];
$percentage = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;

// Determine tier
if ($percentage >= 90) {
    $tier = 'gold';  $medal = '🥇'; $stars = 3; $xp = 100;
    $message = 'Outstanding! You nailed it!';
    $subMsg  = 'Top of the class performance!';
} elseif ($percentage >= 75) {
    $tier = 'silver'; $medal = '🥈'; $stars = 3; $xp = 75;
    $message = 'Great Job!';
    $subMsg  = 'You passed with flying colors!';
} elseif ($percentage >= 50) {
    $tier = 'bronze'; $medal = '🥉'; $stars = 2; $xp = 50;
    $message = 'Good Effort!';
    $subMsg  = 'Keep studying and you\'ll ace it next time!';
} else {
    $tier = 'fail'; $medal = '💪'; $stars = 1; $xp = 20;
    $message = 'Keep Practicing!';
    $subMsg  = 'Every attempt makes you stronger. Don\'t give up!';
}

$showConfetti = $percentage >= 75;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <link rel="stylesheet" href="assets/css/gamification.css?v=1">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Confetti Canvas -->
    <?php if ($showConfetti): ?>
    <canvas id="confettiCanvas"></canvas>
    <?php endif; ?>

    <div class="main-content">
        <?php $pageTitle = 'Quiz Result'; include __DIR__ . '/includes/topbar.php'; ?>

        <div class="container-fluid p-4">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-5">

                    <div class="result-card">
                        <!-- Colored header based on tier -->
                        <div class="result-card-header <?= $tier ?>">
                            <!-- Medal -->
                            <div class="grade-medal <?= $tier ?>"><?= $medal ?></div>

                            <!-- Stars -->
                            <div class="star-rating">
                                <span class="star<?= $stars >= 1 ? ' earned' : '' ?>">★</span>
                                <span class="star<?= $stars >= 2 ? ' earned' : '' ?>">★</span>
                                <span class="star<?= $stars >= 3 ? ' earned' : '' ?>">★</span>
                            </div>

                            <h2 class="fw-800 mb-1" style="font-size:1.5rem; font-weight:800;"><?= htmlspecialchars($message) ?></h2>
                            <p class="text-muted mb-0"><?= htmlspecialchars($subMsg) ?></p>
                        </div>

                        <!-- Score Body -->
                        <div class="card-body p-4 text-center">

                            <!-- Animated score -->
                            <div class="score-counter" id="scoreDisplay">
                                0 / <?= (int)$maxScore ?>
                            </div>

                            <!-- Percentage label -->
                            <p class="text-muted mb-2" style="font-size:1rem;">
                                <span id="percentDisplay">0</span>%
                            </p>

                            <!-- Progress bar -->
                            <div class="score-progress-track">
                                <div class="score-progress-fill <?= $tier ?>" id="progressFill"></div>
                            </div>

                            <!-- XP Earned -->
                            <div class="my-3">
                                <span class="xp-earned-flash">
                                    ⚡ +<?= $xp ?> XP Earned!
                                </span>
                            </div>

                            <!-- Manual grading note -->
                            <?php if ($result['needs_manual_grading']): ?>
                            <div class="alert alert-info text-start mt-3 mb-3" style="font-size:0.85rem;">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Note:</strong> Some questions require manual grading by your teacher. Your final score may change.
                            </div>
                            <?php endif; ?>

                            <!-- Back button -->
                            <a href="student-subject.php?id=<?= $result['subject_id'] ?>" class="btn btn-primary btn-lg w-100 mt-2">
                                <i class="bi bi-arrow-left me-2"></i>Back to Subject
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Animate score counter
    const finalScore  = <?= (int)$score ?>;
    const maxScore    = <?= (int)$maxScore ?>;
    const finalPct    = <?= round($percentage, 1) ?>;
    const scoreDom    = document.getElementById('scoreDisplay');
    const pctDom      = document.getElementById('percentDisplay');
    const fillDom     = document.getElementById('progressFill');

    let current = 0;
    const duration = 1200;
    const stepTime = 16;
    const steps    = duration / stepTime;
    let step = 0;

    const timer = setInterval(() => {
        step++;
        const progress = step / steps;
        const eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        current = Math.round(eased * finalScore);
        const pct = Math.round(eased * finalPct * 10) / 10;

        scoreDom.textContent = current + ' / ' + maxScore;
        pctDom.textContent   = pct.toFixed(1);

        if (step >= steps) {
            clearInterval(timer);
            scoreDom.textContent = finalScore + ' / ' + maxScore;
            pctDom.textContent   = finalPct.toFixed(1);
        }
    }, stepTime);

    // Animate progress bar (slight delay for visual effect)
    setTimeout(() => {
        fillDom.style.width = Math.min(finalPct, 100) + '%';
    }, 300);

    <?php if ($showConfetti): ?>
    // Confetti burst
    (function() {
        const canvas = document.getElementById('confettiCanvas');
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
        const ctx = canvas.getContext('2d');

        const colors = ['#f59e0b','#10b981','#2563eb','#7c3aed','#ec4899','#ef4444','#fbbf24'];
        const pieces = Array.from({length: 120}, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * -canvas.height * 0.5 - 20,
            w: Math.random() * 10 + 5,
            h: Math.random() * 6 + 3,
            color: colors[Math.floor(Math.random() * colors.length)],
            vx: (Math.random() - 0.5) * 4,
            vy: Math.random() * 4 + 2,
            rot: Math.random() * 360,
            rotV: (Math.random() - 0.5) * 8,
            alpha: 1
        }));

        let frame = 0;
        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            pieces.forEach(p => {
                p.x   += p.vx;
                p.y   += p.vy;
                p.rot += p.rotV;
                if (p.y > canvas.height * 0.6) p.alpha -= 0.015;

                ctx.save();
                ctx.globalAlpha = Math.max(0, p.alpha);
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rot * Math.PI / 180);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h);
                ctx.restore();
            });
            frame++;
            if (frame < 200) requestAnimationFrame(draw);
            else ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        setTimeout(draw, 600);
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    })();
    <?php endif; ?>
    </script>
</body>
</html>
