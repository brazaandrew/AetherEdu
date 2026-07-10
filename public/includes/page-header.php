<!-- Shared Page Header Component -->
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h2>
            <i class="bi bi-<?= $icon ?? 'speedometer2' ?>"></i>
            <?= $pageTitle ?? 'Page Title' ?>
        </h2>
        <div class="d-flex gap-2 flex-wrap">
            <?php if (isset($actions)): ?>
                <?= $actions ?>
            <?php endif; ?>
            <a href="<?= $backUrl ?? 'dashboard.php' ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i> <?= $backText ?? 'Back' ?>
            </a>
        </div>
    </div>
</div>
