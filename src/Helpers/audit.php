<?php
declare(strict_types=1);

function saveAudit(int $userId, string $action, string $targetType, int $targetId, array $details = []): void {
    try {
        $stmt = db()->prepare('INSERT INTO audit_logs (user_id, action, target_type, target_id, details, timestamp) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$userId, $action, $targetType, $targetId, json_encode($details, JSON_UNESCAPED_SLASHES)]);
    } catch (Throwable $e) {
        // Swallow to not block main flow
    }
}

function logAuditAction(int $userId, string $action, string $targetType, int $targetId, array $details = []): void {
    saveAudit($userId, $action, $targetType, $targetId, $details);
}
