<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

// Base Model
class BaseModel {
    protected $db;
    protected $table;

    public function __construct($table = null) {
        $this->db = Database::getInstance()->getConnection();
        if ($table) {
            $this->table = $table;
        }
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll($orderBy = 'id DESC') {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

// User Model
class User extends BaseModel {
    public function __construct() {
        parent::__construct('users');
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function create($email, $password, $role = 'member', $status = 'active') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $hashed, $role, $status]);
        return $this->db->lastInsertId();
    }

    public function updatePassword($id, $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashed, $id]);
    }

    public function createResetToken($email, $token, $expiry) {
        $stmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        return $stmt->execute([$token, $expiry, $email]);
    }

    public function findByResetToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function clearResetToken($id) {
        $stmt = $this->db->prepare("UPDATE users SET reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}

// Member Model
class Member {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT m.*, u.email, u.role FROM members m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM members WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function create($userId, $fullName, $phone, $address, $registrationDate, $status = 'active') {
        $stmt = $this->db->prepare("INSERT INTO members (user_id, full_name, phone, address, registration_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $fullName, $phone, $address, $registrationDate, $status]);
        return $this->db->lastInsertId();
    }

    public function update($id, $fullName, $phone, $address, $status = null) {
        if ($status !== null) {
            $stmt = $this->db->prepare("UPDATE members SET full_name = ?, phone = ?, address = ?, status = ? WHERE id = ?");
            $success = $stmt->execute([$fullName, $phone, $address, $status, $id]);
            
            $member = $this->findById($id);
            if ($member) {
                $stmtUser = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmtUser->execute([$status, $member['user_id']]);
            }
            return $success;
        } else {
            $stmt = $this->db->prepare("UPDATE members SET full_name = ?, phone = ?, address = ? WHERE id = ?");
            return $stmt->execute([$fullName, $phone, $address, $id]);
        }
    }

    public function getAll($search = '', $limit = null, $offset = null) {
        $sql = "SELECT m.*, u.email FROM members m JOIN users u ON m.user_id = u.id";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE m.full_name LIKE ? OR u.email LIKE ? OR m.phone LIKE ?";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $sql .= " ORDER BY m.id DESC";
        
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll($search = '') {
        $sql = "SELECT COUNT(*) FROM members m JOIN users u ON m.user_id = u.id";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE m.full_name LIKE ? OR u.email LIKE ? OR m.phone LIKE ?";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function delete($id) {
        $member = $this->findById($id);
        if ($member) {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$member['user_id']]);
        }
        return false;
    }
}

// Book Model
class Book {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($filters = [], $limit = null, $offset = null, $sort = 'b.id', $order = 'DESC') {
        $sql = "SELECT b.*, a.name AS author_name, c.name AS category_name 
                FROM books b 
                LEFT JOIN authors a ON b.author_id = a.id 
                LEFT JOIN categories c ON b.category_id = c.id";
        
        $whereClauses = [];
        $params = [];

        if (!empty($filters['search'])) {
            $search = "%" . $filters['search'] . "%";
            $whereClauses[] = "(b.title LIKE ? OR b.isbn LIKE ? OR b.publisher LIKE ? OR b.description LIKE ? OR a.name LIKE ? OR c.name LIKE ?)";
            array_push($params, $search, $search, $search, $search, $search, $search);
        }

        if (!empty($filters['author_id'])) {
            $whereClauses[] = "b.author_id = ?";
            $params[] = $filters['author_id'];
        }

        if (!empty($filters['category_id'])) {
            $whereClauses[] = "b.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (isset($filters['availability'])) {
            if ($filters['availability'] === 'available') {
                $whereClauses[] = "b.available_copies > 0";
            } elseif ($filters['availability'] === 'unavailable') {
                $whereClauses[] = "b.available_copies = 0";
            }
        }

        if (!empty($filters['publication_year'])) {
            $whereClauses[] = "b.publication_year = ?";
            $params[] = $filters['publication_year'];
        }

        if (count($whereClauses) > 0) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $allowedSort = ['b.id', 'b.title', 'b.publication_year', 'b.available_copies', 'a.name', 'c.name'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'b.id';
        }
        $order = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';
        
        $sql .= " ORDER BY {$sort} {$order}";

        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll($filters = []) {
        $sql = "SELECT COUNT(b.id) 
                FROM books b 
                LEFT JOIN authors a ON b.author_id = a.id 
                LEFT JOIN categories c ON b.category_id = c.id";
        
        $whereClauses = [];
        $params = [];

        if (!empty($filters['search'])) {
            $search = "%" . $filters['search'] . "%";
            $whereClauses[] = "(b.title LIKE ? OR b.isbn LIKE ? OR b.publisher LIKE ? OR b.description LIKE ? OR a.name LIKE ? OR c.name LIKE ?)";
            array_push($params, $search, $search, $search, $search, $search, $search);
        }

        if (!empty($filters['author_id'])) {
            $whereClauses[] = "b.author_id = ?";
            $params[] = $filters['author_id'];
        }

        if (!empty($filters['category_id'])) {
            $whereClauses[] = "b.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (isset($filters['availability'])) {
            if ($filters['availability'] === 'available') {
                $whereClauses[] = "b.available_copies > 0";
            } elseif ($filters['availability'] === 'unavailable') {
                $whereClauses[] = "b.available_copies = 0";
            }
        }

        if (!empty($filters['publication_year'])) {
            $whereClauses[] = "b.publication_year = ?";
            $params[] = $filters['publication_year'];
        }

        if (count($whereClauses) > 0) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT b.*, a.name AS author_name, a.nationality AS author_nationality, a.biography AS author_biography, c.name AS category_name 
                                     FROM books b 
                                     LEFT JOIN authors a ON b.author_id = a.id 
                                     LEFT JOIN categories c ON b.category_id = c.id 
                                     WHERE b.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByIsbn($isbn) {
        $stmt = $this->db->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->execute([$isbn]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO books (isbn, title, author_id, category_id, description, publisher, publication_year, quantity, available_copies, shelf_location, cover_image) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['isbn'] ?? null,
            $data['title'],
            $data['author_id'] ?? null,
            $data['category_id'] ?? null,
            $data['description'] ?? null,
            $data['publisher'] ?? null,
            $data['publication_year'] ?? null,
            $data['quantity'],
            $data['quantity'],
            $data['shelf_location'] ?? null,
            $data['cover_image'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $book = $this->findById($id);
        if (!$book) return false;

        $qtyDiff = intval($data['quantity']) - intval($book['quantity']);
        $newAvailable = max(0, intval($book['available_copies']) + $qtyDiff);

        $stmt = $this->db->prepare("UPDATE books 
                                    SET isbn = ?, title = ?, author_id = ?, category_id = ?, description = ?, publisher = ?, publication_year = ?, quantity = ?, available_copies = ?, shelf_location = ?, cover_image = ? 
                                    WHERE id = ?");
        return $stmt->execute([
            $data['isbn'] ?? null,
            $data['title'],
            $data['author_id'] ?? null,
            $data['category_id'] ?? null,
            $data['description'] ?? null,
            $data['publisher'] ?? null,
            $data['publication_year'] ?? null,
            $data['quantity'],
            $newAvailable,
            $data['shelf_location'] ?? null,
            $data['cover_image'] ?? $book['cover_image'],
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM books WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getTotalCopies() {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM books");
        $stmt->execute();
        return intval($stmt->fetchColumn());
    }

    public function getTotalAvailableCopies() {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(available_copies), 0) FROM books");
        $stmt->execute();
        return intval($stmt->fetchColumn());
    }

    public function decrementAvailable($id) {
        $stmt = $this->db->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ? AND available_copies > 0");
        return $stmt->execute([$id]);
    }

    public function incrementAvailable($id) {
        $stmt = $this->db->prepare("UPDATE books SET available_copies = LEAST(available_copies + 1, quantity) WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getFeatured($limit = 4) {
        $stmt = $this->db->prepare("SELECT b.*, a.name AS author_name, c.name AS category_name 
                                    FROM books b 
                                    LEFT JOIN authors a ON b.author_id = a.id 
                                    LEFT JOIN categories c ON b.category_id = c.id 
                                    ORDER BY RAND() LIMIT ?");
        $stmt->bindValue(1, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecentlyAdded($limit = 4) {
        $stmt = $this->db->prepare("SELECT b.*, a.name AS author_name, c.name AS category_name 
                                    FROM books b 
                                    LEFT JOIN authors a ON b.author_id = a.id 
                                    LEFT JOIN categories c ON b.category_id = c.id 
                                    ORDER BY b.id DESC LIMIT ?");
        $stmt->bindValue(1, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRecommendations($categoryId, $excludeBookId, $limit = 4) {
        $stmt = $this->db->prepare("SELECT b.*, a.name AS author_name, c.name AS category_name 
                                    FROM books b 
                                    LEFT JOIN authors a ON b.author_id = a.id 
                                    LEFT JOIN categories c ON b.category_id = c.id 
                                    WHERE b.category_id = ? AND b.id != ? 
                                    ORDER BY RAND() LIMIT ?");
        $stmt->bindValue(1, intval($categoryId), PDO::PARAM_INT);
        $stmt->bindValue(2, intval($excludeBookId), PDO::PARAM_INT);
        $stmt->bindValue(3, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

// Author Model
class Author extends BaseModel {
    public function __construct() {
        parent::__construct('authors');
    }

    public function getAll($orderBy = 'id DESC') {
        $stmt = $this->db->prepare("SELECT a.*, COUNT(b.id) AS book_count FROM authors a LEFT JOIN books b ON a.id = b.author_id GROUP BY a.id ORDER BY a.name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM authors WHERE name = ? LIMIT 1");
        $stmt->execute([trim($name)]);
        return $stmt->fetch();
    }

    public function create($name, $biography, $nationality) {
        $stmt = $this->db->prepare("INSERT INTO authors (name, biography, nationality) VALUES (?, ?, ?)");
        $stmt->execute([$name, $biography, $nationality]);
        return $this->db->lastInsertId();
    }

    public function update($id, $name, $biography, $nationality) {
        $stmt = $this->db->prepare("UPDATE authors SET name = ?, biography = ?, nationality = ? WHERE id = ?");
        return $stmt->execute([$name, $biography, $nationality, $id]);
    }
}

// Category Model
class Category extends BaseModel {
    public function __construct() {
        parent::__construct('categories');
    }

    public function getAll($orderBy = 'id DESC') {
        $stmt = $this->db->prepare("SELECT c.*, COUNT(b.id) AS book_count FROM categories c LEFT JOIN books b ON c.id = b.category_id GROUP BY c.id ORDER BY c.name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE name = ? LIMIT 1");
        $stmt->execute([trim($name)]);
        return $stmt->fetch();
    }

    public function create($name) {
        $stmt = $this->db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        return $this->db->lastInsertId();
    }

    public function update($id, $name) {
        $stmt = $this->db->prepare("UPDATE categories SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }
}

// Borrow Request Model
class BorrowRequest {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($memberId, $bookId) {
        $stmt = $this->db->prepare("INSERT INTO borrow_requests (member_id, book_id, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$memberId, $bookId]);
        return $this->db->lastInsertId();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT br.*, m.full_name AS member_name, u.email AS member_email, b.title AS book_title, b.available_copies, b.cover_image, b.quantity
                                    FROM borrow_requests br
                                    JOIN members m ON br.member_id = m.id
                                    JOIN users u ON m.user_id = u.id
                                    JOIN books b ON br.book_id = b.id
                                    WHERE br.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatus($id, $status, $adminNotes = null) {
        $stmt = $this->db->prepare("UPDATE borrow_requests SET status = ?, admin_notes = ? WHERE id = ?");
        return $stmt->execute([$status, $adminNotes, $id]);
    }

    public function getPendingRequests() {
        $stmt = $this->db->prepare("SELECT br.*, m.full_name AS member_name, b.title AS book_title, b.isbn, b.cover_image, b.available_copies
                                    FROM borrow_requests br
                                    JOIN members m ON br.member_id = m.id
                                    JOIN books b ON br.book_id = b.id
                                    WHERE br.status = 'pending'
                                    ORDER BY br.request_date ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllRequests() {
        $stmt = $this->db->prepare("SELECT br.*, m.full_name AS member_name, b.title AS book_title, b.isbn, b.cover_image
                                    FROM borrow_requests br
                                    JOIN members m ON br.member_id = m.id
                                    JOIN books b ON br.book_id = b.id
                                    ORDER BY br.id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getHistoryByMember($memberId) {
        $stmt = $this->db->prepare("SELECT br.*, b.title AS book_title, b.isbn, b.cover_image
                                    FROM borrow_requests br
                                    JOIN books b ON br.book_id = b.id
                                    WHERE br.member_id = ?
                                    ORDER BY br.id DESC");
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }

    public function hasActiveRequestOrBorrow($memberId, $bookId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM borrow_requests WHERE member_id = ? AND book_id = ? AND status = 'pending'");
        $stmt->execute([$memberId, $bookId]);
        $pending = $stmt->fetchColumn();

        $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM borrowings WHERE member_id = ? AND book_id = ? AND status = 'borrowed'");
        $stmt2->execute([$memberId, $bookId]);
        $borrowed = $stmt2->fetchColumn();

        return ($pending > 0 || $borrowed > 0);
    }
}

// Borrowing Model
class Borrowing {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($requestId, $memberId, $bookId, $borrowDate, $dueDate) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("INSERT INTO borrowings (borrow_request_id, member_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, ?, 'borrowed')");
            $stmt->execute([$requestId, $memberId, $bookId, $borrowDate, $dueDate]);
            $borrowingId = $this->db->lastInsertId();

            $stmtBook = $this->db->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ? AND available_copies > 0");
            $stmtBook->execute([$bookId]);

            if ($stmtBook->rowCount() === 0) {
                throw new Exception("Book out of stock.");
            }
            $this->db->commit();
            return $borrowingId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT bw.*, m.full_name AS member_name, u.email AS member_email, b.title AS book_title, b.isbn, b.cover_image
                                    FROM borrowings bw
                                    JOIN members m ON bw.member_id = m.id
                                    JOIN users u ON m.user_id = u.id
                                    JOIN books b ON bw.book_id = b.id
                                    WHERE bw.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function markAsReturned($id, $returnDate, $fineAmount = 0.00, $status = 'returned') {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("SELECT * FROM borrowings WHERE id = ? AND status = 'borrowed'");
            $stmt->execute([$id]);
            $borrowing = $stmt->fetch();

            if (!$borrowing) {
                throw new Exception("Borrowing record not found.");
            }

            $stmtUpdate = $this->db->prepare("UPDATE borrowings SET return_date = ?, status = 'returned' WHERE id = ?");
            $stmtUpdate->execute([$returnDate, $id]);

            $paymentStatus = ($fineAmount > 0) ? 'unpaid' : 'waived';

            $stmtReturn = $this->db->prepare("INSERT INTO returns (borrowing_id, return_date, fine_amount, status, payment_status) VALUES (?, ?, ?, ?, ?)");
            $stmtReturn->execute([$id, $returnDate, $fineAmount, $status, $paymentStatus]);

            $stmtBook = $this->db->prepare("UPDATE books SET available_copies = LEAST(available_copies + 1, quantity) WHERE id = ?");
            $stmtBook->execute([$borrowing['book_id']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getAllBorrowings() {
        $stmt = $this->db->prepare("SELECT bw.*, m.full_name AS member_name, b.title AS book_title, b.isbn, r.fine_amount, r.status AS return_condition
                                    FROM borrowings bw
                                    JOIN members m ON bw.member_id = m.id
                                    JOIN books b ON bw.book_id = b.id
                                    LEFT JOIN returns r ON r.borrowing_id = bw.id
                                    ORDER BY bw.id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getHistoryByMember($memberId) {
        $stmt = $this->db->prepare("SELECT bw.*, b.title AS book_title, b.isbn, b.cover_image, r.id AS return_id, r.fine_amount, r.payment_status, r.paid_at, r.status AS return_condition
                                    FROM borrowings bw
                                    JOIN books b ON bw.book_id = b.id
                                    LEFT JOIN returns r ON r.borrowing_id = bw.id
                                    WHERE bw.member_id = ?
                                    ORDER BY bw.id DESC");
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }

    public function updateDueDate($id, $newDueDate) {
        $stmt = $this->db->prepare("UPDATE borrowings SET due_date = ? WHERE id = ? AND status = 'borrowed'");
        $stmt->execute([$newDueDate, $id]);
        if ($stmt->rowCount() === 0) {
            throw new Exception("No active borrowing found to update.");
        }
        return true;
    }

    public function getActiveBorrowings() {
        $stmt = $this->db->prepare("SELECT bw.*, m.full_name AS member_name, b.title AS book_title, b.isbn
                                    FROM borrowings bw
                                    JOIN members m ON bw.member_id = m.id
                                    JOIN books b ON bw.book_id = b.id
                                    WHERE bw.status = 'borrowed'
                                    ORDER BY bw.due_date ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOverdueBorrowings() {
        $stmt = $this->db->prepare("SELECT bw.*, m.full_name AS member_name, b.title AS book_title, b.isbn
                                    FROM borrowings bw
                                    JOIN members m ON bw.member_id = m.id
                                    JOIN books b ON bw.book_id = b.id
                                    WHERE bw.status = 'borrowed' AND bw.due_date < CURDATE()
                                    ORDER BY bw.due_date ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMostBorrowedBooks($limit = 5) {
        $stmt = $this->db->prepare("SELECT b.title, b.cover_image, c.name AS category_name, COUNT(bw.id) AS borrow_count
                                    FROM borrowings bw
                                    JOIN books b ON bw.book_id = b.id
                                    LEFT JOIN categories c ON b.category_id = c.id
                                    GROUP BY b.id
                                    ORDER BY borrow_count DESC
                                    LIMIT ?");
        $stmt->bindValue(1, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActiveMembers($limit = 5) {
        $stmt = $this->db->prepare("SELECT m.full_name, u.email, COUNT(bw.id) AS borrow_count
                                    FROM borrowings bw
                                    JOIN members m ON bw.member_id = m.id
                                    JOIN users u ON m.user_id = u.id
                                    GROUP BY m.id
                                    ORDER BY borrow_count DESC
                                    LIMIT ?");
        $stmt->bindValue(1, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMonthlyBorrowingStats() {
   
        $stmt = $this->db->prepare("SELECT DATE_FORMAT(borrow_date, '%Y-%m') AS ym,
                                            MONTHNAME(borrow_date) AS month,
                                            COUNT(id) AS count 
                                    FROM borrowings 
                                    WHERE borrow_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                                    GROUP BY DATE_FORMAT(borrow_date, '%Y-%m'), MONTHNAME(borrow_date)
                                    ORDER BY ym ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}


// 9. Fine Model 
class Fine {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getUnpaidByMember($memberId) {
        $stmt = $this->db->prepare("SELECT r.*, bw.borrow_date, bw.due_date, bw.return_date AS book_return_date,
                                           b.title AS book_title, b.isbn, b.cover_image
                                    FROM returns r
                                    JOIN borrowings bw ON r.borrowing_id = bw.id
                                    JOIN books b ON bw.book_id = b.id
                                    WHERE bw.member_id = ? AND r.fine_amount > 0 AND r.payment_status = 'unpaid'
                                    ORDER BY r.return_date DESC");
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }

    public function getPaidByMember($memberId) {
        $stmt = $this->db->prepare("SELECT r.*, bw.borrow_date, bw.due_date, bw.return_date AS book_return_date,
                                           b.title AS book_title, b.isbn, b.cover_image
                                    FROM returns r
                                    JOIN borrowings bw ON r.borrowing_id = bw.id
                                    JOIN books b ON bw.book_id = b.id
                                    WHERE bw.member_id = ? AND r.fine_amount > 0 AND r.payment_status = 'paid'
                                    ORDER BY r.paid_at DESC");
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }

    public function findByReturnId($returnId, $memberId = null) {
        $sql = "SELECT r.*, bw.borrow_date, bw.due_date, bw.member_id, bw.return_date AS book_return_date,
                       b.title AS book_title, b.isbn, b.cover_image
                FROM returns r
                JOIN borrowings bw ON r.borrowing_id = bw.id
                JOIN books b ON bw.book_id = b.id
                WHERE r.id = ?";
        $params = [$returnId];

        if ($memberId !== null) {
            $sql .= " AND bw.member_id = ?";
            $params[] = $memberId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function markAsPaid($returnId, $paymentIntentId) {
        $stmt = $this->db->prepare("UPDATE returns SET payment_status = 'paid', paid_at = NOW(), stripe_payment_intent_id = ? WHERE id = ? AND payment_status = 'unpaid'");
        return $stmt->execute([$paymentIntentId, $returnId]);
    }

    public function getUnpaidTotalByMember($memberId) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(r.fine_amount), 0) AS total
                                    FROM returns r
                                    JOIN borrowings bw ON r.borrowing_id = bw.id
                                    WHERE bw.member_id = ? AND r.fine_amount > 0 AND r.payment_status = 'unpaid'");
        $stmt->execute([$memberId]);
        return floatval($stmt->fetchColumn());
    }

    public function getPaidTotalByMember($memberId) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(r.fine_amount), 0) AS total
                                    FROM returns r
                                    JOIN borrowings bw ON r.borrowing_id = bw.id
                                    WHERE bw.member_id = ? AND r.fine_amount > 0 AND r.payment_status = 'paid'");
        $stmt->execute([$memberId]);
        return floatval($stmt->fetchColumn());
    }
}


// 9b. OverdueFineCharge Model 

class OverdueFineCharge {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get or create a charge for today on an active overdue borrowing.
     * Fine = days_overdue * 100 EGP. Updates daily if already exists.
     */
    public function chargeForBorrowing($borrowingId, $memberId) {
        $today = date('Y-m-d');

        // Recalculate days overdue and fine from borrowings table
        $stmt = $this->db->prepare("SELECT due_date FROM borrowings WHERE id = ? AND member_id = ? AND status = 'borrowed'");
        $stmt->execute([$borrowingId, $memberId]);
        $borrow = $stmt->fetch();

        if (!$borrow) {
            throw new Exception("Active borrowing not found.");
        }

        $dueTime  = strtotime($borrow['due_date']);
        $todayTime = strtotime($today);

        if ($todayTime <= $dueTime) {
            throw new Exception("This book is not overdue yet.");
        }

        $daysOverdue = ceil(($todayTime - $dueTime) / 86400);
        $fineAmount  = $daysOverdue * FINE_RATE_PER_DAY;

        // Check if a charge record exists for this borrowing
        $stmtCheck = $this->db->prepare("SELECT id FROM overdue_fine_charges WHERE borrowing_id = ? AND payment_status = 'unpaid'");
        $stmtCheck->execute([$borrowingId]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            // Update the existing unpaid charge with the latest amount
            $stmtUp = $this->db->prepare("UPDATE overdue_fine_charges SET days_overdue = ?, fine_amount = ?, charged_at = ? WHERE id = ?");
            $stmtUp->execute([$daysOverdue, $fineAmount, $today, $existing['id']]);
            return $existing['id'];
        } else {
            // Create a new charge record
            $stmtIns = $this->db->prepare("INSERT INTO overdue_fine_charges (borrowing_id, member_id, days_overdue, fine_amount, charged_at) VALUES (?, ?, ?, ?, ?)");
            $stmtIns->execute([$borrowingId, $memberId, $daysOverdue, $fineAmount, $today]);
            return $this->db->lastInsertId();
        }
    }

    public function findById($id, $memberId = null) {
        $sql = "SELECT ofc.*, bw.borrow_date, bw.due_date, bw.book_id,
                       b.title AS book_title, b.isbn, b.cover_image
                FROM overdue_fine_charges ofc
                JOIN borrowings bw ON ofc.borrowing_id = bw.id
                JOIN books b ON bw.book_id = b.id
                WHERE ofc.id = ?";
        $params = [$id];
        if ($memberId !== null) {
            $sql .= " AND ofc.member_id = ?";
            $params[] = $memberId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getUnpaidByMember($memberId) {
        $stmt = $this->db->prepare(
            "SELECT ofc.*, bw.borrow_date, bw.due_date,
                    b.title AS book_title, b.isbn, b.cover_image
             FROM overdue_fine_charges ofc
             JOIN borrowings bw ON ofc.borrowing_id = bw.id
             JOIN books b ON bw.book_id = b.id
             WHERE ofc.member_id = ? AND ofc.payment_status = 'unpaid'
             ORDER BY ofc.charged_at DESC"
        );
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }

    public function getUnpaidTotalByMember($memberId) {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(fine_amount), 0) FROM overdue_fine_charges WHERE member_id = ? AND payment_status = 'unpaid'"
        );
        $stmt->execute([$memberId]);
        return floatval($stmt->fetchColumn());
    }

    public function markAsPaid($id, $paymentIntentId) {
        $stmt = $this->db->prepare(
            "UPDATE overdue_fine_charges SET payment_status = 'paid', paid_at = NOW(), payment_intent_id = ? WHERE id = ? AND payment_status = 'unpaid'"
        );
        return $stmt->execute([$paymentIntentId, $id]);
    }

    public function getByBorrowingId($borrowingId) {
        $stmt = $this->db->prepare("SELECT * FROM overdue_fine_charges WHERE borrowing_id = ? AND payment_status = 'unpaid' LIMIT 1");
        $stmt->execute([$borrowingId]);
        return $stmt->fetch();
    }


    public function getTotalPaidForBorrowing($borrowingId) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(fine_amount), 0) FROM overdue_fine_charges WHERE borrowing_id = ? AND payment_status = 'paid'");
        $stmt->execute([$borrowingId]);
        return floatval($stmt->fetchColumn());
    }
}


// 10. Cart Model

class Cart {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Returned-book fines 

    public function addItem($memberId, $returnId) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO cart_items (member_id, return_id) VALUES (?, ?)");
        return $stmt->execute([$memberId, $returnId]);
    }

    public function removeItem($memberId, $returnId) {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE member_id = ? AND return_id = ?");
        return $stmt->execute([$memberId, $returnId]);
    }

    public function clear($memberId) {
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE member_id = ?");
        $stmt->execute([$memberId]);
       
    }

    public function isInCart($memberId, $returnId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM cart_items WHERE member_id = ? AND return_id = ?");
        $stmt->execute([$memberId, $returnId]);
        return $stmt->fetchColumn() > 0;
    }

    public function removeInvalidItems($memberId) {
        $stmt = $this->db->prepare("DELETE ci FROM cart_items ci
                                    JOIN returns r ON ci.return_id = r.id
                                    WHERE ci.member_id = ? AND r.payment_status != 'unpaid'");
        return $stmt->execute([$memberId]);
    }

    // Active overdue fines 

    public function isOverdueChargeInCart($memberId, $borrowingId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM overdue_fine_charges WHERE member_id = ? AND borrowing_id = ? AND payment_status = 'unpaid'");
        $stmt->execute([$memberId, $borrowingId]);
        return $stmt->fetchColumn() > 0;
    }

 

    
    public function countItems($memberId) {
        $stmt1 = $this->db->prepare("SELECT COUNT(*) FROM cart_items WHERE member_id = ?");
        $stmt1->execute([$memberId]);
        $returnedFines = intval($stmt1->fetchColumn());

        $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM overdue_fine_charges WHERE member_id = ? AND payment_status = 'unpaid'");
        $stmt2->execute([$memberId]);
        $overdueCharges = intval($stmt2->fetchColumn());

        return $returnedFines + $overdueCharges;
    }


    public function getItemsByMember($memberId) {
        // Returned book fines
        $stmt1 = $this->db->prepare(
            "SELECT ci.id, ci.return_id, NULL AS overdue_charge_id, 'return' AS item_type,
                    r.fine_amount, r.return_date, r.payment_status,
                    bw.borrow_date, bw.due_date, bw.return_date AS book_return_date, bw.id AS borrowing_id,
                    b.title AS book_title, b.isbn, b.cover_image,
                    ci.added_at AS added_at
             FROM cart_items ci
             JOIN returns r ON ci.return_id = r.id
             JOIN borrowings bw ON r.borrowing_id = bw.id
             JOIN books b ON bw.book_id = b.id
             WHERE ci.member_id = ? AND r.payment_status = 'unpaid'
             ORDER BY ci.added_at DESC"
        );
        $stmt1->execute([$memberId]);
        $returnedFines = $stmt1->fetchAll();

        // Active overdue fines
        $stmt2 = $this->db->prepare(
            "SELECT NULL AS id, NULL AS return_id, ofc.id AS overdue_charge_id, 'overdue' AS item_type,
                    ofc.fine_amount, NULL AS return_date, ofc.payment_status,
                    bw.borrow_date, bw.due_date, NULL AS book_return_date, bw.id AS borrowing_id,
                    b.title AS book_title, b.isbn, b.cover_image,
                    ofc.days_overdue, ofc.charged_at,
                    ofc.created_at AS added_at
             FROM overdue_fine_charges ofc
             JOIN borrowings bw ON ofc.borrowing_id = bw.id
             JOIN books b ON bw.book_id = b.id
             WHERE ofc.member_id = ? AND ofc.payment_status = 'unpaid'
             ORDER BY ofc.charged_at DESC"
        );
        $stmt2->execute([$memberId]);
        $overdueCharges = $stmt2->fetchAll();

        return array_merge($returnedFines, $overdueCharges);
    }


    public function getTotal($memberId) {
        $stmt1 = $this->db->prepare("SELECT COALESCE(SUM(r.fine_amount), 0) FROM cart_items ci JOIN returns r ON ci.return_id = r.id WHERE ci.member_id = ? AND r.payment_status = 'unpaid'");
        $stmt1->execute([$memberId]);
        $returnedTotal = floatval($stmt1->fetchColumn());

        $stmt2 = $this->db->prepare("SELECT COALESCE(SUM(fine_amount), 0) FROM overdue_fine_charges WHERE member_id = ? AND payment_status = 'unpaid'");
        $stmt2->execute([$memberId]);
        $overdueTotal = floatval($stmt2->fetchColumn());

        return $returnedTotal + $overdueTotal;
    }
}

// Payment Model
class Payment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($memberId, $paymentIntentId, $amount) {
        $stmt = $this->db->prepare("INSERT INTO payments (member_id, stripe_payment_intent_id, amount, currency, status, payment_method) VALUES (?, ?, ?, ?, 'pending', 'card')");
        $stmt->execute([$memberId, $paymentIntentId, $amount, PAYMENT_CURRENCY]);
        return $this->db->lastInsertId();
    }

    public function addItem($paymentId, $returnId, $amount) {
        $stmt = $this->db->prepare("INSERT INTO payment_items (payment_id, return_id, amount) VALUES (?, ?, ?)");
        return $stmt->execute([$paymentId, $returnId, $amount]);
    }

    public function findByPaymentIntentId($paymentIntentId) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE stripe_payment_intent_id = ?");
        $stmt->execute([$paymentIntentId]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatus($id, $status, $paidAt = null) {
        $stmt = $this->db->prepare("UPDATE payments SET status = ?, paid_at = ? WHERE id = ?");
        return $stmt->execute([$status, $paidAt, $id]);
    }

    public function getHistoryByMember($memberId) {
        $stmt = $this->db->prepare("SELECT p.*, COUNT(pi.id) AS item_count
                                    FROM payments p
                                    LEFT JOIN payment_items pi ON pi.payment_id = p.id
                                    WHERE p.member_id = ?
                                    GROUP BY p.id
                                    ORDER BY p.created_at DESC");
        $stmt->execute([$memberId]);
        return $stmt->fetchAll();
    }

    public function getItemsByPaymentId($paymentId) {
        $stmt = $this->db->prepare("SELECT pi.*, r.return_date, bw.borrow_date, bw.due_date, b.title AS book_title
                                    FROM payment_items pi
                                    JOIN returns r ON pi.return_id = r.id
                                    JOIN borrowings bw ON r.borrowing_id = bw.id
                                    JOIN books b ON bw.book_id = b.id
                                    WHERE pi.payment_id = ?");
        $stmt->execute([$paymentId]);
        return $stmt->fetchAll();
    }

    public function completePayment($paymentId, $paymentIntentId, $returnIds) {
        try {
            $this->db->beginTransaction();

            $this->updateStatus($paymentId, 'succeeded', date('Y-m-d H:i:s'));

            $fineModel = new Fine();
            foreach ($returnIds as $returnId) {
                $fineModel->markAsPaid($returnId, $paymentIntentId);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}


// 8. Activity Log Model

class ActivityLog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function log($userId, $action, $details = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
            return $stmt->execute([$userId, $action, $details]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getRecentLogs($limit = 50) {
        $stmt = $this->db->prepare("SELECT al.*, u.email 
                                    FROM activity_logs al 
                                    LEFT JOIN users u ON al.user_id = u.id 
                                    ORDER BY al.id DESC LIMIT ?");
        $stmt->bindValue(1, intval($limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
