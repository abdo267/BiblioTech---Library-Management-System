<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

// Auth Controller
class AuthController {
    private $userModel;
    private $memberModel;

    public function __construct() {
        $this->userModel = new User();
        $this->memberModel = new Member();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Session::setFlash('error', 'Please enter email and password.');
            header('Location: ' . BASE_URL . '/index.php?route=login');
            exit;
        }

        $user = $this->userModel->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                Session::setFlash('error', 'Your account is suspended.');
                header('Location: ' . BASE_URL . '/index.php?route=login');
                exit;
            }

            Auth::login($user);
            Session::setFlash('success', 'Welcome back!');
            
            $dashboard = $user['role'] === 'admin' ? 'admin/dashboard' : 'member/dashboard';
            header('Location: ' . BASE_URL . '/index.php?route=' . $dashboard);
            exit;
        }

        Session::setFlash('error', 'Invalid email or password.');
        header('Location: ' . BASE_URL . '/index.php?route=login');
        exit;
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=register');
            exit;
        }

        $fullName = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';

        if (strlen($fullName) < 3) {
            Session::setFlash('error', 'Name must be at least 3 characters.');
            header('Location: ' . BASE_URL . '/index.php?route=register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Invalid email address.');
            header('Location: ' . BASE_URL . '/index.php?route=register');
            exit;
        }

        if (strlen($password) < 6) {
            Session::setFlash('error', 'Password must be at least 6 characters.');
            header('Location: ' . BASE_URL . '/index.php?route=register');
            exit;
        }

        if ($password !== $confirmPassword) {
            Session::setFlash('error', 'Passwords do not match.');
            header('Location: ' . BASE_URL . '/index.php?route=register');
            exit;
        }

        if ($this->userModel->findByEmail($email)) {
            Session::setFlash('error', 'Email already registered.');
            header('Location: ' . BASE_URL . '/index.php?route=register');
            exit;
        }

        try {
            $userId = $this->userModel->create($email, $password, 'member', 'active');
            $this->memberModel->create($userId, $fullName, $phone, $address, date('Y-m-d'), 'active');
            Auth::login($this->userModel->findById($userId));
            Session::setFlash('success', 'Registration successful!');
            header('Location: ' . BASE_URL . '/index.php?route=member/dashboard');
            exit;
        } catch (Exception $e) {
            Session::setFlash('error', 'Registration failed. Please try again.');
            header('Location: ' . BASE_URL . '/index.php?route=register');
            exit;
        }
    }

    public function logout() {
        Auth::logout();
        Session::setFlash('success', 'Logged out successfully.');
        header('Location: ' . BASE_URL . '/index.php?route=login');
        exit;
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=forgot-password');
            exit;
        }

        if (isset($_POST['token'])) {
            $token = $_POST['token'];
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (strlen($password) < 6) {
                Session::setFlash('error', 'Password must be at least 6 characters.');
                header('Location: ' . BASE_URL . '/index.php?route=forgot-password&token=' . urlencode($token));
                exit;
            }

            if ($password !== $confirmPassword) {
                Session::setFlash('error', 'Passwords do not match.');
                header('Location: ' . BASE_URL . '/index.php?route=forgot-password&token=' . urlencode($token));
                exit;
            }

            $user = $this->userModel->findByResetToken($token);
            if ($user) {
                $this->userModel->updatePassword($user['id'], $password);
                $this->userModel->clearResetToken($user['id']);
                Session::setFlash('success', 'Password reset successfully.');
                header('Location: ' . BASE_URL . '/index.php?route=login');
                exit;
            }
            Session::setFlash('error', 'Invalid or expired token.');
            header('Location: ' . BASE_URL . '/index.php?route=forgot-password');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $user = $this->userModel->findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $this->userModel->createResetToken($email, $token, $expiry);
        }
        Session::setFlash('success', 'If the email exists, reset instructions were sent.');
        header('Location: ' . BASE_URL . '/index.php?route=login');
        exit;
    }

    public function changePassword() {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=change-password');
            exit;
        }

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_new_password'] ?? '';

        if (strlen($new) < 6) {
            Session::setFlash('error', 'Password must be at least 6 characters.');
            header('Location: ' . BASE_URL . '/index.php?route=change-password');
            exit;
        }

        if ($new !== $confirm) {
            Session::setFlash('error', 'Passwords do not match.');
            header('Location: ' . BASE_URL . '/index.php?route=change-password');
            exit;
        }

        $user = $this->userModel->findById(Auth::getUserId());
        if ($user && password_verify($current, $user['password'])) {
            $this->userModel->updatePassword(Auth::getUserId(), $new);
            Session::setFlash('success', 'Password updated successfully.');
            header('Location: ' . BASE_URL . '/index.php?route=profile');
            exit;
        }
        Session::setFlash('error', 'Incorrect current password.');
        header('Location: ' . BASE_URL . '/index.php?route=change-password');
        exit;
    }

    public function updateProfile() {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=profile');
            exit;
        }

        $fullName = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';

        if (strlen($fullName) < 3) {
            Session::setFlash('error', 'Name must be at least 3 characters.');
            header('Location: ' . BASE_URL . '/index.php?route=profile');
            exit;
        }

        if (Auth::getUserRole() === 'member') {
            $this->memberModel->update(Auth::getMemberId(), $fullName, $phone, $address);
            Session::set('member_name', $fullName);
        }

        Session::setFlash('success', 'Profile updated successfully.');
        header('Location: ' . BASE_URL . '/index.php?route=profile');
        exit;
    }
}

// Book Controller
class BookController {
    private $bookModel;

    public function __construct() {
        Auth::requireAdmin();
        $this->bookModel = new Book();
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=admin/books');
            exit;
        }

        if (empty($_POST['title']) || empty($_POST['quantity'])) {
            Session::setFlash('error', 'Title and quantity are required.');
            header('Location: ' . BASE_URL . '/index.php?route=admin/books');
            exit;
        }

        $cover = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $up = Uploader::uploadCover($_FILES['cover_image']);
            if (!$up['status']) {
                Session::setFlash('error', $up['error']);
                header('Location: ' . BASE_URL . '/index.php?route=admin/books');
                exit;
            }
            $cover = $up['filename'];
        }

        $bookData = [
            'isbn' => $_POST['isbn'] ?? null,
            'title' => $_POST['title'],
            'author_id' => !empty($_POST['author_id']) ? intval($_POST['author_id']) : null,
            'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
            'description' => $_POST['description'] ?? '',
            'publisher' => $_POST['publisher'] ?? '',
            'publication_year' => !empty($_POST['publication_year']) ? intval($_POST['publication_year']) : null,
            'quantity' => intval($_POST['quantity']),
            'shelf_location' => $_POST['shelf_location'] ?? '',
            'cover_image' => $cover
        ];

        $this->bookModel->create($bookData);
        Session::setFlash('success', 'Book added!');
        header('Location: ' . BASE_URL . '/index.php?route=admin/books');
        exit;
    }

    public function edit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=admin/books');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        $book = $this->bookModel->findById($id);
        if (!$book) {
            Session::setFlash('error', 'Book not found.');
            header('Location: ' . BASE_URL . '/index.php?route=admin/books');
            exit;
        }

        if (empty($_POST['title']) || empty($_POST['quantity'])) {
            Session::setFlash('error', 'Title and quantity are required.');
            header('Location: ' . BASE_URL . '/index.php?route=admin/books');
            exit;
        }

        $cover = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $up = Uploader::uploadCover($_FILES['cover_image']);
            if (!$up['status']) {
                Session::setFlash('error', $up['error']);
                header('Location: ' . BASE_URL . '/index.php?route=admin/books');
                exit;
            }
            $cover = $up['filename'];
            if ($book['cover_image'] && file_exists(UPLOAD_PATH . '/' . $book['cover_image'])) {
                @unlink(UPLOAD_PATH . '/' . $book['cover_image']);
            }
        }

        $bookData = [
            'isbn' => $_POST['isbn'] ?? null,
            'title' => $_POST['title'],
            'author_id' => !empty($_POST['author_id']) ? intval($_POST['author_id']) : null,
            'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
            'description' => $_POST['description'] ?? '',
            'publisher' => $_POST['publisher'] ?? '',
            'publication_year' => !empty($_POST['publication_year']) ? intval($_POST['publication_year']) : null,
            'quantity' => intval($_POST['quantity']),
            'shelf_location' => $_POST['shelf_location'] ?? '',
            'cover_image' => $cover
        ];

        $this->bookModel->update($id, $bookData);
        Session::setFlash('success', 'Book updated!');
        header('Location: ' . BASE_URL . '/index.php?route=admin/books');
        exit;
    }

    public function delete() {
        $id = intval($_GET['id'] ?? 0);
        $book = $this->bookModel->findById($id);
        if ($book) {
            if ($book['cover_image'] && file_exists(UPLOAD_PATH . '/' . $book['cover_image'])) {
                @unlink(UPLOAD_PATH . '/' . $book['cover_image']);
            }
            try {
                $this->bookModel->delete($id);
                Session::setFlash('success', 'Book deleted.');
            } catch (Exception $e) {
                Session::setFlash('error', 'Cannot delete book with active borrows.');
            }
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/books');
        exit;
    }
}

// Author Controller
class AuthorController {
    private $authorModel;

    public function __construct() {
        Auth::requireAdmin();
        $this->authorModel = new Author();
    }

    public function add() {
        $name = $_POST['name'] ?? '';
        if ($name) {
            $this->authorModel->create($name, $_POST['biography'] ?? '', $_POST['nationality'] ?? '');
            Session::setFlash('success', 'Author added.');
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/authors');
        exit;
    }

    public function edit() {
        $id = intval($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        if ($id && $name) {
            $this->authorModel->update($id, $name, $_POST['biography'] ?? '', $_POST['nationality'] ?? '');
            Session::setFlash('success', 'Author updated.');
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/authors');
        exit;
    }

    public function delete() {
        $id = intval($_GET['id'] ?? 0);
        $this->authorModel->delete($id);
        Session::setFlash('success', 'Author deleted.');
        header('Location: ' . BASE_URL . '/index.php?route=admin/authors');
        exit;
    }
}

// Category Controller
class CategoryController {
    private $catModel;

    public function __construct() {
        Auth::requireAdmin();
        $this->catModel = new Category();
    }

    public function add() {
        $name = $_POST['name'] ?? '';
        if ($name) {
            $this->catModel->create($name);
            Session::setFlash('success', 'Category added.');
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/categories');
        exit;
    }

    public function edit() {
        $id = intval($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        if ($id && $name) {
            $this->catModel->update($id, $name);
            Session::setFlash('success', 'Category updated.');
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/categories');
        exit;
    }

    public function delete() {
        $id = intval($_GET['id'] ?? 0);
        $this->catModel->delete($id);
        Session::setFlash('success', 'Category deleted.');
        header('Location: ' . BASE_URL . '/index.php?route=admin/categories');
        exit;
    }
}

// Member Controller
class MemberController {
    private $userModel;
    private $memberModel;

    public function __construct() {
        Auth::requireAdmin();
        $this->userModel = new User();
        $this->memberModel = new Member();
    }

    public function add() {
        $fullName = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($fullName && $email && $password) {
            if ($this->userModel->findByEmail($email)) {
                Session::setFlash('error', 'Email already in use.');
            } else {
                $userId = $this->userModel->create($email, $password, 'member', $_POST['status'] ?? 'active');
                $this->memberModel->create($userId, $fullName, $_POST['phone'] ?? '', $_POST['address'] ?? '', date('Y-m-d'), $_POST['status'] ?? 'active');
                Session::setFlash('success', 'Member added.');
            }
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/members');
        exit;
    }

    public function edit() {
        $id = intval($_POST['id'] ?? 0);
        $fullName = $_POST['full_name'] ?? '';
        if ($id && $fullName) {
            $this->memberModel->update($id, $fullName, $_POST['phone'] ?? '', $_POST['address'] ?? '', $_POST['status'] ?? 'active');
            Session::setFlash('success', 'Member updated.');
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/members');
        exit;
    }

    public function delete() {
        $id = intval($_GET['id'] ?? 0);
        $this->memberModel->delete($id);
        Session::setFlash('success', 'Member deleted.');
        header('Location: ' . BASE_URL . '/index.php?route=admin/members');
        exit;
    }
}

// Borrow Controller
class BorrowController {
    private $reqModel;
    private $borrowModel;
    private $logModel;

    public function __construct() {
        $this->reqModel = new BorrowRequest();
        $this->borrowModel = new Borrowing();
        $this->logModel = new ActivityLog();
    }

    public function request() {
        Auth::requireMember();
        $bookId = intval($_GET['book_id'] ?? 0);
        $memberId = Auth::getMemberId();

        if ($bookId && $memberId) {
            if ($this->reqModel->hasActiveRequestOrBorrow($memberId, $bookId)) {
                Session::setFlash('error', 'You already have an active request or checkout for this book.');
            } else {
                $this->reqModel->create($memberId, $bookId);
                $this->logModel->log(Auth::getUserId(), 'Borrow Request', 'Request submitted for Book ID: ' . $bookId);
                Session::setFlash('success', 'Your reservation request is submitted for review!');
            }
        }
        header('Location: ' . BASE_URL . '/index.php?route=member/borrowings');
        exit;
    }

    public function approve() {
        Auth::requireAdmin();
        $reqId = intval($_GET['id'] ?? 0);
        $req = $this->reqModel->findById($reqId);

        if ($req && $req['status'] === 'pending') {
            try {
                $borrowDate = date('Y-m-d');
                $dueDate = date('Y-m-d', strtotime('+14 days')); // 2 weeks checkout limit
                
                $this->borrowModel->create($reqId, $req['member_id'], $req['book_id'], $borrowDate, $dueDate);
                $this->reqModel->updateStatus($reqId, 'approved', 'Approved by system');
                
                $this->logModel->log(Auth::getUserId(), 'Approve Request', 'Approved checkout request ID: ' . $reqId);
                EmailSim::send($req['member_email'], "Borrow Request Approved", "Your request for '{$req['book_title']}' has been approved. Please collect it. Due date: {$dueDate}.");

                Session::setFlash('success', 'Request approved. Checkout registered.');
            } catch (Exception $e) {
                Session::setFlash('error', 'Checkout failed: ' . $e->getMessage());
            }
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/requests');
        exit;
    }

    public function reject() {
        Auth::requireAdmin();
        $reqId = intval($_GET['id'] ?? 0);
        $notes = $_POST['admin_notes'] ?? 'Rejected';
        $req = $this->reqModel->findById($reqId);

        if ($req && $req['status'] === 'pending') {
            $this->reqModel->updateStatus($reqId, 'rejected', $notes);
            $this->logModel->log(Auth::getUserId(), 'Reject Request', 'Rejected request ID: ' . $reqId);
            
            EmailSim::send($req['member_email'], "Borrow Request Rejected", "Your reservation request for '{$req['book_title']}' was declined. Reason: {$notes}");
            Session::setFlash('success', 'Reservation request rejected.');
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/requests');
        exit;
    }

    public function returnBook() {
        Auth::requireAdmin();
        $borrowId = intval($_GET['id'] ?? 0);
        $borrow = $this->borrowModel->findById($borrowId);

        if ($borrow && $borrow['status'] === 'borrowed') {
            $returnDate = date('Y-m-d');

            // Fines: 100 EGP per day past due date
            $dueTime = strtotime($borrow['due_date']);
            $returnTime = strtotime($returnDate);
            $fine = 0.00;
            if ($returnTime > $dueTime) {
                $days = ceil(($returnTime - $dueTime) / (60 * 60 * 24));
                $fine = $days * FINE_RATE_PER_DAY;
            }

            try {
                $this->borrowModel->markAsReturned($borrowId, $returnDate, $fine, 'returned');
                $this->logModel->log(Auth::getUserId(), 'Check In Book', 'Processed check-in for borrowing ID: ' . $borrowId);
                EmailSim::send($borrow['member_email'], "Book Checked In", "Book '{$borrow['book_title']}' has been returned. Late fine charged: EGP {$fine}.");

                Session::setFlash('success', 'Book returned successfully.' . ($fine > 0 ? ' Late fine charged: EGP ' . number_format($fine, 2) : ''));
            } catch (Exception $e) {
                Session::setFlash('error', 'Check-in failed: ' . $e->getMessage());
            }
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/requests');
        exit;
    }

    public function updateDueDate() {
        Auth::requireAdmin();
        $borrowId = intval($_POST['borrow_id'] ?? 0);
        $newDueDate = trim($_POST['new_due_date'] ?? '');

        if (!$borrowId || !$newDueDate || !strtotime($newDueDate)) {
            Session::setFlash('error', 'Invalid input for due date update.');
            header('Location: ' . BASE_URL . '/index.php?route=admin/requests');
            exit;
        }

        $borrow = $this->borrowModel->findById($borrowId);
        if (!$borrow || $borrow['status'] !== 'borrowed') {
            Session::setFlash('error', 'Borrowing record not found or already returned.');
            header('Location: ' . BASE_URL . '/index.php?route=admin/requests');
            exit;
        }

        try {
            $this->borrowModel->updateDueDate($borrowId, $newDueDate);
            $this->logModel->log(Auth::getUserId(), 'Update Due Date', "Changed due date for borrowing #{$borrowId} ({$borrow['book_title']}) to {$newDueDate}");
            Session::setFlash('success', 'Due date updated to ' . date('M d, Y', strtotime($newDueDate)) . '.');
        } catch (Exception $e) {
            Session::setFlash('error', 'Failed to update due date: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/index.php?route=admin/requests');
        exit;
    }

    public function memberReturnBook() {
        Auth::requireMember();
        $memberId = Auth::getMemberId();
        $borrowId = intval($_GET['id'] ?? 0);
        $borrow = $this->borrowModel->findById($borrowId);

        if ($borrow && $borrow['member_id'] == $memberId && $borrow['status'] === 'borrowed') {
            $returnDate = date('Y-m-d');

            // Fines: 100 EGP per day past due date
            $dueTime = strtotime($borrow['due_date']);
            $returnTime = strtotime($returnDate);
            $fine = 0.00;
            if ($returnTime > $dueTime) {
                $days = ceil(($returnTime - $dueTime) / (60 * 60 * 24));
                $fine = $days * FINE_RATE_PER_DAY;
            }

         
            $overdueModel = new OverdueFineCharge();
            $alreadyPaid = $overdueModel->getTotalPaidForBorrowing($borrowId);
            $netFine = max(0, $fine - $alreadyPaid);

            try {
                $this->borrowModel->markAsReturned($borrowId, $returnDate, $netFine, 'returned');
                $this->logModel->log(Auth::getUserId(), 'Return Book', 'Member returned book: ' . $borrow['book_title']);
                EmailSim::send($borrow['member_email'], "Book Returned", "Book '{$borrow['book_title']}' has been returned. Late fine: EGP {$netFine}.");

                if ($netFine > 0) {
                    Session::setFlash('success', 'Book returned. Remaining fine of EGP ' . number_format($netFine, 2) . ' charged. Please pay it in your cart.');
                } elseif ($alreadyPaid > 0) {
                    Session::setFlash('success', 'Book returned successfully! Your previously paid fine of EGP ' . number_format($alreadyPaid, 2) . ' has been credited.');
                } else {
                    Session::setFlash('success', 'Book returned successfully!');
                }
            } catch (Exception $e) {
                Session::setFlash('error', 'Return failed: ' . $e->getMessage());
            }
        } else {
            Session::setFlash('error', 'Invalid return request.');
        }
        header('Location: ' . BASE_URL . '/index.php?route=member/borrowings');
        exit;
    }

   
    public function chargeOverdueFine() {
        Auth::requireMember();
        $memberId = Auth::getMemberId();
        $borrowId = intval($_GET['id'] ?? 0);

        if (!$borrowId) {
            Session::setFlash('error', 'Invalid request.');
            header('Location: ' . BASE_URL . '/index.php?route=member/borrowings');
            exit;
        }

        try {
            $overdueModel = new OverdueFineCharge();
            $chargeId = $overdueModel->chargeForBorrowing($borrowId, $memberId);

            $cartModel = new Cart();
            if (!$cartModel->isOverdueChargeInCart($memberId, $borrowId)) {
               
            }

            $charge = $overdueModel->findById($chargeId);
            Session::setFlash('success', 
                'Overdue fine of EGP ' . number_format($charge['fine_amount'], 2) . 
                ' (' . $charge['days_overdue'] . ' day(s)) has been added to your cart.'
            );
        } catch (Exception $e) {
            Session::setFlash('error', 'Could not charge fine: ' . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/index.php?route=member/cart');
        exit;
    }
}

// -------------------------------------------------------------
// 7. GoogleBooksController


class GoogleBooksController {
    private $bookModel;
    private $authorModel;
    private $categoryModel;
    private $logModel;
    private $lastError = null;

    public function __construct() {
        $this->bookModel = new Book();
        $this->authorModel = new Author();
        $this->categoryModel = new Category();
        $this->logModel = new ActivityLog();
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function searchBooks($query) {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $cleanIsbn = preg_replace('/[^0-9Xx]/', '', $query);
        if (strlen($cleanIsbn) === 10 || strlen($cleanIsbn) === 13) {
            $searchParam = 'isbn:' . $cleanIsbn;
        } else {
            $searchParam = $query;
        }

        $params = [
            'q' => $searchParam,
            'maxResults' => 20,
            'printType' => 'books'
        ];
        if (GOOGLE_BOOKS_API_KEY !== '') {
            $params['key'] = GOOGLE_BOOKS_API_KEY;
        }

        $url = GOOGLE_BOOKS_API_URL . '?' . http_build_query($params);
        $response = $this->httpGet($url);

        if ($response === false) {
            return [];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->lastError = 'Invalid response from Google Books API.';
            return [];
        }

        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? 'Google Books API returned an error.';
            $this->lastError = $message;
            return [];
        }

        if (empty($data['items'])) {
            return [];
        }

        $results = [];
        foreach ($data['items'] as $item) {
            $mapped = $this->mapVolume($item);
            if ($mapped !== null) {
                $results[] = $mapped;
            }
        }
        return $results;
    }

    public function importBook() {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=admin/google-books');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            Session::setFlash('error', 'Book title is required for import.');
            header('Location: ' . BASE_URL . '/index.php?route=admin/google-books');
            exit;
        }

        $isbn = trim($_POST['isbn'] ?? '') ?: null;
        if ($isbn && $this->bookModel->findByIsbn($isbn)) {
            Session::setFlash('error', 'A book with ISBN ' . htmlspecialchars($isbn) . ' already exists in the catalog.');
            header('Location: ' . BASE_URL . '/index.php?route=admin/google-books');
            exit;
        }

        $authorId = $this->resolveAuthorId($_POST['authors'] ?? '');
        $categoryId = $this->resolveCategoryId($_POST['category'] ?? '');

        $cover = null;
        $coverUrl = trim($_POST['cover_url'] ?? '');
        if ($coverUrl !== '') {
            $coverUrl = str_replace('http://', 'https://', $coverUrl);
            $coverUrl = preg_replace('/&zoom=\d+/', '&zoom=1', $coverUrl);
            $downloaded = Uploader::downloadCoverFromUrl($coverUrl);
            if ($downloaded['status']) {
                $cover = $downloaded['filename'];
            }
        }

        $publicationYear = $this->extractYear($_POST['published_date'] ?? '');

        $bookData = [
            'isbn' => $isbn,
            'title' => $title,
            'author_id' => $authorId,
            'category_id' => $categoryId,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'publisher' => trim($_POST['publisher'] ?? '') ?: null,
            'publication_year' => $publicationYear,
            'quantity' => 1,
            'shelf_location' => null,
            'cover_image' => $cover
        ];

        $bookId = $this->bookModel->create($bookData);
        $this->logModel->log(Auth::getUserId(), 'Import Book', 'Imported from Google Books: ' . $title);

        Session::setFlash('success', 'Book "' . htmlspecialchars($title) . '" imported successfully from Google Books!');
        header('Location: ' . BASE_URL . '/index.php?route=admin/books');
        exit;
    }

    private function mapVolume(array $item) {
        $info = $item['volumeInfo'] ?? [];
        $title = trim($info['title'] ?? '');
        if ($title === '') {
            return null;
        }

        $authors = $info['authors'] ?? [];
        $authorsStr = !empty($authors) ? implode(', ', $authors) : 'Unknown Author';

        $category = 'General';
        if (!empty($info['categories'][0])) {
            $parts = explode('/', $info['categories'][0]);
            $category = trim($parts[0]);
        }

        $isbn = $this->extractIsbn($info['industryIdentifiers'] ?? []);

        $coverUrl = '';
        if (!empty($info['imageLinks'])) {
            $links = $info['imageLinks'];
            $coverUrl = $links['thumbnail'] ?? $links['smallThumbnail'] ?? '';
            $coverUrl = str_replace('http://', 'https://', $coverUrl);
        }

        $description = strip_tags($info['description'] ?? '');
        if (strlen($description) > 1000) {
            $description = substr($description, 0, 997) . '...';
        }

        return [
            'title' => $title,
            'authors' => $authorsStr,
            'category' => $category,
            'isbn' => $isbn,
            'description' => $description,
            'publisher' => $info['publisher'] ?? '',
            'publishedDate' => $info['publishedDate'] ?? '',
            'coverUrl' => $coverUrl
        ];
    }

    private function extractIsbn(array $identifiers) {
        $isbn13 = null;
        $isbn10 = null;
        foreach ($identifiers as $id) {
            if (($id['type'] ?? '') === 'ISBN_13') {
                $isbn13 = $id['identifier'] ?? null;
            } elseif (($id['type'] ?? '') === 'ISBN_10') {
                $isbn10 = $id['identifier'] ?? null;
            }
        }
        return $isbn13 ?? $isbn10 ?? '';
    }

    private function extractYear($publishedDate) {
        if (preg_match('/^(\d{4})/', trim($publishedDate), $matches)) {
            return intval($matches[1]);
        }
        return null;
    }

    private function resolveAuthorId($authorsString) {
        $authorsString = trim($authorsString);
        if ($authorsString === '' || $authorsString === 'Unknown Author') {
            return null;
        }

        $firstAuthor = trim(explode(',', $authorsString)[0]);
        $existing = $this->authorModel->findByName($firstAuthor);
        if ($existing) {
            return $existing['id'];
        }

        return $this->authorModel->create($firstAuthor, '', '');
    }

    private function resolveCategoryId($categoryName) {
        $categoryName = trim($categoryName);
        if ($categoryName === '' || $categoryName === 'General') {
            return null;
        }

        $existing = $this->categoryModel->findByName($categoryName);
        if ($existing) {
            return $existing['id'];
        }

        return $this->categoryModel->create($categoryName);
    }

    private function httpGet($url) {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_USERAGENT => 'BiblioTech/1.0',
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                $this->lastError = 'Could not connect to Google Books API.';
                return false;
            }

            if ($httpCode === 429) {
                $this->lastError = 'Google Books API rate limit reached. Add an API key in config/config.local.php for higher limits.';
                return false;
            }

            if ($httpCode !== 200) {
                $data = json_decode($response, true);
                $this->lastError = $data['error']['message'] ?? ('Google Books API returned HTTP ' . $httpCode . '.');
                return false;
            }

            return $response;
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 20,
                'user_agent' => 'BiblioTech/1.0',
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            $this->lastError = 'Could not connect to Google Books API.';
            return false;
        }

        if (isset($http_response_header[0]) && !str_contains($http_response_header[0], '200')) {
            if (str_contains($http_response_header[0], '429')) {
                $this->lastError = 'Google Books API rate limit reached. Add an API key in config/config.local.php for higher limits.';
            } else {
                $data = json_decode($response, true);
                $this->lastError = $data['error']['message'] ?? 'Google Books API request failed.';
            }
            return false;
        }

        return $response;
    }
}

// Cart Controller
class CartController {
    private $cartModel;

    public function __construct() {
        Auth::requireMember();
        $this->cartModel = new Cart();
    }

    public function add() {
        $memberId = Auth::getMemberId();
        $returnId = intval($_GET['return_id'] ?? 0);
        if ($returnId > 0) {
            $this->cartModel->addItem($memberId, $returnId);
            Session::setFlash('success', 'Fine added to cart.');
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/index.php?route=member/borrowings')));
        exit;
    }

    public function remove() {
        $memberId = Auth::getMemberId();
        $returnId = intval($_GET['return_id'] ?? 0);
        if ($returnId > 0) {
            $this->cartModel->removeItem($memberId, $returnId);
            Session::setFlash('success', 'Fine removed from cart.');
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/index.php?route=member/cart')));
        exit;
    }

    public function clear() {
        $memberId = Auth::getMemberId();
        $this->cartModel->clear($memberId);
        Session::setFlash('success', 'Cart cleared.');
        header('Location: ' . BASE_URL . '/index.php?route=member/cart');
        exit;
    }
}

// Payment Controller
class PaymentController {
    private $cartModel;
    private $paymentModel;
    private $fineModel;

    public function __construct() {
        Auth::requireMember();
        $this->cartModel = new Cart();
        $this->paymentModel = new Payment();
        $this->fineModel = new Fine();
    }

    public function createPaymentIntent() {
        $memberId = Auth::getMemberId();
        $total = $this->cartModel->getTotal($memberId);
        
        header('Content-Type: application/json');
        if ($total <= 0) {
            echo json_encode(['error' => 'Cart is empty.']);
            exit;
        }

        try {
            $isRealStripe = STRIPE_SECRET_KEY !== '' && STRIPE_PUBLISHABLE_KEY !== '' && !str_starts_with(STRIPE_SECRET_KEY, 'sk_test_YOUR');
            if ($isRealStripe) {
                $amountCents = round($total * 100);
                $intent = StripeService::createPaymentIntent($amountCents, [
                    'member_id' => $memberId,
                    'email' => Auth::getUserEmail()
                ]);
                echo json_encode(['clientSecret' => $intent['client_secret']]);
            } else {
                echo json_encode(['clientSecret' => 'simulated_secret_' . uniqid()]);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function confirmPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/index.php?route=member/checkout');
            exit;
        }

        $memberId    = Auth::getMemberId();
        $cartItems   = $this->cartModel->getItemsByMember($memberId);
        $total       = $this->cartModel->getTotal($memberId);

        if (empty($cartItems)) {
            Session::setFlash('error', 'Cart is empty.');
            header('Location: ' . BASE_URL . '/index.php?route=member/cart');
            exit;
        }

        $paymentIntentId = $_POST['payment_intent_id'] ?? ('pi_sim_' . uniqid());

        try {
            $paymentId   = $this->paymentModel->create($memberId, $paymentIntentId, $total);
            $overdueModel = new OverdueFineCharge();

            foreach ($cartItems as $item) {
                $refReturnId = $item['return_id'] ?? null;
                $this->paymentModel->addItem($paymentId, $refReturnId, $item['fine_amount']);

                if ($item['item_type'] === 'overdue') {
                    $overdueModel->markAsPaid($item['overdue_charge_id'], $paymentIntentId);
                }
            }

            $returnIds = array_filter(array_column($cartItems, 'return_id'));
            if (!empty($returnIds)) {
                $this->paymentModel->completePayment($paymentId, $paymentIntentId, array_values($returnIds));
            } else {
                $this->paymentModel->updateStatus($paymentId, 'succeeded', date('Y-m-d H:i:s'));
            }

            $this->cartModel->clear($memberId);
            Session::setFlash('success', 'Payment confirmed!');
            header('Location: ' . BASE_URL . '/index.php?route=member/payment-success&id=' . $paymentId);
            exit;
        } catch (Exception $e) {
            Session::setFlash('error', 'Payment failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/index.php?route=member/checkout');
            exit;
        }
    }
}

