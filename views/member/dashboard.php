<?php

if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$memberId = Auth::getMemberId();
if (!$memberId) {
    echo '<div class="alert alert-danger m-4">Error: Member profile not found. Please contact administration.</div>';
    exit;
}


$bookModel = new Book();
$borrowModel = new Borrowing();
$reqModel = new BorrowRequest();
$fineModel = new Fine();


$myBorrowings = $borrowModel->getHistoryByMember($memberId);
$myRequests = $reqModel->getHistoryByMember($memberId);
$myUnpaidFines = $fineModel->getUnpaidByMember($memberId);
$unpaidFineTotal = $fineModel->getUnpaidTotalByMember($memberId);


$activeBorrowingsCount = 0;
$overdueCount = 0;
$returnedCount = 0;
$today = strtotime('today');

foreach ($myBorrowings as $b) {
    if ($b['status'] === 'borrowed') {
        $activeBorrowingsCount++;
        if (strtotime($b['due_date']) < $today) {
            $overdueCount++;
        }
    } elseif ($b['status'] === 'returned') {
        $returnedCount++;
    }
}

$pendingRequestsCount = 0;
foreach ($myRequests as $r) {
    if ($r['status'] === 'pending') {
        $pendingRequestsCount++;
    }
}

// Monthly borrowing activity for chart 
$monthNames = [];
$monthCounts = [];
// Build from the borrowings array 
$monthlyCounts = [];
foreach ($myBorrowings as $b) {
    $key = date('Y-m', strtotime($b['borrow_date']));
    $label = date('M Y', strtotime($b['borrow_date']));
    if (!isset($monthlyCounts[$key])) {
        $monthlyCounts[$key] = ['label' => $label, 'count' => 0];
    }
    $monthlyCounts[$key]['count']++;
}
// Sorting 
ksort($monthlyCounts);
$last6 = array_slice($monthlyCounts, -6, 6, true);
foreach ($last6 as $data) {
    $monthNames[] = $data['label'];
    $monthCounts[] = $data['count'];
}


$featuredBooks = $bookModel->getRecentlyAdded(4);
?>

<div class="container py-5 fade-in-up">
    <!-- Welcome Banner -->
    <div class="mb-5 p-4 rounded-3 position-relative overflow-hidden"
        style="background: var(--primary-gradient); color: white;">
        <div class="position-absolute opacity-10" style="right: -40px; top: -40px; font-size: 10rem;"><i
                class="fa-solid fa-book-open"></i></div>
        <h2 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars(Auth::getMemberName()); ?>! 👋</h2>
        <p class="mb-3 opacity-75">Here's a snapshot of your library activity.</p>
        <a href="<?php echo BASE_URL; ?>/index.php?route=books" class="btn btn-light fw-semibold px-4 rounded-pill">
            <i class="fa-solid fa-search me-2"></i>Browse Books
        </a>
    </div>

    <!-- Stats Cards Grid -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card bg-gradient-primary border-0">
                <h3 class="fw-bold mb-1 fs-2"><?php echo $activeBorrowingsCount; ?></h3>
                <p class="mb-0 text-white-50 small font-semibold">Currently Borrowed</p>
                <div class="position-absolute opacity-25" style="right: 20px; top: 20px; font-size: 2.5rem;"><i
                        class="fa-solid fa-book"></i></div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card bg-gradient-success border-0">
                <h3 class="fw-bold mb-1 fs-2"><?php echo $returnedCount; ?></h3>
                <p class="mb-0 text-white-50 small font-semibold">Books Returned</p>
                <div class="position-absolute opacity-25" style="right: 20px; top: 20px; font-size: 2.5rem;"><i
                        class="fa-solid fa-circle-check"></i></div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card bg-gradient-warning border-0">
                <h3 class="fw-bold mb-1 fs-2"><?php echo $overdueCount; ?></h3>
                <p class="mb-0 text-white-50 small font-semibold">Overdue Books</p>
                <div class="position-absolute opacity-25" style="right: 20px; top: 20px; font-size: 2.5rem;"><i
                        class="fa-solid fa-triangle-exclamation"></i></div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <a href="<?php echo BASE_URL; ?>/index.php?route=member/cart" class="text-decoration-none">
                <div class="card stat-card bg-gradient-purple border-0">
                    <h3 class="fw-bold mb-1 fs-2">$<?php echo number_format($unpaidFineTotal, 2); ?></h3>
                    <p class="mb-0 text-white-50 small font-semibold">Outstanding Fines</p>
                    <div class="position-absolute opacity-25" style="right: 20px; top: 20px; font-size: 2.5rem;"><i
                            class="fa-solid fa-dollar-sign"></i></div>
                </div>
            </a>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-5">
        <!-- Status Distribution Chart -->
        <div class="col-lg-4">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-pie text-primary me-2"></i>My Borrowing Status</h5>
                <div style="position: relative; height: 260px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Activity Trends Chart -->
        <div class="col-lg-8">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-line text-success me-2"></i>My Borrowing Activity
                </h5>
                <?php if (empty($monthNames)): ?>
                    <div class="d-flex align-items-center justify-content-center h-100 text-muted flex-column pb-4">
                        <i class="fa-solid fa-chart-line fs-1 mb-3 opacity-25"></i>
                        <p class="mb-0">No borrowing history to chart yet.</p>
                    </div>
                <?php else: ?>
                    <div style="position: relative; height: 260px;">
                        <canvas id="activityChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row g-4 mb-5">
        <!-- Active / Overdue Borrowings -->
        <div class="col-lg-6">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock text-warning me-2"></i>Active Borrowings</h5>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=member/borrowings"
                        class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-medium">View All</a>
                </div>

                <?php
                $activeBorrows = array_filter($myBorrowings, fn($b) => $b['status'] === 'borrowed');
                if (empty($activeBorrows)):
                    ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-circle-check fs-1 text-success mb-3"></i>
                        <p class="mb-0">No active borrowings. Browse books to get started!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($activeBorrows, 0, 5) as $b):
                                    $isOverdue = strtotime($b['due_date']) < $today;
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold d-block"
                                                style="color: var(--text-color); max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                title="<?php echo htmlspecialchars($b['book_title']); ?>">
                                                <?php echo htmlspecialchars($b['book_title']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($b['due_date'])); ?></td>
                                        <td>
                                            <?php if ($isOverdue): ?>
                                                <span
                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i
                                                        class="fa-solid fa-triangle-exclamation me-1"></i>Overdue</span>
                                            <?php else: ?>
                                                <span
                                                    class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i
                                                        class="fa-solid fa-clock me-1"></i>Borrowed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Borrow Requests -->
        <div class="col-lg-6">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-hourglass-half text-info me-2"></i>My Recent Requests
                    </h5>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=books"
                        class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-medium">Browse Books</a>
                </div>

                <?php if (empty($myRequests)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fs-1 mb-3"></i>
                        <p class="mb-0">You haven't made any borrow requests yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($myRequests, 0, 5) as $req): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold d-block"
                                                style="color: var(--text-color); max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                title="<?php echo htmlspecialchars($req['book_title']); ?>">
                                                <?php echo htmlspecialchars($req['book_title']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                                        <td>
                                            <?php if ($req['status'] === 'pending'): ?>
                                                <span
                                                    class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i
                                                        class="fa-solid fa-hourglass me-1"></i>Pending</span>
                                            <?php elseif ($req['status'] === 'approved'): ?>
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i
                                                        class="fa-solid fa-check me-1"></i>Approved</span>
                                            <?php else: ?>
                                                <span
                                                    class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i
                                                        class="fa-solid fa-xmark me-1"></i>Rejected</span>
                                            <?php endif; ?>
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

    <!-- Outstanding Fines Alert -->
    <?php if ($unpaidFineTotal > 0): ?>
        <div class="alert border-0 shadow-sm mb-5 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 p-4 rounded-3"
            style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e;">
            <div class="d-flex align-items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation fs-3"></i>
                <div>
                    <h6 class="fw-bold mb-1">Outstanding Fines: $<?php echo number_format($unpaidFineTotal, 2); ?></h6>
                    <p class="mb-0 small">You have <?php echo count($myUnpaidFines); ?> unpaid fine(s). You can pay them
                        directly online using your credit card.</p>
                </div>
            </div>
            <a href="<?php echo BASE_URL; ?>/index.php?route=member/cart"
                class="btn btn-warning fw-bold px-4 rounded-pill shadow-sm text-nowrap align-self-start align-self-sm-center"
                style="background: #d97706; border: none; color: white;">
                <i class="fa-solid fa-cart-shopping me-2"></i>Pay Fines Now
            </a>
        </div>
    <?php endif; ?>

    <!-- Recently Added Books -->
    <div class="card card-custom border-0 shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-book-medical text-primary me-2"></i>Recently Added Books</h5>
            <a href="<?php echo BASE_URL; ?>/index.php?route=books"
                class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-medium">View Catalog</a>
        </div>

        <?php if (empty($featuredBooks)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-folder-open fs-1 mb-3"></i>
                <p class="mb-0">No books cataloged yet.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($featuredBooks as $book): ?>
                    <div class="col-md-3 col-sm-6">
                        <a href="<?php echo BASE_URL; ?>/index.php?route=book-details&id=<?php echo $book['id']; ?>"
                            class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm p-3 text-center"
                                style="transition: transform 0.2s; border-radius: 12px;"
                                onmouseenter="this.style.transform='translateY(-4px)'"
                                onmouseleave="this.style.transform='translateY(0)'">
                                <?php if (!empty($book['cover_image']) && file_exists(UPLOAD_PATH . '/' . $book['cover_image'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $book['cover_image']; ?>"
                                        class="rounded mx-auto mb-3 shadow-sm" style="width: 70px; height: 95px; object-fit: cover;"
                                        alt="">
                                <?php elseif (!empty($book['cover_image']) && strpos($book['cover_image'], 'http') === 0): ?>
                                    <img src="<?php echo $book['cover_image']; ?>" class="rounded mx-auto mb-3 shadow-sm"
                                        style="width: 70px; height: 95px; object-fit: cover;" alt="">
                                <?php else: ?>
                                    <div class="bg-secondary rounded mx-auto mb-3 text-white d-flex align-items-center justify-content-center"
                                        style="width: 70px; height: 95px;">
                                        <i class="fa-solid fa-book fs-4"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="fw-semibold small text-truncate" style="color: var(--text-color);">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <?php echo htmlspecialchars($book['author_name'] ?? 'Unknown'); ?>
                                </div>
                                <?php if ($book['available_copies'] > 0): ?>
                                    <span
                                        class="badge bg-success-subtle text-success border border-success-subtle mt-2 px-2">Available</span>
                                <?php else: ?>
                                    <span
                                        class="badge bg-danger-subtle text-danger border border-danger-subtle mt-2 px-2">Unavailable</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chart Script -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const isDark = () => document.documentElement.getAttribute('data-theme') === 'dark';
        const getLabelColor = () => isDark() ? '#cbd5e1' : '#475569';
        const getGridColor = () => isDark() ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

        // Status Distribution Doughnut
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx && typeof Chart !== 'undefined') {
            const statusChart = new Chart(statusCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Returned', 'Overdue'],
                    datasets: [{
                        data: [
                            <?php echo max(0, $activeBorrowingsCount - $overdueCount); ?>,
                            <?php echo $returnedCount; ?>,
                            <?php echo $overdueCount; ?>
                        ],
                        backgroundColor: ['#4f46e5', '#10b981', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: getLabelColor() }
                        }
                    }
                }
            });

            window.addEventListener('themechanged', (e) => {
                const col = e.detail.theme === 'dark' ? '#cbd5e1' : '#475569';
                statusChart.options.plugins.legend.labels.color = col;
                statusChart.update();
            });
        }

        // Activity Line Chart
        <?php if (!empty($monthNames)): ?>
            const actCtx = document.getElementById('activityChart');
            if (actCtx && typeof Chart !== 'undefined') {
                const activityChart = new Chart(actCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_values($monthNames)); ?>,
                        datasets: [{
                            label: 'Books Borrowed',
                            data: <?php echo json_encode(array_values($monthCounts)); ?>,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                grid: { color: getGridColor() },
                                ticks: { color: getLabelColor() }
                            },
                            y: {
                                grid: { color: getGridColor() },
                                ticks: { color: getLabelColor(), stepSize: 1 },
                                beginAtZero: true
                            }
                        }
                    }
                });

                window.addEventListener('themechanged', (e) => {
                    const labelCol = e.detail.theme === 'dark' ? '#cbd5e1' : '#475569';
                    const gridCol = e.detail.theme === 'dark' ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
                    activityChart.options.scales.x.ticks.color = labelCol;
                    activityChart.options.scales.x.grid.color = gridCol;
                    activityChart.options.scales.y.ticks.color = labelCol;
                    activityChart.options.scales.y.grid.color = gridCol;
                    activityChart.update();
                });
            }
        <?php endif; ?>
    });
</script>