<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$type = $_GET['type'] ?? 'borrowed';

$borrowModel = new Borrowing();
$bookModel = new Book();
$memberModel = new Member();

$reportTitle = 'Active Borrowings';
$reportData = [];

switch ($type) {
    case 'borrowed':
        $reportTitle = 'Active Loaned Books';
        $reportData = $borrowModel->getActiveBorrowings();
        break;
        
    case 'returned':
        $reportTitle = 'Returned Books History';
       
        $all = $borrowModel->getAllBorrowings();
        $reportData = array_filter($all, function ($b) {
            return $b['status'] === 'returned';
        });
        break;
        
    case 'most_borrowed':
        $reportTitle = 'Most Borrowed Books (Top Books)';
        $reportData = $borrowModel->getMostBorrowedBooks(10);
        break;
        
    case 'active_members':
        $reportTitle = 'Most Active Members';
        $reportData = $borrowModel->getActiveMembers(10);
        break;
        
    case 'low_stock':
        $reportTitle = 'Low Stock Warnings (Copies <= 1)';
 
        $allBooks = $bookModel->getAll();
        $reportData = array_filter($allBooks, function ($b) {
            return intval($b['available_copies']) <= 1;
        });
        break;
}
?>

<div class="fade-in-up">
   
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reports Center</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Navigation-->
        <div class="col-lg-3">
            <div class="card card-custom border-0 shadow-sm p-4 sticky-top" style="top: 90px; z-index: 10;">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-list-check me-2 text-primary"></i>Report Types</h5>
                
                <div class="list-group list-group-custom border-0">
                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin/reports&type=borrowed" class="list-group-item list-group-item-action border-0 px-3 py-2.5 rounded-3 mb-1 fw-semibold <?php echo $type === 'borrowed' ? 'active bg-primary text-white' : 'text-muted'; ?>">
                        <i class="fa-solid fa-bookmark me-2"></i>Active Loans
                    </a>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin/reports&type=returned" class="list-group-item list-group-item-action border-0 px-3 py-2.5 rounded-3 mb-1 fw-semibold <?php echo $type === 'returned' ? 'active bg-primary text-white' : 'text-muted'; ?>">
                        <i class="fa-solid fa-circle-check me-2"></i>Returned Books
                    </a>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin/reports&type=most_borrowed" class="list-group-item list-group-item-action border-0 px-3 py-2.5 rounded-3 mb-1 fw-semibold <?php echo $type === 'most_borrowed' ? 'active bg-primary text-white' : 'text-muted'; ?>">
                        <i class="fa-solid fa-chart-bar me-2"></i>Most Borrowed
                    </a>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin/reports&type=active_members" class="list-group-item list-group-item-action border-0 px-3 py-2.5 rounded-3 mb-1 fw-semibold <?php echo $type === 'active_members' ? 'active bg-primary text-white' : 'text-muted'; ?>">
                        <i class="fa-solid fa-users me-2"></i>Active Members
                    </a>
                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin/reports&type=low_stock" class="list-group-item list-group-item-action border-0 px-3 py-2.5 rounded-3 mb-0 fw-semibold <?php echo $type === 'low_stock' ? 'active bg-primary text-white' : 'text-muted'; ?>">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Low Stock Alert
                    </a>
                </div>
            </div>
        </div>

        <!-- Report Table display-->
        <div class="col-lg-9">
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <h4 class="fw-bold mb-0"><?php echo $reportTitle; ?></h4>
                        <button class="btn btn-outline-secondary btn-sm fw-medium px-3" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print Report</button>
                    </div>

                    <?php if (empty($reportData)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-1 mb-3"></i>
                            <p class="mb-0">No records found matching this report criteria.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive table-responsive-custom">
                            
                            <!-- Dynamic rendering based on type -->
                            <?php if ($type === 'borrowed' || $type === 'returned'): ?>
                                <table class="table table-custom align-middle datatable-custom w-100">
                                    <thead>
                                        <tr>
                                            <th>Book Title</th>
                                            <th>ISBN</th>
                                            <th>Member Name</th>
                                            <th>Borrow Date</th>
                                            <th>Due Date</th>
                                            <?php if ($type === 'returned'): ?>
                                                <th>Return Date</th>
                                                <th>Fine Amount</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                            <tr>
                                                <td><strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['book_title']); ?></strong></td>
                                                <td><code><?php echo htmlspecialchars($row['isbn']); ?></code></td>
                                                <td><?php echo htmlspecialchars($row['member_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['borrow_date'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['due_date'])); ?></td>
                                                <?php if ($type === 'returned'): ?>
                                                    <td><?php echo date('M d, Y', strtotime($row['return_date'])); ?></td>
                                                    <td>
                                                        <span class="<?php echo $row['fine_amount'] > 0 ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                                            $<?php echo number_format($row['fine_amount'], 2); ?>
                                                        </span>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            <?php elseif ($type === 'most_borrowed'): ?>
                                <table class="table table-custom align-middle datatable-custom w-100">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Cover</th>
                                            <th>Book Title</th>
                                            <th>Category</th>
                                            <th>Borrow Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rank = 1;
                                        foreach ($reportData as $row): 
                                        ?>
                                            <tr>
                                                <td><span class="badge bg-primary rounded-circle" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;"><?php echo $rank++; ?></span></td>
                                                <td>
                                                    <?php if (!empty($row['cover_image']) && file_exists(UPLOAD_PATH . '/' . $row['cover_image'])): ?>
                                                        <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $row['cover_image']; ?>" class="rounded" style="width: 30px; height: 40px; object-fit: cover;" alt="">
                                                    <?php else: ?>
                                                        <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 40px;"><i class="fa-solid fa-image small" style="font-size: 0.6rem;"></i></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($row['category_name'] ?? 'General'); ?></td>
                                                <td><span class="badge bg-success px-3 py-2"><?php echo $row['borrow_count']; ?> times</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            <?php elseif ($type === 'active_members'): ?>
                                <table class="table table-custom align-middle datatable-custom w-100">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Member Name</th>
                                            <th>Email</th>
                                            <th>Total Books Borrowed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $rank = 1;
                                        foreach ($reportData as $row): 
                                        ?>
                                            <tr>
                                                <td><span class="badge bg-primary rounded-circle" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;"><?php echo $rank++; ?></span></td>
                                                <td><strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['full_name']); ?></strong></td>
                                                <td><code><?php echo htmlspecialchars($row['email']); ?></code></td>
                                                <td><span class="badge bg-success px-3 py-2"><?php echo $row['borrow_count']; ?> books</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            <?php elseif ($type === 'low_stock'): ?>
                                <table class="table table-custom align-middle datatable-custom w-100">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>ISBN</th>
                                            <th>Author</th>
                                            <th>Total Stock</th>
                                            <th>Copies Available</th>
                                            <th>Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                            <tr>
                                                <td><strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                                <td><code><?php echo htmlspecialchars($row['isbn']); ?></code></td>
                                                <td><?php echo htmlspecialchars($row['author_name'] ?? 'Unknown'); ?></td>
                                                <td><?php echo $row['quantity']; ?></td>
                                                <td>
                                                    <span class="badge <?php echo $row['available_copies'] === 0 ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning'; ?> border p-2">
                                                        <?php echo $row['available_copies']; ?> copies
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['shelf_location'] ?: 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
