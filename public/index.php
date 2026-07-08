<?php


define('SECURE_ACCESS', true);


require_once __DIR__ . '/../config/config.php';


require_once BASE_PATH . '/config/database.php';


require_once BASE_PATH . '/app/Helpers/Helpers.php';
require_once BASE_PATH . '/app/Models/Models.php';
require_once BASE_PATH . '/app/Controllers/Controllers.php';

// Start Session
Session::start();

// Action Dispatcher 
$action = $_GET['action'] ?? null;
if ($action) {
    try {
        switch ($action) {
            case 'login':
                $controller = new AuthController();
                $controller->login();
                break;
            case 'register':
                $controller = new AuthController();
                $controller->register();
                break;
            case 'logout':
                $controller = new AuthController();
                $controller->logout();
                break;
            case 'forgot-password':
                $controller = new AuthController();
                $controller->forgotPassword();
                break;
            case 'change-password':
                $controller = new AuthController();
                $controller->changePassword();
                break;
            case 'update-profile':
                $controller = new AuthController();
                $controller->updateProfile();
                break;
            case 'add-book':
                $controller = new BookController();
                $controller->add();
                break;
            case 'edit-book':
                $controller = new BookController();
                $controller->edit();
                break;
            case 'delete-book':
                $controller = new BookController();
                $controller->delete();
                break;
            case 'add-author':
                $controller = new AuthorController();
                $controller->add();
                break;
            case 'edit-author':
                $controller = new AuthorController();
                $controller->edit();
                break;
            case 'delete-author':
                $controller = new AuthorController();
                $controller->delete();
                break;
            case 'add-category':
                $controller = new CategoryController();
                $controller->add();
                break;
            case 'edit-category':
                $controller = new CategoryController();
                $controller->edit();
                break;
            case 'delete-category':
                $controller = new CategoryController();
                $controller->delete();
                break;
            case 'add-member':
                $controller = new MemberController();
                $controller->add();
                break;
            case 'edit-member':
                $controller = new MemberController();
                $controller->edit();
                break;
            case 'delete-member':
                $controller = new MemberController();
                $controller->delete();
                break;
            case 'request-book':
                $controller = new BorrowController();
                $controller->request();
                break;
            case 'approve-request':
                $controller = new BorrowController();
                $controller->approve();
                break;
            case 'reject-request':
                $controller = new BorrowController();
                $controller->reject();
                break;
            case 'return-book':
                $controller = new BorrowController();
                $controller->returnBook();
                break;
            case 'update-due-date':
                $controller = new BorrowController();
                $controller->updateDueDate();
                break;
            case 'import-book':
                $controller = new GoogleBooksController();
                $controller->importBook();
                break;
            case 'add-to-cart':
                $controller = new CartController();
                $controller->add();
                break;
            case 'remove-from-cart':
                $controller = new CartController();
                $controller->remove();
                break;
            case 'clear-cart':
                $controller = new CartController();
                $controller->clear();
                break;
            case 'member-return-book':
                $controller = new BorrowController();
                $controller->memberReturnBook();
                break;
            case 'charge-overdue-fine':
                $controller = new BorrowController();
                $controller->chargeOverdueFine();
                break;
            case 'create-payment-intent':
                $controller = new PaymentController();
                $controller->createPaymentIntent();
                break;
            case 'checkout-confirm':
                $controller = new PaymentController();
                $controller->confirmPayment();
                break;
            default:
                throw new Exception("Action not recognized.");
        }
    } catch (Exception $e) {
        Session::setFlash('error', $e->getMessage());
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/index.php'));
        exit;
    }
}

// Page Routing
$route = $_GET['route'] ?? 'home';
$viewFile = '';
$pageTitle = 'Home';

// view files and access requirements
switch ($route) {
    case 'home':
        $viewFile = 'pages/home.php';
        $pageTitle = 'Home';
        break;
    case 'about':
        $viewFile = 'pages/about.php';
        $pageTitle = 'About Us';
        break;
    case 'contact':
        $viewFile = 'pages/contact.php';
        $pageTitle = 'Contact Us';
        break;
    case 'books':
        $viewFile = 'pages/books.php';
        $pageTitle = 'Browse Books';
        break;
    case 'book-details':
        $viewFile = 'book-details.php';
        $pageTitle = 'Book Details';
        break;
    case 'login':
        if (Auth::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/index.php?route=home');
            exit;
        }
        $viewFile = 'auth/login.php';
        $pageTitle = 'Login';
        break;
    case 'register':
        if (Auth::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/index.php?route=home');
            exit;
        }
        $viewFile = 'auth/register.php';
        $pageTitle = 'Register';
        break;
    case 'forgot-password':
        $viewFile = 'auth/forgot-password.php';
        $pageTitle = 'Reset Password';
        break;
    case 'change-password':
        Auth::requireLogin();
        $viewFile = 'auth/change-password.php';
        $pageTitle = 'Change Password';
        break;
    case 'profile':
        Auth::requireLogin();
        $viewFile = 'auth/profile.php';
        $pageTitle = 'My Profile';
        break;

    // Member Specific routes
    case 'member/dashboard':
        Auth::requireMember();
        $viewFile = 'member/dashboard.php';
        $pageTitle = 'Member Dashboard';
        break;
    case 'member/borrowings':
        Auth::requireMember();
        $viewFile = 'member/borrowings.php';
        $pageTitle = 'My Borrowings';
        break;
    case 'member/cart':
        Auth::requireMember();
        $viewFile = 'member/cart.php';
        $pageTitle = 'My Payment Cart';
        break;
    case 'member/checkout':
        Auth::requireMember();
        $viewFile = 'member/checkout.php';
        $pageTitle = 'Fine Checkout';
        break;
    case 'member/payment-success':
        Auth::requireMember();
        $viewFile = 'member/payment-success.php';
        $pageTitle = 'Payment Confirmation';
        break;

    // Admin Specific routes
    case 'admin/dashboard':
        Auth::requireAdmin();
        $viewFile = 'admin/dashboard.php';
        $pageTitle = 'Admin Dashboard';
        break;
    case 'admin/books':
        Auth::requireAdmin();
        $viewFile = 'admin/books.php';
        $pageTitle = 'Manage Books';
        break;

    case 'admin/authors':
        Auth::requireAdmin();
        $viewFile = 'admin/authors.php';
        $pageTitle = 'Manage Authors';
        break;
    case 'admin/categories':
        Auth::requireAdmin();
        $viewFile = 'admin/categories.php';
        $pageTitle = 'Manage Categories';
        break;
    case 'admin/members':
        Auth::requireAdmin();
        $viewFile = 'admin/members.php';
        $pageTitle = 'Manage Members';
        break;
    case 'admin/requests':            
    case 'admin/borrow-requests':
        Auth::requireAdmin();
        $viewFile = 'admin/requests.php';
        $pageTitle = 'Manage Borrow Requests';
        break;
    case 'admin/reports':
        Auth::requireAdmin();
        $viewFile = 'admin/reports.php';
        $pageTitle = 'Library Reports';
        break;
    case 'admin/settings':
        Auth::requireAdmin();
        $viewFile = 'admin/settings.php';
        $pageTitle = 'Activity Logs & Settings';
        break;
    case 'admin/google-books':
        Auth::requireAdmin();
        $viewFile = 'admin/google-books.php';
        $pageTitle = 'Import from Google Books';
        break;

    default:
        header('Location: ' . BASE_URL . '/index.php?route=home');
        exit;
}

// Render Page using layouts
$fullViewPath = VIEWS_PATH . '/' . $viewFile;
if (file_exists($fullViewPath)) {
   
    $isAdminRoute = (strpos($route, 'admin/') === 0);
    
   
    require_once VIEWS_PATH . '/layouts/header.php';
    
    if ($isAdminRoute) {
        echo '<div class="admin-wrapper d-flex">';
        require_once VIEWS_PATH . '/layouts/sidebar.php';
        echo '<div class="admin-content-area flex-grow-1 p-4">';
        require_once $fullViewPath;
        echo '</div>';
        echo '</div>'; 
    } else {
        require_once $fullViewPath;
    }
    
    require_once VIEWS_PATH . '/layouts/footer.php';
} else {
    header('Location: ' . BASE_URL . '/index.php?route=home');
    exit;
}
