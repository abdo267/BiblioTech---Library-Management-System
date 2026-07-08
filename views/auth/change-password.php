<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}
?>

<div class="container my-5 py-5 fade-in-up flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="card card-custom border-0 shadow-lg glass-card overflow-hidden" style="max-width: 480px; width: 100%;">
        <div class="p-4 p-md-5">
            <div class="text-center mb-4">
                <i class="fa-solid fa-key text-primary fs-1 mb-3"></i>
                <h4 class="fw-bold text-dark-theme-override" style="color: var(--text-color);">Change Password</h4>
                <p class="text-muted small">Update your account credentials to keep your profile secure.</p>
            </div>

            <!-- Change Password Form-->
            <form action="<?php echo BASE_URL; ?>/index.php?action=change-password" method="POST">
                <div class="mb-3">
                    <label for="currentPass" class="form-label small fw-medium">Current Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-solid fa-lock-open"></i></span>
                        <input type="password" class="form-control form-control-custom border-start-0" id="currentPass" name="current_password" placeholder="Enter current password" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="newPass" class="form-label small fw-medium">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control form-control-custom border-start-0" id="newPass" name="new_password" placeholder="At least 6 characters" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirmNewPass" class="form-label small fw-medium">Confirm New Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light-custom border-end-0 text-muted"><i class="fa-solid fa-shield"></i></span>
                        <input type="password" class="form-control form-control-custom border-start-0" id="confirmNewPass" name="confirm_new_password" placeholder="Re-type new password" required>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary fw-medium py-2" style="border-radius: 10px; background: var(--primary-gradient); border: none;">
                        <i class="fa-solid fa-circle-check me-2"></i>Update Password
                    </button>
                </div>

                <div class="text-center mt-3">
                    <a href="<?php echo BASE_URL; ?>/index.php?route=profile" class="fw-medium text-decoration-none text-primary small">Back to Profile</a>
                </div>
            </form>
        </div>
    </div>
</div>
