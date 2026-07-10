<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Book.php';
require_once __DIR__ . '/../Models/BookBorrowing.php';

class LibraryController
{
    private Book $bookModel;
    private BookBorrowing $borrowingModel;

    public function __construct()
    {
        $this->bookModel = new Book();
        $this->borrowingModel = new BookBorrowing();
    }

    /**
     * Get all books with filters
     */
    public function getBooks(array $filters = []): array
    {
        return $this->bookModel->getAllBooks($filters);
    }

    /**
     * Get single book
     */
    public function getBook(int $id): ?array
    {
        return $this->bookModel->getBookById($id);
    }

    /**
     * Add new book
     */
    public function addBook(array $data): array
    {
        try {
            $bookId = $this->bookModel->addBook($data);
            return ['success' => true, 'book_id' => $bookId, 'message' => 'Book added successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update book
     */
    public function updateBook(int $id, array $data): array
    {
        try {
            $success = $this->bookModel->updateBook($id, $data);
            if ($success) {
                return ['success' => true, 'message' => 'Book updated successfully'];
            }
            return ['success' => false, 'error' => 'Book not found'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete book
     */
    public function deleteBook(int $id): array
    {
        try {
            $success = $this->bookModel->deleteBook($id);
            if ($success) {
                return ['success' => true, 'message' => 'Book deleted successfully'];
            }
            return ['success' => false, 'error' => 'Book not found'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Search books
     */
    public function searchBooks(string $query): array
    {
        return $this->bookModel->searchBooks($query);
    }

    /**
     * Get book categories
     */
    public function getCategories(): array
    {
        return $this->bookModel->getCategories();
    }

    /**
     * Borrow a book
     */
    public function borrowBook(int $bookId, int $studentId, string $dueDate, ?string $notes = null): array
    {
        try {
            // Check if book exists and is available
            $book = $this->bookModel->getBookById($bookId);
            if (!$book) {
                return ['success' => false, 'error' => 'Book not found'];
            }

            if ($book['available_copies'] <= 0) {
                return ['success' => false, 'error' => 'Book is not available for borrowing'];
            }

            // Check if student already borrowed this book
            if ($this->borrowingModel->hasStudentBorrowedBook($studentId, $bookId)) {
                return ['success' => false, 'error' => 'Student has already borrowed this book'];
            }

            // Create borrowing record
            $borrowingId = $this->borrowingModel->borrowBook($bookId, $studentId, $dueDate, $notes);

            // Update available copies
            $this->bookModel->updateAvailableCopies($bookId, -1);

            return ['success' => true, 'borrowing_id' => $borrowingId, 'message' => 'Book borrowed successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Return a book
     */
    public function returnBook(int $borrowingId, ?string $notes = null): array
    {
        try {
            // Get borrowing details
            $borrowing = $this->borrowingModel->getBorrowingById($borrowingId);
            if (!$borrowing) {
                return ['success' => false, 'error' => 'Borrowing record not found'];
            }

            if ($borrowing['status'] === 'returned') {
                return ['success' => false, 'error' => 'Book has already been returned'];
            }

            // Update borrowing record
            $this->borrowingModel->returnBook($borrowingId, $notes);

            // Update available copies
            $this->bookModel->updateAvailableCopies($borrowing['book_id'], 1);

            return ['success' => true, 'message' => 'Book returned successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get student borrowings
     */
    public function getStudentBorrowings(int $studentId, string $status = 'borrowed'): array
    {
        return $this->borrowingModel->getStudentBorrowings($studentId, $status);
    }

    /**
     * Get student borrowing history
     */
    public function getStudentBorrowingHistory(int $studentId): array
    {
        return $this->borrowingModel->getStudentBorrowingHistory($studentId);
    }

    /**
     * Get all borrowings
     */
    public function getAllBorrowings(array $filters = []): array
    {
        return $this->borrowingModel->getAllBorrowings($filters);
    }

    /**
     * Get overdue books
     */
    public function getOverdueBooks(): array
    {
        // First update overdue status
        $this->borrowingModel->updateOverdueStatus();
        return $this->borrowingModel->getOverdueBooks();
    }

    /**
     * Get library statistics
     */
    public function getStatistics(): array
    {
        $bookStats = $this->bookModel->getStatistics();
        $borrowingStats = $this->borrowingModel->getStatistics();

        return array_merge($bookStats, $borrowingStats);
    }
}
