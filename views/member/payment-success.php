<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$memberId = Auth::getMemberId();
$paymentId = intval($_GET['id'] ?? 0);

$paymentModel = new Payment();
$payment = $paymentModel->findById($paymentId);

if (!$payment || $payment['member_id'] != $memberId) {
    echo '<div class="alert alert-danger m-5">Invalid payment confirmation record.</div>';
    exit;
}

$items = $paymentModel->getItemsByPaymentId($paymentId);
?>

<div class="container py-5 fade-in-up text-center" style="max-width: 600px;">
  
    <div class="mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle shadow-sm" style="width: 90px; height: 90px;">
            <i class="fa-solid fa-circle-check display-3"></i>
        </div>
    </div>

    <h2 class="fw-bold mb-2">Payment Settled!</h2>
    <p class="text-muted mb-5">Your outstanding library fines have been paid off successfully.</p>

    <!-- Receipt Information Card -->
    <div class="card card-custom border-0 shadow-sm p-4 text-start mb-4">
        <h5 class="fw-bold mb-4 border-bottom pb-2">Receipt Details</h5>
        
        <div class="row g-3 mb-4">
            <div class="col-6">
                <span class="text-muted small d-block">Transaction Date</span>
                <span class="fw-medium text-dark-theme-override" style="color: var(--text-color);"><?php echo date('M d, Y h:i A', strtotime($payment['paid_at'] ?? $payment['created_at'])); ?></span>
            </div>
            <div class="col-6">
                <span class="text-muted small d-block">Payment Method</span>
                <span class="fw-medium text-dark-theme-override" style="color: var(--text-color);"><i class="fa-regular fa-credit-card me-1"></i>Credit Card</span>
            </div>
            <div class="col-6">
                <span class="text-muted small d-block">Transaction ID</span>
                <span class="fw-medium text-dark-theme-override" style="color: var(--text-color); font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($payment['stripe_payment_intent_id']); ?></span>
            </div>
            <div class="col-6">
                <span class="text-muted small d-block">Status</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-check-circle me-1"></i>Succeeded</span>
            </div>
        </div>

        <h6 class="fw-bold mb-3 border-bottom pb-1">Fines Paid</h6>
        <div class="table-responsive">
            <table class="table table-sm table-borderless align-middle mb-0">
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="ps-0 flex-grow-1">
                                <span class="fw-bold text-dark-theme-override text-truncate d-inline-block" style="max-width: 250px; color: var(--text-color);" title="<?php echo htmlspecialchars($item['book_title']); ?>">
                                    <?php echo htmlspecialchars($item['book_title']); ?>
                                </span>
                            </td>
                            <td class="text-muted text-nowrap small text-end">
                                Return Fine
                            </td>
                            <td class="pe-0 text-end fw-bold text-danger text-nowrap">
                                    EGP <?php echo number_format($item['amount'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="border-top">
                        <td colspan="2" class="ps-0 fw-bold text-dark-theme-override" style="color: var(--text-color);">Total Paid</td>
                        <td class="pe-0 text-end fw-bold text-primary fs-5">EGP <?php echo number_format($payment['amount'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Actions Links -->
    <div class="d-flex gap-3 justify-content-center">
        <a href="<?php echo BASE_URL; ?>/index.php?route=member/dashboard" class="btn btn-outline-primary rounded-pill px-4">
            <i class="fa-solid fa-columns me-2"></i>Go to Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>/index.php?route=member/borrowings" class="btn btn-primary rounded-pill px-4" style="background: var(--primary-gradient); border: none;">
            <i class="fa-solid fa-clock-rotate-left me-2"></i>My Borrowings
        </a>
    </div>
</div>
