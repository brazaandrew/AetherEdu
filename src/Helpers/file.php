<?php
declare(strict_types=1);

function sanitizeFilename(string $name): string {
    return preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]/', '_', trim($name));
}

function ensureDir(string $path): void {
    if (!is_dir($path)) {
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Failed to create directory');
        }
    }
}
