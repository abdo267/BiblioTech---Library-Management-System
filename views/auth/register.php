<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}
?>

<div class="container my-5 py-3 fade-in-up flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="card card-custom border-0 shadow-lg glass-card overflow-hidden" style="max-width: 540px; width: 100%;">
        <div class="p-4 p-md-5">
            <div class="text-center mb-4">
                <a class="d-flex align-items-center justify-content-center fw-extrabold text-primary fs-3 text-decoration-none mb-3" href="<?php echo BASE_URL; ?>/index.php?route=home">
                    <i class="fa-solid fa-book-open me-2 text-primary fs-2"></i>
                    <span style="color: var(--text-color);">BiblioTech</span>
                </a>
                <h4 class="fw-bold text-dark-theme-override" style="color: var(--text-color);">Create Account</h4>
                <p class="text-muted small">Join BiblioTech Library today to start borrowing books.</p>
            </div>

            <!-- Register Form -->
            <form action="<?php echo BASE_URL; ?>/index.php?action=register" method="POST">
                <div class="mb-3">
                    <label for="regName" class="form-label small fw-medium">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-regular fa-user"></i></span>
                        <input type="text" class="form-control form-control-custom border-start-0" id="regName" name="full_name" placeholder="John Doe" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="regEmail" class="form-label small fw-medium">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                        <input type="email" class="form-control form-control-custom border-start-0" id="regEmail" name="email" placeholder="john@example.com" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="regPass" class="form-label small fw-medium">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                            <input type="password" class="form-control form-control-custom border-start-0" id="regPass" name="password" placeholder="At least 6 chars" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="regConfirm" class="form-label small fw-medium">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-solid fa-shield"></i></span>
                            <input type="password" class="form-control form-control-custom border-start-0" id="regConfirm" name="confirm_password" placeholder="Re-type password" required>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label for="regPhone" class="form-label small fw-medium">Phone (Optional)</label>
                        <input type="text" class="form-control form-control-custom" id="regPhone" name="phone" placeholder="+12345678">
                    </div>
                    <div class="col-sm-6">
                        <label for="regAddress" class="form-label small fw-medium">Address (Optional)</label>
                        <input type="text" class="form-control form-control-custom" id="regAddress" name="address" placeholder="Cairo, Egypt">
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary fw-medium py-2" style="border-radius: 10px; background: var(--primary-gradient); border: none;">
                        <i class="fa-solid fa-user-plus me-2"></i>Create Account
                    </button>
                </div>

                <div class="text-center mt-4">
                    <span class="small text-muted">Already have an account? <a href="<?php echo BASE_URL; ?>/index.php?route=login" class="fw-medium text-decoration-none text-primary">Sign In</a></span>
                </div>
            </form>
        </div>
    </div>
</div>
