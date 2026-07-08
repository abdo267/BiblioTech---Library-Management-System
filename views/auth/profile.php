<?php

if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

// Load current user details
$userModel = new User();
$memberModel = new Member();

$user = $userModel->findById(Auth::getUserId());
$member = null;

if (Auth::isMember()) {
    $member = $memberModel->findByUserId(Auth::getUserId());
}
?>

<div class="container py-5 fade-in-up">
  
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=home">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Profile</li>
        </ol>
    </nav>

    <div class="row g-4 justify-content-center">
        <!-- Account Info Card -->
        <div class="col-lg-4">
            <div class="card card-custom border-0 shadow-sm text-center p-4 h-100">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="fs-1 text-primary bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                    </div>
                    
                    <h4 class="fw-bold mb-1 text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars(Auth::getMemberName() ?: 'Administrator'); ?></h4>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-4 fs-6 px-3"><?php echo ucfirst(Auth::getUserRole()); ?></span>
                    
                    <div class="border-top pt-4 text-start">
                        <div class="mb-3">
                            <span class="text-muted small d-block">Email Address</span>
                            <span class="fw-medium text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        
                        <?php if ($member): ?>
                            <div class="mb-3">
                                <span class="text-muted small d-block">Member Since</span>
                                <span class="fw-medium text-dark-theme-override" style="color: var(--text-color);"><?php echo date('F d, Y', strtotime($member['registration_date'])); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="mb-0">
                            <span class="text-muted small d-block">Account Status</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-check-double me-1"></i>Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-lg-6">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <div class="card-body">
                    <h4 class="fw-bold mb-4"><i class="fa-solid fa-user-pen me-2 text-primary"></i>Profile Details</h4>
                    
                    <?php if (Auth::isMember() && $member): ?>
                        <form action="<?php echo BASE_URL; ?>/index.php?action=update-profile" method="POST">
                            <div class="mb-3">
                                <label for="profName" class="form-label small fw-medium">Full Name</label>
                                <input type="text" class="form-control form-control-custom" id="profName" name="full_name" value="<?php echo htmlspecialchars($member['full_name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="profPhone" class="form-label small fw-medium">Phone Number</label>
                                <input type="text" class="form-control form-control-custom" id="profPhone" name="phone" value="<?php echo htmlspecialchars($member['phone'] ?: ''); ?>" placeholder="No phone number registered">
                            </div>

                            <div class="mb-4">
                                <label for="profAddr" class="form-label small fw-medium">Residential Address</label>
                                <textarea class="form-control form-control-custom" id="profAddr" name="address" rows="3" placeholder="No address registered"><?php echo htmlspecialchars($member['address'] ?: ''); ?></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary fw-medium py-2" style="border-radius: 10px; background: var(--primary-gradient); border: none;">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Save Profile Changes
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <!-- Admin Profile Details -->
                        <div class="py-5 text-center text-muted">
                            <i class="fa-solid fa-lock fs-1 mb-3"></i>
                            <h5>Administrator Access</h5>
                            <p class="small">Librarian credential parameters are managed securely in the configuration layers. Please use the "Change Password" link below to update credentials.</p>
                        </div>
                    <?php endif; ?>

                    <div class="border-top mt-4 pt-3 text-center">
                        <a href="<?php echo BASE_URL; ?>/index.php?route=change-password" class="btn btn-outline-primary btn-sm fw-medium px-4" style="border-radius: 8px;">
                            <i class="fa-solid fa-key me-2"></i>Change Account Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
