<?php
class NotificationService
{
    public static function send(int $userId, string $title, string $message, string $link = '#', string $icon = 'bi-bell-fill'): void
    {
        if ($userId <= 0) return;
        try {
            $stmt = db()->prepare("INSERT INTO notifications (user_id, title, message, link, icon, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$userId, $title, $message, $link, $icon]);
        } catch (Exception $e) {}
    }

    public static function sendToMany(array $userIds, string $title, string $message, string $link = '#', string $icon = 'bi-bell-fill'): void
    {
        if (empty($userIds)) return;
        try {
            $stmt = db()->prepare("INSERT INTO notifications (user_id, title, message, link, icon, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            foreach (array_unique($userIds) as $uid) {
                if ((int)$uid > 0) $stmt->execute([(int)$uid, $title, $message, $link, $icon]);
            }
        } catch (Exception $e) {}
    }

    public static function notifyNewActivity(int $activityId, string $activityTitle, int $subjectId, string $subjectName, ?string $deadline): void
    {
        $studentIds = self::getEnrolledStudentIds($subjectId);
        if (empty($studentIds)) return;
        $deadlineText = $deadline ? ' Due: ' . date('M d, Y', strtotime($deadline)) . '.' : '';
        self::sendToMany($studentIds, 'New Assignment: ' . $activityTitle, "A new assignment has been posted in {$subjectName}.{$deadlineText}", "submit-activity.php?id={$activityId}", 'bi-file-earmark-text-fill');
    }

    public static function notifyNewQuiz(int $quizId, string $quizTitle, int $subjectId, string $subjectName, int $timeLimitMinutes): void
    {
        $studentIds = self::getEnrolledStudentIds($subjectId);
        if (empty($studentIds)) return;
        $timeText = $timeLimitMinutes > 0 ? " Time limit: {$timeLimitMinutes} min." : '';
        self::sendToMany($studentIds, 'Quiz Available: ' . $quizTitle, "A new quiz is available in {$subjectName}.{$timeText}", "take-quiz.php?id={$quizId}", 'bi-card-checklist');
    }

    public static function notifyActivityGraded(int $studentId, string $activityTitle, int $score, int $maxScore, int $subjectId): void
    {
        $pct = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;
        self::send($studentId, 'Grade Released: ' . $activityTitle, "Your submission: {$score}/{$maxScore} ({$pct}%)", "student-subject.php?id={$subjectId}", 'bi-award-fill');
    }

    public static function notifyQuizGraded(int $studentId, string $quizTitle, int $score, int $maxScore, int $subjectId): void
    {
        $pct = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;
        self::send($studentId, 'Quiz Graded: ' . $quizTitle, "Your quiz score: {$score}/{$maxScore} ({$pct}%)", "student-subject.php?id={$subjectId}", 'bi-trophy-fill');
    }

    public static function broadcastAnnouncement(string $title, string $message, string $link = 'dashboard.php', ?string $role = null): void
    {
        try {
            $db = db();
            if ($role) { $stmt = $db->prepare("SELECT id FROM users WHERE role = ? AND archived = 0"); $stmt->execute([$role]); }
            else { $stmt = $db->query("SELECT id FROM users WHERE archived = 0"); }
            $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            self::sendToMany($userIds, $title, $message, $link, 'bi-megaphone-fill');
        } catch (Exception $e) {}
    }

    public static function checkDueDateReminders(int $userId): void
    {
        $key = "due_reminder_ts_{$userId}";
        if (isset($_SESSION[$key]) && (time() - $_SESSION[$key]) < 1800) return;
        $_SESSION[$key] = time();
        try {
            $db = db();
            $stmt = $db->prepare("SELECT a.id, a.title, a.deadline, s.name AS subject_name, a.subject_id FROM activities a JOIN subjects s ON a.subject_id = s.id JOIN enrollments e ON e.subject_id = a.subject_id AND e.student_id = ? LEFT JOIN activity_submissions asub ON asub.activity_id = a.id AND asub.student_id = ? WHERE a.deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR) AND asub.id IS NULL");
            $stmt->execute([$userId, $userId]);
            foreach ($stmt->fetchAll() as $item) {
                $link = "submit-activity.php?id={$item['id']}";
                $dup = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND link = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
                $dup->execute([$userId, $link]);
                if ($dup->fetchColumn() > 0) continue;
                self::send($userId, 'Due Soon: ' . $item['title'], 'Assignment due today at ' . date('h:i A', strtotime($item['deadline'])) . " in {$item['subject_name']}. Submit now!", $link, 'bi-alarm-fill');
            }
        } catch (Exception $e) {}
    }

    private static function getEnrolledStudentIds(int $subjectId): array
    {
        try {
            $stmt = db()->prepare("SELECT e.student_id FROM enrollments e JOIN users u ON u.id = e.student_id WHERE e.subject_id = ? AND u.archived = 0");
            $stmt->execute([$subjectId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) { return []; }
    }
}
