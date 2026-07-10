<?php
declare(strict_types=1);

/**
 * Build display name from users row without duplicating middle_name
 * when enrollment already stored it inside `name`.
 */
function studentFullName(array $student): string
{
    $name = trim((string) ($student['name'] ?? ''));
    $middle = trim((string) ($student['middle_name'] ?? ''));

    if ($middle === '') {
        return $name;
    }

    if (preg_match('/\b' . preg_quote($middle, '/') . '\s*$/iu', $name)) {
        return $name;
    }

    return $name . ' ' . $middle;
}

function studentNormalizeName(string $name): string
{
    $normalized = preg_replace('/\s+/u', ' ', trim($name)) ?? '';

    return mb_strtolower($normalized, 'UTF-8');
}

/**
 * Find an active student that would duplicate a new enrollment.
 *
 * @return array{reason: string, student: array}|null
 */
function studentFindEnrollmentDuplicate(
    string $fullName,
    string $email,
    string $lrnNumber = '',
    ?int $excludeId = null
): ?array {
    $email = trim($email);
    $lrnNumber = trim($lrnNumber);
    $normName = studentNormalizeName($fullName);

    $find = static function (string $sql, array $params) use ($excludeId): ?array {
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row ?: null;
    };

    if ($email !== '') {
        $row = $find(
            "SELECT id, empidno, email, name FROM users
             WHERE role = 'student' AND archived = 0 AND LOWER(TRIM(email)) = LOWER(?)",
            [$email]
        );
        if ($row) {
            return ['reason' => 'email', 'student' => $row];
        }
    }

    if ($lrnNumber !== '') {
        $row = $find(
            "SELECT id, empidno, email, name FROM users
             WHERE role = 'student' AND archived = 0 AND TRIM(lrn_number) = ?",
            [$lrnNumber]
        );
        if ($row) {
            return ['reason' => 'lrn', 'student' => $row];
        }
    }

    if ($normName !== '') {
        $row = $find(
            "SELECT id, empidno, email, name FROM users
             WHERE role = 'student' AND archived = 0 AND LOWER(TRIM(name)) = ?",
            [$normName]
        );
        if ($row) {
            return ['reason' => 'name', 'student' => $row];
        }
    }

    return null;
}

function studentEnrollmentDuplicateMessage(array $duplicate): string
{
    $s = $duplicate['student'];
    $id = (string) $s['empidno'];
    $name = (string) $s['name'];
    $email = (string) $s['email'];

    return match ($duplicate['reason']) {
        'email' => "This email is already registered to {$name} ({$id}).",
        'lrn' => "This LRN is already registered to {$name} ({$id}).",
        'name' => "A student with the same name is already enrolled: {$name} ({$id}, {$email}). Check the student list before enrolling again.",
        default => 'This student appears to already be enrolled.',
    };
}

/** @return list<string> Normalized names that appear more than once among active students */
function studentDuplicateNormalizedNames(): array
{
    $stmt = db()->query(
        "SELECT LOWER(TRIM(name)) AS norm_name
         FROM users
         WHERE role = 'student' AND archived = 0 AND TRIM(name) != ''
         GROUP BY LOWER(TRIM(name))
         HAVING COUNT(*) > 1"
    );

    return array_column($stmt->fetchAll(), 'norm_name');
}