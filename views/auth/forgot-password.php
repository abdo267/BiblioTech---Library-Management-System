<?php

if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$token = $_GET['token'] ?? null;
$isValidToken = false;
$userRow = null;

if ($token) {
    
    $userModel = new User();
    $userRow = $userModel->findByResetToken($token);
    if ($userRow) {
        $isValidToken = true;
    }
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
                
                <?php if ($token): ?>
                    <h4 class="fw-bold text-dark-theme-override" style="color: var(--text-color);">Set New Password</h4>
                    <p class="text-muted small">Enter your new secure account password below.</p>
                <?php else: ?>
                    <h4 class="fw-bold text-dark-theme-override" style="color: var(--text-color);">Recover Password</h4>
                    <p class="text-muted small">Enter email to simulate a password recovery link link.</p>
                <?php endif; ?>
            </div>

            <?php if ($token): ?>
                <?php if ($isValidToken): ?>
                    <!-- Form to Enter New Password-->
                    <form action="<?php echo BASE_URL; ?>/index.php?action=forgot-password" method="POST">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="mb-3">
                            <label for="resetPass" class="form-label small fw-medium">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                <input type="password" class="form-control form-control-custom border-start-0" id="resetPass" name="password" placeholder="At least 6 characters" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="resetConfirm" class="form-label small fw-medium">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-solid fa-shield"></i></span>
                                <input type="password" class="form-control form-control-custom border-start-0" id="resetConfirm" name="confirm_password" placeholder="Re-enter password" required>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary fw-medium py-2" style="border-radius: 10px; background: var(--primary-gradient); border: none;">
                                <i class="fa-solid fa-circle-check me-2"></i>Reset Password
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- Invalid Token Message -->
                    <div class="alert alert-danger text-center p-3 mb-4 rounded-3">
                        <i class="fa-solid fa-triangle-exclamation fs-3 mb-2 d-block"></i>
                        <span class="fw-semibold">Invalid or Expired Token</span>
                        <p class="small mb-0 mt-1">This recovery link is invalid, has expired, or has already been used.</p>
                    </div>
                    <div class="d-grid">
                        <a href="<?php echo BASE_URL; ?>/index.php?route=forgot-password" class="btn btn-outline-primary fw-medium">Request New Link</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Form to Request Recovery Link-->
                <form action="<?php echo BASE_URL; ?>/index.php?action=forgot-password" method="POST">
                    <div class="mb-4">
                        <label for="forgotEmail" class="form-label small fw-medium">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                            <input type="email" class="form-control form-control-custom border-start-0" id="forgotEmail" name="email" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary fw-medium py-2" style="border-radius: 10px; background: var(--primary-gradient); border: none;">
                            <i class="fa-solid fa-paper-plane me-2"></i>Send Reset Link
                        </button>
                    </div>

                    <div class="text-center mt-4">
                        <a href="<?php echo BASE_URL; ?>/index.php?route=login" class="fw-medium text-decoration-none text-primary small"><i class="fa-solid fa-arrow-left me-1"></i>Back to Sign In</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
