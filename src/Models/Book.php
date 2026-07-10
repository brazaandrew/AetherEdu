<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/database.php';

class Book
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    /**
     * Get all books with optional filtering
     */
    public function getAllBooks(array $filters = []): array
    {
        $sql = "SELECT * FROM books WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY title ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get book by ID
     */
    public function getBookById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        return $book ?: null;
    }

    /**
     * Add new book
     */
    public function addBook(array $data): int
    {
        $sql = "INSERT INTO books (isbn, title, author, publisher, publication_year, category, 
                description, total_copies, available_copies, shelf_location, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['isbn'] ?? null,
            $data['title'],
            $data['author'],
            $data['publisher'] ?? null,
            $data['publication_year'] ?? null,
            $data['category'] ?? null,
            $data['description'] ?? null,
            $data['total_copies'] ?? 1,
            $data['available_copies'] ?? ($data['total_copies'] ?? 1),
            $data['shelf_location'] ?? null,
            $data['status'] ?? 'available'
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update book
     */
    public function updateBook(int $id, array $data): bool
    {
        $sql = "UPDATE books SET 
                isbn = ?, title = ?, author = ?, publisher = ?, publication_year = ?, 
                category = ?, description = ?, total_copies = ?, available_copies = ?, 
                shelf_location = ?, status = ? 
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['isbn'] ?? null,
            $data['title'],
            $data['author'],
            $data['publisher'] ?? null,
            $data['publication_year'] ?? null,
            $data['category'] ?? null,
            $data['description'] ?? null,
            $data['total_copies'] ?? 1,
            $data['available_copies'] ?? ($data['total_copies'] ?? 1),
            $data['shelf_location'] ?? null,
            $data['status'] ?? 'available',
            $id
        ]);
    }

    /**
     * Delete book
     */
    public function deleteBook(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM books WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Search books
     */
    public function searchBooks(string $query): array
    {
        $sql = "SELECT * FROM books 
                WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? OR category LIKE ?
                ORDER BY title ASC";

        $searchTerm = '%' . $query . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Update available copies
     */
    public function updateAvailableCopies(int $bookId, int $change): bool
    {
        $stmt = $this->db->prepare("UPDATE books SET available_copies = available_copies + ? WHERE id = ?");
        return $stmt->execute([$change, $bookId]);
    }

    /**
     * Get book statistics
     */
    public function getStatistics(): array
    {
        $stats = [];

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM books");
        $stats['total_books'] = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT SUM(total_copies) as total FROM books");
        $stats['total_copies'] = $stmt->fetchColumn() ?: 0;

        $stmt = $this->db->query("SELECT SUM(available_copies) as total FROM books");
        $stats['available_copies'] = $stmt->fetchColumn() ?: 0;

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM book_borrowings WHERE status = 'borrowed'");
        $stats['borrowed_copies'] = $stmt->fetchColumn();

        return $stats;
    }
}
