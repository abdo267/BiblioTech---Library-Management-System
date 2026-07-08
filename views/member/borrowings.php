<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$memberId = Auth::getMemberId();
if (!$memberId) {
    echo '<div class="alert alert-danger">Error: Member profile not found. Please contact administration.</div>';
    exit;
}

$borrowingModel  = new Borrowing();
$myBorrowings    = $borrowingModel->getHistoryByMember($memberId);
$cartModel       = new Cart();
$overdueModel    = new OverdueFineCharge();
$today           = time();
?>

<div class="container py-5 fade-in-up">
 
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=home">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=member/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Borrowing History</li>
        </ol>
    </nav>

    <div class="card card-custom border-0 shadow-sm p-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Borrowing History</h3>
                <div class="d-flex gap-2 align-items-center">
                    <?php
                    $cartCount = $cartModel->countItems($memberId);
                    if ($cartCount > 0):
                    ?>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=member/cart" class="btn btn-warning btn-sm fw-bold rounded-pill px-3">
                        <i class="fa-solid fa-cart-shopping me-1"></i>Pay Fines (<?php echo $cartCount; ?>)
                    </a>
                    <?php endif; ?>
                    <span class="badge bg-primary fs-6 px-3 py-2"><?php echo count($myBorrowings); ?> Records</span>
                </div>
            </div>

            <?php if (empty($myBorrowings)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 mb-3"></i>
                    <h5 class="fw-bold">No Borrowing Records</h5>
                    <p class="small">You haven't borrowed any books from our catalog yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive table-responsive-custom">
                    <table class="table table-custom align-middle datatable-custom w-100">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th>Fine</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myBorrowings as $row): ?>
                                <?php
                                $isOverdue     = ($row['status'] === 'borrowed' && strtotime($row['due_date']) < $today);
                                $daysOverdue   = $isOverdue ? ceil(($today - strtotime($row['due_date'])) / 86400) : 0;
                                $liveFine      = $isOverdue ? ($daysOverdue * FINE_RATE_PER_DAY) : 0;

                                // Check if this active overdue book already has an unpaid charge in the cart
                                $existingCharge = $isOverdue ? $overdueModel->getByBorrowingId($row['id']) : null;
                                $chargeInCart   = $existingCharge ? true : false;
                                ?>
                                <tr <?php echo $isOverdue ? 'class="table-danger-subtle"' : ''; ?>>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($row['cover_image']) && file_exists(UPLOAD_PATH . '/' . $row['cover_image'])): ?>
                                                <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $row['cover_image']; ?>" class="rounded shadow-sm" style="width: 38px; height: 52px; object-fit: cover;" alt="">
                                            <?php elseif (!empty($row['cover_image']) && strpos($row['cover_image'], 'http') === 0): ?>
                                                <img src="<?php echo $row['cover_image']; ?>" class="rounded shadow-sm" style="width: 38px; height: 52px; object-fit: cover;" alt="">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 52px;"><i class="fa-solid fa-book small"></i></div>
                                            <?php endif; ?>
                                            <div>
                                                <span class="fw-bold d-block text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['book_title']); ?></span>
                                                <span class="text-muted small">ISBN: <?php echo htmlspecialchars($row['isbn'] ?: 'N/A'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['borrow_date'])); ?></td>
                                    <td>
                                        <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ''; ?>">
                                            <?php echo date('M d, Y', strtotime($row['due_date'])); ?>
                                        </span>
                                        <?php if ($isOverdue): ?>
                                            <span class="d-block text-danger small fw-medium">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i><?php echo $daysOverdue; ?> day(s) overdue
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($row['return_date']) {
                                            echo date('M d, Y', strtotime($row['return_date']));
                                        } else {
                                            echo '<span class="text-muted small">Not returned</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'returned'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Returned</span>
                                        <?php elseif ($isOverdue): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Overdue</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fa-solid fa-clock me-1"></i>Borrowed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isOverdue): ?>
                                            <!--growing fine for active overdue books-->
                                            <div class="text-danger fw-bold">
                                                EGP <?php echo number_format($liveFine, 2); ?>
                                            </div>
                                            <div class="text-muted small"><?php echo $daysOverdue; ?> × EGP <?php echo number_format(FINE_RATE_PER_DAY, 0); ?>/day</div>
                                            <?php if ($chargeInCart): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle d-inline-block mt-1 small">
                                                    <i class="fa-solid fa-cart-shopping me-1"></i>In Cart
                                                </span>
                                            <?php endif; ?>
                                        <?php elseif ($row['fine_amount'] > 0): ?>
                                            <span class="text-danger fw-bold">EGP <?php echo number_format($row['fine_amount'], 2); ?></span>
                                            <?php if ($row['payment_status'] === 'paid'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle d-block mt-1 small">Paid</span>
                                            <?php elseif ($row['payment_status'] === 'waived'): ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle d-block mt-1 small">Waived</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle d-block mt-1 small">Unpaid</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">EGP 0.00</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($row['status'] === 'borrowed'): ?>
                                            <div class="d-flex flex-column gap-1 align-items-end">
                                                <?php if ($isOverdue): ?>
                                                    <!-- Charge Fine button-->
                                                    <?php if ($chargeInCart): ?>
                                                        <a href="<?php echo BASE_URL; ?>/index.php?route=member/cart" class="btn btn-sm btn-warning rounded-pill px-3 fw-medium">
                                                            <i class="fa-solid fa-cart-shopping me-1"></i>Pay Fine
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo BASE_URL; ?>/index.php?action=charge-overdue-fine&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger rounded-pill px-3 fw-medium">
                                                            <i class="fa-solid fa-bolt me-1"></i>Charge &amp; Pay Fine
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <!-- Return Book button -->
                                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-medium"
                                                        onclick="window.confirmAction('Return Book', 'Return \'<?php echo addslashes($row['book_title']); ?>\'?<?php echo $isOverdue ? ' Note: Your overdue fine will be settled.' : ''; ?>', 'Return', '<?php echo BASE_URL; ?>/index.php?action=member-return-book&id=<?php echo $row['id']; ?>')">
                                                    <i class="fa-solid fa-arrow-rotate-left me-1"></i>Return
                                                </button>
                                            </div>
                                        <?php elseif ($row['status'] === 'returned' && $row['fine_amount'] > 0 && $row['payment_status'] === 'unpaid'): ?>
                                            <?php if ($cartModel->isInCart($memberId, $row['return_id'])): ?>
                                                <a href="<?php echo BASE_URL; ?>/index.php?action=remove-from-cart&return_id=<?php echo $row['return_id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    <i class="fa-solid fa-cart-arrow-down me-1"></i>Remove
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo BASE_URL; ?>/index.php?action=add-to-cart&return_id=<?php echo $row['return_id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                                    <i class="fa-solid fa-cart-plus me-1"></i>Pay Fine
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Overdue Alert Banner -->
                <?php
                $overdueActiveCount = 0;
                $totalLiveFines = 0;
                foreach ($myBorrowings as $row) {
                    if ($row['status'] === 'borrowed' && strtotime($row['due_date']) < $today) {
                        $overdueActiveCount++;
                        $daysOv = ceil(($today - strtotime($row['due_date'])) / 86400);
                        $totalLiveFines += $daysOv * FINE_RATE_PER_DAY;
                    }
                }
                if ($overdueActiveCount > 0):
                ?>
                <div class="alert border-0 rounded-3 mt-4 p-4 d-flex align-items-center gap-3"
                     style="background: linear-gradient(135deg,#fef2f2,#fee2e2); color: #991b1b;">
                    <i class="fa-solid fa-circle-exclamation fs-3"></i>
                    <div>
                        <div class="fw-bold mb-1">
                            <?php echo $overdueActiveCount; ?> overdue book<?php echo $overdueActiveCount > 1 ? 's' : ''; ?> —
                            Total accrued fine: <strong>EGP <?php echo number_format($totalLiveFines, 2); ?></strong>
                        </div>
                        <div class="small">Fines accumulate at <strong>EGP <?php echo number_format(FINE_RATE_PER_DAY, 0); ?> per day</strong> past the due date.
                            Click <strong>"Charge &amp; Pay Fine"</strong> to settle your overdue fine online now.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
