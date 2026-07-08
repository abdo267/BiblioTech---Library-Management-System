<?php

if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

// Load Models
$bookModel = new Book();
$memberModel = new Member();
$borrowModel = new Borrowing();
$reqModel = new BorrowRequest();
$logModel = new ActivityLog();


$totalBooks = $bookModel->countAll();
$availableBooks = $bookModel->countAll(['availability' => 'available']);
$borrowedBooksCount = $totalBooks - $availableBooks;

$totalMembers = $memberModel->countAll();
$activeLoans = $borrowModel->getActiveBorrowings();
$activeLoansCount = count($activeLoans);

$pendingRequests = $reqModel->getPendingRequests();
$pendingRequestsCount = count($pendingRequests);


$monthlyStats = $borrowModel->getMonthlyBorrowingStats();
$monthNames = [];
$monthCounts = [];
foreach ($monthlyStats as $m) {
    $monthNames[] = $m['month'];
    $monthCounts[] = intval($m['count']);
}


$recentBooks = $bookModel->getRecentlyAdded(5);
?>

<div class="fade-in-up">
    <!-- Stats Cards Grid -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card bg-gradient-primary border-0">
                <h3 class="fw-bold mb-1 fs-2"><?php echo number_format($totalBooks); ?></h3>
                <p class="mb-0 text-white-50 small font-semibold">Total Books</p>
                <div class="position-absolute opacity-25" style="right: 20px; top: 20px; font-size: 2.5rem;"><i class="fa-solid fa-book"></i></div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card bg-gradient-success border-0">
                <h3 class="fw-bold mb-1 fs-2"><?php echo number_format($availableBooks); ?></h3>
                <p class="mb-0 text-white-50 small font-semibold">Available Copies</p>
                <div class="position-absolute opacity-25" style="right: 20px; top: 20px; font-size: 2.5rem;"><i class="fa-solid fa-check-circle"></i></div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card bg-gradient-warning border-0">
                <h3 class="fw-bold mb-1 fs-2"><?php echo $activeLoansCount; ?></h3>
                <p class="mb-0 text-white-50 small font-semibold">Active Loans</p>
                <div class="position-absolute opacity-25" style="right: 20px; top: 20px; font-size: 2.5rem;"><i class="fa-solid fa-clock"></i></div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card bg-gradient-purple border-0">
                <h3 class="fw-bold mb-1 fs-2"><?php echo number_format($totalMembers); ?></h3>
                <p class="mb-0 text-white-50 small font-semibold">Total Members</p>
                <div class="position-absolute opacity-25" style="right: 20px; top: 20px; font-size: 2.5rem;"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-5">
        <!-- Stock Distribution Chart -->
        <div class="col-lg-4">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Book Stock Distribution</h5>
                <div style="position: relative; height: 260px;">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Borrowing Trends Chart -->
        <div class="col-lg-8">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-line text-success me-2"></i>Borrowing Activity (Past 6 Months)</h5>
                <div style="position: relative; height: 260px;">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row g-4">
        <!-- Pending Borrow Requests -->
        <div class="col-lg-6">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-hourglass-half text-warning me-2"></i>Recent Borrow Requests</h5>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin/borrow-requests" class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-medium">View All</a>
                </div>

                <?php if (empty($pendingRequests)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-circle-check fs-1 text-success mb-3"></i>
                        <p class="mb-0">All requests processed. No pending requests!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Book Requested</th>
                                    <th>Date</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $topReqs = array_slice($pendingRequests, 0, 5);
                                foreach ($topReqs as $req): 
                                ?>
                                    <tr>
                                        <td><span class="fw-bold text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($req['member_name']); ?></span></td>
                                        <td><span class="text-truncate d-inline-block text-dark-theme-override" style="max-width: 180px; color: var(--text-color);" title="<?php echo htmlspecialchars($req['book_title']); ?>"><?php echo htmlspecialchars($req['book_title']); ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                                        <td class="text-end">
                                            <a href="<?php echo BASE_URL; ?>/index.php?action=approve-request&id=<?php echo $req['id']; ?>" class="btn btn-sm btn-success rounded-circle me-1" title="Approve"><i class="fa-solid fa-check"></i></a>
                                            <button class="btn btn-sm btn-danger rounded-circle" onclick="window.confirmAction('Reject Request', 'Reject borrow request for: <?php echo addslashes($req['member_name']); ?>?', 'Reject', '<?php echo BASE_URL; ?>/index.php?action=reject-request&id=<?php echo $req['id']; ?>')" title="Reject"><i class="fa-solid fa-xmark"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recently Added Books -->
        <div class="col-lg-6">
            <div class="card card-custom border-0 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-book-medical text-primary me-2"></i>Recently Added Books</h5>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin/books" class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-medium">View Catalog</a>
                </div>

                <?php if (empty($recentBooks)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fs-1 mb-3"></i>
                        <p class="mb-0">No books cataloged yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Author</th>
                                    <th>Qty Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBooks as $book): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if (!empty($book['cover_image']) && file_exists(UPLOAD_PATH . '/' . $book['cover_image'])): ?>
                                                    <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $book['cover_image']; ?>" class="rounded shadow-sm" style="width: 30px; height: 40px; object-fit: cover;" alt="">
                                                <?php elseif (!empty($book['cover_image']) && strpos($book['cover_image'], 'http') === 0): ?>
                                                    <img src="<?php echo $book['cover_image']; ?>" class="rounded shadow-sm" style="width: 30px; height: 40px; object-fit: cover;" alt="">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 40px;"><i class="fa-solid fa-image small" style="font-size: 0.6rem;"></i></div>
                                                <?php endif; ?>
                                                <span class="fw-bold text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($book['title']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($book['author_name'] ?? 'Unknown'); ?></td>
                                        <td>
                                            <span class="badge bg-light-custom text-dark border p-2"><?php echo $book['available_copies']; ?> / <?php echo $book['quantity']; ?></span>
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


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const isDark = () => document.documentElement.getAttribute('data-theme') === 'dark';
        const getLabelColor = () => isDark() ? '#cbd5e1' : '#475569';
        const getGridColor = () => isDark() ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

        // Stock Chart
        const stockCtx = document.getElementById('stockChart').getContext('2d');
        const stockChart = new Chart(stockCtx, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'Borrowed'],
                datasets: [{
                    data: [<?php echo $availableBooks; ?>, <?php echo $borrowedBooksCount; ?>],
                    backgroundColor: ['#10b981', '#4f46e5'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: getLabelColor()
                        }
                    }
                }
            }
        });

        // Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        const trendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthNames); ?>,
                datasets: [{
                    label: 'Borrowings',
                    data: <?php echo json_encode($monthCounts); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
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
            
            
            stockChart.options.plugins.legend.labels.color = labelCol;
            stockChart.update();

            trendsChart.options.scales.x.ticks.color = labelCol;
            trendsChart.options.scales.x.grid.color = gridCol;
            trendsChart.options.scales.y.ticks.color = labelCol;
            trendsChart.options.scales.y.grid.color = gridCol;
            trendsChart.update();
        });
    });
</script>
