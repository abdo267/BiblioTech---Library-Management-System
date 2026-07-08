<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$reqModel = new BorrowRequest();
$borrowModel = new Borrowing();

$pendingReqs = $reqModel->getPendingRequests();
$allBorrowings = $borrowModel->getAllBorrowings();
?>

<div class="fade-in-up">
 
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Circulation Desk</li>
        </ol>
    </nav>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom mb-4" id="circulationTab" role="tablist" style="border-bottom: 2px solid var(--border-color);">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold px-4 py-3" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="true" style="border: none; border-bottom: 3px solid transparent;">
                <i class="fa-solid fa-hourglass-half me-2"></i>Pending Requests 
                <span class="badge bg-warning text-dark ms-2"><?php echo count($pendingReqs); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-4 py-3" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button" role="tab" aria-controls="loans" aria-selected="false" style="border: none; border-bottom: 3px solid transparent;">
                <i class="fa-solid fa-book-bookmark me-2"></i>Active Loans & History
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="circulationTabContent">
        
        <!-- Tab 1: Pending Borrow Requests -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Pending Requests Approval</h5>
                    
                    <?php if (empty($pendingReqs)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-circle-check fs-1 text-success mb-3"></i>
                            <h5 class="fw-bold">No Pending Requests</h5>
                            <p class="small">All reader requests have been processed successfully.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive table-responsive-custom">
                            <table class="table table-custom align-middle datatable-custom w-100">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Member</th>
                                        <th>Book Requested</th>
                                        <th>Stock Copies</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingReqs as $row): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y H:i', strtotime($row['request_date'])); ?></td>
                                            <td>
                                                <strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['member_name']); ?></strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if (!empty($row['cover_image']) && file_exists(UPLOAD_PATH . '/' . $row['cover_image'])): ?>
                                                        <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $row['cover_image']; ?>" class="rounded" style="width: 30px; height: 40px; object-fit: cover;" alt="">
                                                    <?php else: ?>
                                                        <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 40px;"><i class="fa-solid fa-image small" style="font-size: 0.6rem;"></i></div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <span class="fw-bold d-block text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['book_title']); ?></span>
                                                        <span class="text-muted small">ISBN: <?php echo htmlspecialchars($row['isbn'] ?: 'N/A'); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light-custom text-dark border p-2">
                                                    <?php echo $row['available_copies']; ?> available
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="<?php echo BASE_URL; ?>/index.php?action=approve-request&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" title="Approve loan"><i class="fa-solid fa-check me-1"></i>Approve</a>
                                                    
                                                    <!-- Reject Button-->
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $row['id']; ?>" title="Reject loan"><i class="fa-solid fa-xmark me-1"></i>Reject</button>
                                                </div>

                                                <!-- Reject Modal-->
                                                <div class="modal fade" id="rejectModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content glass-card">
                                                            <div class="modal-header border-0 pb-0">
                                                                <h5 class="modal-title fw-bold">Reject Borrow Request</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="<?php echo BASE_URL; ?>/index.php?action=reject-request&id=<?php echo $row['id']; ?>" method="POST">
                                                                <div class="modal-body text-start mt-3">
                                                                    <p class="small text-muted">Please provide a rejection reason. This note will be emailed to the member.</p>
                                                                    <div class="mb-3">
                                                                        <label for="rejectNote" class="form-label small fw-medium">Reason Note</label>
                                                                        <textarea class="form-control form-control-custom" id="rejectNote" name="admin_notes" rows="3" placeholder="e.g. Book is reserved for maintenance." required></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer border-0 pt-0">
                                                                    <button type="button" class="btn btn-outline-secondary btn-sm fw-medium px-3" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger btn-sm fw-medium px-4">Reject Request</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab 2: Active Loans & Returns Tracker -->
        <div class="tab-pane fade" id="loans" role="tabpanel" aria-labelledby="loans-tab">
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-4">Active &amp; Historical Loans</h5>
                    
                    <?php if (empty($allBorrowings)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-book fs-1 mb-3"></i>
                            <h5 class="fw-bold">No Borrowing History</h5>
                            <p class="small">Lending records will display here after approving requests.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive table-responsive-custom">
                            <table class="table table-custom align-middle datatable-custom w-100">
                                <thead>
                                    <tr>
                                        <th>Book / ISBN</th>
                                        <th>Borrowed By</th>
                                        <th>Borrow Date</th>
                                        <th>Due Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                        <th>Fine (EGP)</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allBorrowings as $row): ?>
                                        <?php 
                                        $isOverdue = ($row['status'] === 'borrowed' && strtotime($row['due_date']) < time());
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold d-block text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['book_title']); ?></span>
                                                <span class="text-muted small">ISBN: <?php echo htmlspecialchars($row['isbn'] ?: 'N/A'); ?></span>
                                            </td>
                                            <td><strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['member_name']); ?></strong></td>
                                            <td><?php echo date('M d, Y', strtotime($row['borrow_date'])); ?></td>
                                            <td>
                                                <span class="d-block fw-medium <?php echo $isOverdue ? 'text-danger' : ''; ?>">
                                                    <?php echo date('M d, Y', strtotime($row['due_date'])); ?>
                                                </span>
                                                <?php if ($isOverdue): ?>
                                                    <span class="text-danger small"><i class="fa-solid fa-triangle-exclamation me-1"></i>Overdue</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($row['return_date']) {
                                                    echo date('M d, Y', strtotime($row['return_date']));
                                                } else {
                                                    echo '<span class="text-muted small">Checked out</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($row['status'] === 'returned'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-circle-check me-1"></i>Returned</span>
                                                <?php elseif ($isOverdue): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fa-solid fa-triangle-exclamation me-1"></i>Overdue</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fa-solid fa-clock me-1"></i>Borrowed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($row['fine_amount'] > 0): ?>
                                                    <span class="text-danger fw-bold">EGP <?php echo number_format($row['fine_amount'], 2); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">EGP 0.00</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1 flex-wrap">
                                                    <?php if ($row['status'] === 'borrowed'): ?>
                                                        <!-- Change Due Date Button — triggers the single shared modal-->
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary fw-medium px-2 open-due-date-modal"
                                                                data-borrow-id="<?php echo $row['id']; ?>"
                                                                data-book-title="<?php echo htmlspecialchars($row['book_title'], ENT_QUOTES); ?>"
                                                                data-member-name="<?php echo htmlspecialchars($row['member_name'], ENT_QUOTES); ?>"
                                                                data-due-date="<?php echo $row['due_date']; ?>"
                                                                data-is-overdue="<?php echo $isOverdue ? '1' : '0'; ?>"
                                                                title="Change due date">
                                                            <i class="fa-solid fa-calendar-days me-1"></i>Due Date
                                                        </button>
                                                        <!-- Check In Button -->
                                                        <button class="btn btn-sm btn-primary fw-medium px-2" 
                                                                onclick="window.confirmAction('Return Book', 'Mark book \'<?php echo addslashes($row['book_title']); ?>\' as returned?<?php echo $isOverdue ? ' A fine of EGP ' . (ceil((time() - strtotime($row['due_date'])) / 86400) * 100) . ' will be charged.' : ''; ?>', 'Check In', '<?php echo BASE_URL; ?>/index.php?action=return-book&id=<?php echo $row['id']; ?>')">
                                                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>Check In
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted small"><i class="fa-solid fa-lock text-success me-1"></i>Closed</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="modal fade" id="sharedDueDateModal" tabindex="-1" aria-labelledby="sharedDueDateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="sharedDueDateModalLabel">
                    <i class="fa-solid fa-calendar-days text-primary me-2"></i>Change Due Date
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="dueDateForm" action="<?php echo BASE_URL; ?>/index.php?action=update-due-date" method="POST">
                <input type="hidden" name="borrow_id" id="modal-borrow-id" value="">
                <div class="modal-body text-start mt-2">
                    <p class="small text-muted mb-3" id="modal-description"></p>

                    <div class="mb-3 p-3 rounded-3 bg-light-custom">
                        <div class="small text-muted mb-1">Current Due Date</div>
                        <div class="fw-bold" id="modal-current-date"></div>
                    </div>

                    <div>
                        <label for="modal-new-due-date" class="form-label small fw-semibold">New Due Date</label>
                        <input type="date"
                               class="form-control"
                               id="modal-new-due-date"
                               name="new_due_date"
                               required>
                        <div class="form-text text-muted small mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i>Late fines are <strong>100 EGP per day</strong> past the due date.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm fw-medium px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-medium px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Save New Date
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sharedModal = document.getElementById('sharedDueDateModal');
    if (!sharedModal) return;

    
    document.body.appendChild(sharedModal);

    var bsModal = new bootstrap.Modal(sharedModal);

    document.querySelectorAll('.open-due-date-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var borrowId    = this.dataset.borrowId;
            var bookTitle   = this.dataset.bookTitle;
            var memberName  = this.dataset.memberName;
            var dueDate     = this.dataset.dueDate;   
            var isOverdue   = this.dataset.isOverdue === '1';

            // Populate hidden input & description
            document.getElementById('modal-borrow-id').value = borrowId;
            document.getElementById('modal-description').innerHTML =
                'Updating the due date for <strong>' + bookTitle + '</strong> borrowed by <strong>' + memberName + '</strong>.';

            // Format current date
            var d = new Date(dueDate + 'T00:00:00');
            var formatted = d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            var currentDateEl = document.getElementById('modal-current-date');
            currentDateEl.innerHTML = formatted;
            if (isOverdue) {
                currentDateEl.innerHTML += ' <span class="badge bg-danger ms-1">Overdue</span>';
                currentDateEl.className = 'fw-bold text-danger';
            } else {
                currentDateEl.className = 'fw-bold';
            }

            
            var today = new Date().toISOString().split('T')[0];
            var dateInput = document.getElementById('modal-new-due-date');
            dateInput.value = dueDate;
            dateInput.min   = today;

            bsModal.show();
        });
    });
});
</script>


<style>
    .nav-tabs-custom .nav-link {
        opacity: 0.7;
        transition: all 0.2s ease;
    }
    .nav-tabs-custom .nav-link:hover {
        opacity: 1;
        border-bottom-color: var(--border-color);
    }
    .nav-tabs-custom .nav-link.active {
        opacity: 1;
        background: transparent !important;
    }
</style>
