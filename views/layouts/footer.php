<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$currentRoute = $_GET['route'] ?? 'home';
$isAdminView = (strpos($currentRoute, 'admin/') === 0);
?>

    <?php if (!$isAdminView): ?>
    <!-- Public Footer -->
    <footer class="mt-auto py-5">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4 col-md-6">
                    <h5 class="fw-bold text-primary mb-3">
                        <i class="fa-solid fa-book-open me-2"></i>BiblioTech
                    </h5>
                    <p class="text-muted">A state-of-the-art Library Management System built with PHP, PDO MySQL, HTML5, CSS3, JavaScript and Bootstrap 5.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-github"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <h6 class="fw-bold text-uppercase mb-3 tracking-wider text-xs">Quick Links</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="<?php echo BASE_URL; ?>/index.php?route=home" class="text-decoration-none text-muted">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/index.php?route=books" class="text-decoration-none text-muted">Browse Books</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/index.php?route=about" class="text-decoration-none text-muted">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/index.php?route=contact" class="text-decoration-none text-muted">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-3 col-6">
                    <h6 class="fw-bold text-uppercase mb-3 tracking-wider text-xs">Member Area</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <?php if (Auth::isLoggedIn()): ?>
                            <li><a href="<?php echo BASE_URL; ?>/index.php?route=profile" class="text-decoration-none text-muted">My Profile</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/index.php?route=member/dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/index.php?route=member/borrowings" class="text-decoration-none text-muted">My Borrowings</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>/index.php?route=login" class="text-decoration-none text-muted">Sign In</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/index.php?route=register" class="text-decoration-none text-muted">Create Account</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold text-uppercase mb-3 tracking-wider text-xs">Support & Contact</h6>
                    <p class="text-muted mb-2"><i class="fa-solid fa-location-dot me-2 text-primary"></i> 123 Education Ave, Cairo, Egypt</p>
                    <p class="text-muted mb-2"><i class="fa-solid fa-phone me-2 text-primary"></i> +20 123 456 7890</p>
                    <p class="text-muted mb-0"><i class="fa-solid fa-envelope me-2 text-primary"></i> support@bibliotech.com</p>
                </div>
            </div>
            
            <hr class="my-4 text-muted">
            
            <div class="row align-items-center justify-content-between">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted small">&copy; <?php echo date('Y'); ?> BiblioTech. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <a href="#" class="text-decoration-none text-muted small me-3">Privacy Policy</a>
                    <a href="#" class="text-decoration-none text-muted small">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
    <?php else: ?>
    <!-- Admin Footer -->
    <footer class="py-3 px-4 text-center mt-auto" style="border-top: 1px solid var(--border-color); background: var(--nav-bg);">
        <p class="mb-0 text-muted small">&copy; <?php echo date('Y'); ?> BiblioTech Admin Portal. All rights reserved.</p>
    </footer>
    <?php endif; ?>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
   
    <?php if ($route === 'admin/dashboard' || $route === 'admin/reports' || $route === 'member/dashboard'): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>

    
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>

    <!-- Alert Flash Notification Binder Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const alertBg = isDark ? '#1e293b' : '#ffffff';
            const alertColor = isDark ? '#f1f5f9' : '#1e293b';

            <?php if (Session::hasFlash('success')): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?php echo addslashes(Session::getFlash("success")); ?>',
                    timer: 4000,
                    showConfirmButton: false,
                    background: alertBg,
                    color: alertColor
                });
            <?php endif; ?>

            <?php if (Session::hasFlash('error')): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?php echo addslashes(Session::getFlash("error")); ?>',
                    background: alertBg,
                    color: alertColor,
                    confirmButtonColor: '#4f46e5'
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>
