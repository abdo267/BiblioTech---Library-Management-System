<?php

if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$memberId = Auth::getMemberId();
$cartModel = new Cart();
$cartItems = $cartModel->getItemsByMember($memberId);
$total = $cartModel->getTotal($memberId);
?>

<div class="container py-5 fade-in-up">

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=home">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=member/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Cart</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Cart Items List-->
        <div class="col-lg-8">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h3 class="fw-bold mb-0">
                        <i class="fa-solid fa-cart-shopping text-primary me-2"></i>Payment Cart
                    </h3>
                    <span class="badge bg-primary-subtle text-primary fs-6 px-3 py-2 rounded-pill"><?php echo count($cartItems); ?> Fine(s)</span>
                </div>

                <?php if (empty($cartItems)): ?>
                    <div class="text-center py-5 my-5 text-muted">
                        <div class="display-3 text-muted opacity-25 mb-3"><i class="fa-solid fa-cart-flatbed"></i></div>
                        <h4 class="fw-bold text-dark-theme-override">Your Cart is Empty</h4>
                        <p class="small mb-4">You have no pending library fines added to your payment cart.</p>
                        <a href="<?php echo BASE_URL; ?>/index.php?route=member/borrowings" class="btn btn-primary rounded-pill px-4">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>My Borrowings
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">Fines added for overdue books</span>
                        <a href="<?php echo BASE_URL; ?>/index.php?action=clear-cart" class="btn btn-sm btn-outline-danger border-0 rounded-pill px-3" onclick="return confirm('Clear all items from your cart?')">
                            <i class="fa-solid fa-trash-can me-1"></i>Clear Cart
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Dates</th>
                                    <th class="text-end">Fine</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <?php if (!empty($item['cover_image']) && file_exists(UPLOAD_PATH . '/' . $item['cover_image'])): ?>
                                                    <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $item['cover_image']; ?>" class="rounded shadow-sm" style="width: 40px; height: 55px; object-fit: cover;" alt="">
                                                <?php elseif (!empty($item['cover_image']) && strpos($item['cover_image'], 'http') === 0): ?>
                                                    <img src="<?php echo $item['cover_image']; ?>" class="rounded shadow-sm" style="width: 40px; height: 55px; object-fit: cover;" alt="">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 55px;"><i class="fa-solid fa-image small" style="font-size: 0.75rem;"></i></div>
                                                <?php endif; ?>
                                                <div>
                                                    <span class="fw-bold d-block text-dark-theme-override" style="color: var(--text-color); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($item['book_title']); ?></span>
                                                    <span class="text-muted small">ISBN: <?php echo htmlspecialchars($item['isbn'] ?: 'N/A'); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <span class="text-muted">Due:</span> <?php echo date('M d, Y', strtotime($item['due_date'])); ?><br>
                                                <span class="text-muted">Returned:</span> <?php echo date('M d, Y', strtotime($item['book_return_date'])); ?>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-danger fw-bold">EGP <?php echo number_format($item['fine_amount'], 2); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?php echo BASE_URL; ?>/index.php?action=remove-from-cart&return_id=<?php echo $item['return_id']; ?>" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width: 28px; height: 28px; line-height: 1;" title="Remove Item">
                                                <i class="fa-solid fa-xmark text-xs" style="font-size: 0.75rem;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Summary & Checkout-->
        <div class="col-lg-4">
            <div class="card card-custom border-0 shadow-sm p-4 bg-light-custom">
                <h4 class="fw-bold mb-4 border-bottom pb-2">Order Summary</h4>
                
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span>Fines Total</span>
                    <span>EGP <?php echo number_format($total, 2); ?></span>
                </div>
                
                <div class="d-flex justify-content-between mb-3 text-muted">
                    <span>Processing Fee</span>
                    <span class="text-success">Free</span>
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold text-dark-theme-override" style="color: var(--text-color);">Total Amount</span>
                    <span class="fw-bold text-primary fs-4">EGP <?php echo number_format($total, 2); ?></span>
                </div>

                <?php if ($total > 0): ?>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=member/checkout" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm" style="background: var(--primary-gradient); border: none;">
                        <i class="fa-solid fa-credit-card me-2"></i>Proceed to Checkout
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100 rounded-pill py-3 fw-bold" disabled>
                        <i class="fa-solid fa-credit-card me-2"></i>Proceed to Checkout
                    </button>
                <?php endif; ?>

                <div class="text-center mt-3 text-muted" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-lock me-1"></i>Secure credit card transaction with Stripe
                </div>
            </div>
        </div>
    </div>
</div>
