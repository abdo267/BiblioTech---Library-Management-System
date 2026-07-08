<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}
?>

<div class="container my-5 py-5 fade-in-up flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="card card-custom border-0 shadow-lg glass-card overflow-hidden" style="max-width: 480px; width: 100%;">
        <div class="p-4 p-md-5">
            <div class="text-center mb-4">
                <a class="d-flex align-items-center justify-content-center fw-extrabold text-primary fs-3 text-decoration-none mb-3" href="<?php echo BASE_URL; ?>/index.php?route=home">
                    <i class="fa-solid fa-book-open me-2 text-primary fs-2"></i>
                    <span style="color: var(--text-color);">BiblioTech</span>
                </a>
                <h4 class="fw-bold text-dark-theme-override" style="color: var(--text-color);">Welcome Back!</h4>
                <p class="text-muted small">Access your account to manage your borrowings.</p>
            </div>

            <!-- Login Form -->
            <form action="<?php echo BASE_URL; ?>/index.php?action=login" method="POST">
                <div class="mb-3">
                    <label for="loginEmail" class="form-label small fw-medium">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                        <input type="email" class="form-control form-control-custom border-start-0" id="loginEmail" name="email" placeholder="name@example.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="loginPassword" class="form-label small fw-medium mb-0">Password</label>
                        <a href="<?php echo BASE_URL; ?>/index.php?route=forgot-password" class="small text-decoration-none fw-medium text-primary">Forgot Password?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control form-control-custom border-start-0" id="loginPassword" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary fw-medium py-2" style="border-radius: 10px; background: var(--primary-gradient); border: none;">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
                    </button>
                </div>

                <div class="text-center mt-4">
                    <span class="small text-muted">Don't have an account? <a href="<?php echo BASE_URL; ?>/index.php?route=register" class="fw-medium text-decoration-none text-primary">Sign Up</a></span>
                </div>
            </form>
        </div>
    </div>
</div>
