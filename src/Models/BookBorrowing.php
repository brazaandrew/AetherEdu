<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/database.php';

class BookBorrowing
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Borrow a book
     */
    public function borrowBook(int $bookId, int $studentId, string $dueDate, ?string $notes = null): int
    {
        $sql = "INSERT INTO book_borrowings (book_id, student_id, due_date, notes) 
                VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bookId, $studentId, $dueDate, $notes]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Return a book
     */
    public function returnBook(int $borrowingId, ?string $notes = null): bool
    {
        $sql = "UPDATE book_borrowings 
                SET status = 'returned', returned_at = NOW(), notes = COALESCE(?, notes) 
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$notes, $borrowingId]);
    }

    /**
     * Get borrowing by ID
     */
    public function getBorrowingById(int $id): ?array
    {
        $sql = "SELECT bb.*, b.title as book_title, b.author as book_author, b.isbn,
                u.name as student_name, u.empidno as student_id_number
                FROM book_borrowings bb
                JOIN books b ON bb.book_id = b.id
                JOIN users u ON bb.student_id = u.id
                WHERE bb.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $borrowing = $stmt->fetch(PDO::FETCH_ASSOC);
        return $borrowing ?: null;
    }

    /**
     * Get student's current borrowings
     */
    public function getStudentBorrowings(int $studentId, string $status = 'borrowed'): array
    {
        $sql = "SELECT bb.*, b.title as book_title, b.author as book_author, b.isbn, b.shelf_location
                FROM book_borrowings bb
                JOIN books b ON bb.book_id = b.id
                WHERE bb.student_id = ? AND bb.status = ?
                ORDER BY bb.due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get student's borrowing history
     */
    public function getStudentBorrowingHistory(int $studentId): array
    {
        $sql = "SELECT bb.*, b.title as book_title, b.author as book_author, b.isbn
                FROM book_borrowings bb
                JOIN books b ON bb.book_id = b.id
                WHERE bb.student_id = ?
                ORDER BY bb.borrowed_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all borrowings with optional filtering
     */
    public function getAllBorrowings(array $filters = []): array
    {
        $sql = "SELECT bb.*, b.title as book_title, b.author as book_author, b.isbn,
                u.name as student_name, u.empidno as student_id_number
                FROM book_borrowings bb
                JOIN books b ON bb.book_id = b.id
                JOIN users u ON bb.student_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND bb.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['student_id'])) {
            $sql .= " AND bb.student_id = ?";
            $params[] = $filters['student_id'];
        }

        if (!empty($filters['book_id'])) {
            $sql .= " AND bb.book_id = ?";
            $params[] = $filters['book_id'];
        }

        $sql .= " ORDER BY bb.borrowed_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get overdue books
     */
    public function getOverdueBooks(): array
    {
        $sql = "SELECT bb.*, b.title as book_title, b.author as book_author, b.isbn,
                u.name as student_name, u.empidno as student_id_number, u.contact_number
                FROM book_borrowings bb
                JOIN books b ON bb.book_id = b.id
                JOIN users u ON bb.student_id = u.id
                WHERE bb.status = 'borrowed' AND bb.due_date < CURDATE()
                ORDER BY bb.due_date ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if student has borrowed a specific book
     */
    public function hasStudentBorrowedBook(int $studentId, int $bookId): bool
    {
        $sql = "SELECT COUNT(*) FROM book_borrowings 
                WHERE student_id = ? AND book_id = ? AND status = 'borrowed'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $bookId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get total borrowings count for a book
     */
    public function getBookBorrowingCount(int $bookId, string $status = 'borrowed'): int
    {
        $sql = "SELECT COUNT(*) FROM book_borrowings WHERE book_id = ? AND status = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bookId, $status]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Update overdue status
     */
    public function updateOverdueStatus(): int
    {
        $sql = "UPDATE book_borrowings 
                SET status = 'overdue' 
                WHERE status = 'borrowed' AND due_date < CURDATE()";

        $stmt = $this->db->query($sql);
        return $stmt->rowCount();
    }

    /**
     * Get borrowing statistics
     */
    public function getStatistics(): array
    {
        $stats = [];

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM book_borrowings WHERE status = 'borrowed'");
        $stats['currently_borrowed'] = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM book_borrowings WHERE status = 'returned'");
        $stats['total_returned'] = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM book_borrowings WHERE status = 'overdue'");
        $stats['overdue'] = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(DISTINCT student_id) as total FROM book_borrowings WHERE status = 'borrowed'");
        $stats['active_borrowers'] = $stmt->fetchColumn();

        return $stats;
    }
}
