<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A premium library management system for browsing, borrowing, and auditing catalog inventory.">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Welcome'; ?> | <?php echo SITE_NAME; ?></title>
    
   
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        #page-loader.loader-hidden {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
    </style>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets CDNs-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Local Styling -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Loading Spinner Screen -->
    <div id="page-loader">
        <div class="text-center">
            <div class="loader-spinner mb-3"></div>
            <h5 class="fw-medium text-muted">Loading BiblioTech...</h5>
        </div>
    </div>
    <script>
        // Hide loader 
        function hidePageLoader() {
            var loader = document.getElementById('page-loader');
            if (loader) {
                loader.style.opacity = '0';
                loader.style.visibility = 'hidden';
                loader.style.pointerEvents = 'none';
                setTimeout(function() { loader.style.display = 'none'; }, 350);
            }
        }
        document.addEventListener('DOMContentLoaded', hidePageLoader);
        // Hard fallback 
        setTimeout(hidePageLoader, 1500);
    </script>

    <?php 
    
    $currentRoute = $_GET['route'] ?? 'home';
    $isAdminView = (strpos($currentRoute, 'admin/') === 0);
    ?>

    <!-- Public / Member Navigation Header -->
    <?php if (!$isAdminView): ?>
    <nav class="navbar navbar-expand-lg py-3 sticky-top" style="background-color: var(--nav-bg); border-bottom: 1px solid var(--border-color); z-index: 1020;">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center fw-bold text-primary fs-4" href="<?php echo BASE_URL; ?>/index.php?route=home">
                <i class="fa-solid fa-book-open me-2 text-primary"></i>
                <span class="text-dark-theme-override" style="color: var(--text-color);">BiblioTech</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-4">
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3 <?php echo $currentRoute === 'home' ? 'text-primary' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php?route=home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3 <?php echo $currentRoute === 'books' ? 'text-primary' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php?route=books">Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3 <?php echo $currentRoute === 'about' ? 'text-primary' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php?route=about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium px-3 <?php echo $currentRoute === 'contact' ? 'text-primary' : ''; ?>" href="<?php echo BASE_URL; ?>/index.php?route=contact">Contact</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    
                    <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle dark mode">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                    
                    <?php if (Auth::isMember()): 
                        $cartModel = new Cart();
                        $cartCount = $cartModel->countItems(Auth::getMemberId());
                    ?>
                        <a href="<?php echo BASE_URL; ?>/index.php?route=member/cart" class="theme-toggle-btn position-relative me-1 text-decoration-none" aria-label="View payment cart">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <?php if ($cartCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.25em 0.5em; transform: translate(-30%, -30%) !important;">
                                    <?php echo $cartCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (Auth::isLoggedIn()): ?>
                        <div class="dropdown">
                            <a class="btn btn-outline-primary dropdown-toggle fw-medium px-4 d-flex align-items-center gap-2" href="#" role="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-user-circle fs-5"></i>
                                <?php echo htmlspecialchars(Auth::getMemberName() ?? Auth::getUserEmail()); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 p-2 glass-card" aria-labelledby="userMenuDropdown" style="border-radius: 12px; min-width: 200px;">
                                <li>
                                    <div class="px-3 py-2 text-muted small border-bottom mb-2 pb-2">
                                        Logged as <strong class="text-primary"><?php echo ucfirst(Auth::getUserRole()); ?></strong>
                                    </div>
                                </li>
                                <?php if (Auth::isAdmin()): ?>
                                    <li><a class="dropdown-item py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard"><i class="fa-solid fa-chart-line me-2"></i>Admin Area</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?route=member/dashboard"><i class="fa-solid fa-columns me-2"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?route=member/borrowings"><i class="fa-solid fa-clock-rotate-left me-2"></i>Borrowings</a></li>
                                    <li><a class="dropdown-item py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?route=member/cart"><i class="fa-solid fa-cart-shopping me-2"></i>My Cart</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?route=profile"><i class="fa-solid fa-user-pen me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?route=change-password"><i class="fa-solid fa-key me-2"></i>Change Password</a></li>
                                <li><hr class="dropdown-divider my-2"></li>
                                <li><a class="dropdown-item text-danger py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?action=logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/index.php?route=login" class="btn btn-link text-decoration-none fw-medium text-dark-theme-override px-2" style="color: var(--text-color);">Sign In</a>
                        <a href="<?php echo BASE_URL; ?>/index.php?route=register" class="btn btn-primary fw-medium px-4" style="border-radius: 10px; background: var(--primary-gradient); border: none;">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <?php else: ?>
    <!-- Admin Top Navbar -->
    <nav class="navbar navbar-expand-lg py-3 sticky-top shadow-sm" style="background-color: var(--nav-bg); border-bottom: 1px solid var(--border-color); z-index: 1010; margin-left: 0;">
        <div class="container-fluid px-4">
            <button class="btn btn-outline-secondary d-lg-none me-3" type="button" id="sidebarCollapseBtn">
                <i class="fa-solid fa-bars"></i>
            </button>
            
            <h4 class="mb-0 fw-semibold text-primary d-none d-sm-block">
                <?php echo htmlspecialchars($pageTitle); ?>
            </h4>
            
            <div class="d-flex align-items-center gap-3 ms-auto">
                <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle dark mode">
                    <i class="fa-solid fa-moon"></i>
                </button>
                
                <div class="dropdown">
                    <a class="btn btn-link nav-link text-decoration-none dropdown-toggle d-flex align-items-center gap-2 p-0 text-dark-theme-override" style="color: var(--text-color);" href="#" role="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user-shield text-primary fs-4"></i>
                        <span class="fw-medium d-none d-md-inline"><?php echo htmlspecialchars(Auth::getUserEmail()); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 p-2 glass-card" aria-labelledby="adminDropdown" style="border-radius: 12px; min-width: 200px;">
                        <li><div class="px-3 py-2 text-muted small border-bottom mb-2 pb-2">Admin Dashboard</div></li>
                        <li><a class="dropdown-item py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?route=home"><i class="fa-solid fa-house me-2"></i>Library Front</a></li>
                        <li><a class="dropdown-item py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?route=profile"><i class="fa-solid fa-user-pen me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?route=change-password"><i class="fa-solid fa-key me-2"></i>Change Password</a></li>
                        <li><hr class="dropdown-divider my-2"></li>
                        <li><a class="dropdown-item text-danger py-2 px-3 rounded" href="<?php echo BASE_URL; ?>/index.php?action=logout"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Simulation Notification Alert Drawer -->
    <?php if (Session::hasFlash('email_sim')): ?>
        <div class="email-sim-toast position-fixed bottom-0 end-0 m-4 p-3 shadow-lg border-start border-4 border-warning glass-card" style="z-index: 2000; max-width: 400px; transition: transform 0.4s ease-in-out;">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex gap-2">
                    <i class="fa-solid fa-envelope-open-text text-warning fs-4 mt-1"></i>
                    <div>
                        <h6 class="fw-semibold mb-1">Email Simulated Notice</h6>
                        <div class="small text-muted"><?php echo Session::getFlash('email_sim'); ?></div>
                    </div>
                </div>
                <button type="button" class="btn-close ms-2" onclick="this.parentElement.parentElement.style.transform='translateX(120%)'"></button>
            </div>
        </div>
    <?php endif; ?>
