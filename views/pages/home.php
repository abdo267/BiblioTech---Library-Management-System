<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}


$bookModel = new Book();
$categoryModel = new Category();
$memberModel = new Member();

$totalBooks = $bookModel->countAll();
$totalCategories = count($categoryModel->getAll());
$totalMembers = $memberModel->countAll();

$recentBooks = $bookModel->getRecentlyAdded(4);
$featuredBooks = $bookModel->getFeatured(4);
?>

<div class="container py-5 fade-in-up">
    <!-- Hero Header banner -->
    <div class="hero-section text-center text-md-start">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="display-4 fw-extrabold mb-3">Welcome to BiblioTech</h1>
                <p class="lead fs-5 mb-4 text-white-50">Discover, borrow, and read thousands of books online and offline. Manage your reading lists, track borrowing history, and explore recommendations tailor-made for you.</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                    <a href="<?php echo BASE_URL; ?>/index.php?route=books" class="btn btn-light btn-lg fw-medium px-4 text-primary"><i class="fa-solid fa-magnifying-glass me-2"></i>Browse Catalog</a>
                    <?php if (!Auth::isLoggedIn()): ?>
                        <a href="<?php echo BASE_URL; ?>/index.php?route=register" class="btn btn-outline-light btn-lg fw-medium px-4">Get Started</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-5 d-none d-md-block text-center">
                <i class="fa-solid fa-book-bookmark text-white-50" style="font-size: 15rem; transform: rotate(-10deg);"></i>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards Section -->
    <div class="row g-4 mb-5 text-center">
        <div class="col-md-4">
            <div class="card card-custom p-4 border-0 shadow-sm glass-card">
                <div class="card-body">
                    <div class="fs-1 text-primary mb-2"><i class="fa-solid fa-book"></i></div>
                    <h3 class="fw-bold mb-1"><?php echo number_format($totalBooks); ?></h3>
                    <p class="text-muted mb-0">Total Books</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-4 border-0 shadow-sm glass-card">
                <div class="card-body">
                    <div class="fs-1 text-success mb-2"><i class="fa-solid fa-tags"></i></div>
                    <h3 class="fw-bold mb-1"><?php echo number_format($totalCategories); ?></h3>
                    <p class="text-muted mb-0">Categories</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-4 border-0 shadow-sm glass-card">
                <div class="card-body">
                    <div class="fs-1 text-warning mb-2"><i class="fa-solid fa-users"></i></div>
                    <h3 class="fw-bold mb-1"><?php echo number_format($totalMembers); ?></h3>
                    <p class="text-muted mb-0">Active Readers</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recently Added Section -->
    <div class="row align-items-center mb-4">
        <div class="col-6">
            <h3 class="fw-bold mb-0">Recently Added</h3>
        </div>
        <div class="col-6 text-end">
            <a href="<?php echo BASE_URL; ?>/index.php?route=books" class="btn btn-outline-primary fw-medium px-4">View All</a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <?php if (empty($recentBooks)): ?>
            <div class="col-12 text-center text-muted">No books registered yet.</div>
        <?php else: ?>
            <?php foreach ($recentBooks as $book): ?>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-custom book-card h-100 border-0 shadow-sm">
                        <div class="p-3">
                            <div class="book-cover-wrapper">
                                <?php if (!empty($book['cover_image']) && file_exists(UPLOAD_PATH . '/' . $book['cover_image'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $book['cover_image']; ?>" class="book-cover-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php elseif (!empty($book['cover_image']) && strpos($book['cover_image'], 'http') === 0): ?>
                                    <img src="<?php echo $book['cover_image']; ?>" class="book-cover-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php else: ?>
                                    <div class="w-100 h-100 bg-secondary d-flex flex-column align-items-center justify-content-center text-white">
                                        <i class="fa-solid fa-image fs-1 mb-2"></i>
                                        <small class="text-uppercase tracking-wider">No Cover</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body pt-0 d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge badge-custom mb-2"><?php echo htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></span>
                                <h5 class="card-title fw-bold text-truncate mb-1" title="<?php echo htmlspecialchars($book['title']); ?>"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text text-muted small mb-3">by <?php echo htmlspecialchars($book['author_name'] ?? 'Unknown Author'); ?></p>
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                <?php if ($book['available_copies'] > 0): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-check me-1"></i>Available</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-ban me-1"></i>Borrowed</span>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>/index.php?route=book-details&id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary px-3 rounded-3">Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Featured Reads Section -->
    <div class="row align-items-center mb-4">
        <div class="col-12">
            <h3 class="fw-bold mb-0">Featured Books</h3>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($featuredBooks)): ?>
            <div class="col-12 text-center text-muted">No books featured.</div>
        <?php else: ?>
            <?php foreach ($featuredBooks as $book): ?>
                <div class="col-xl-3 col-md-6">
                    <div class="card card-custom book-card h-100 border-0 shadow-sm">
                        <div class="p-3">
                            <div class="book-cover-wrapper">
                                <?php if (!empty($book['cover_image']) && file_exists(UPLOAD_PATH . '/' . $book['cover_image'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $book['cover_image']; ?>" class="book-cover-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php elseif (!empty($book['cover_image']) && strpos($book['cover_image'], 'http') === 0): ?>
                                    <img src="<?php echo $book['cover_image']; ?>" class="book-cover-img" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php else: ?>
                                    <div class="w-100 h-100 bg-secondary d-flex flex-column align-items-center justify-content-center text-white">
                                        <i class="fa-solid fa-image fs-1 mb-2"></i>
                                        <small class="text-uppercase tracking-wider">No Cover</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body pt-0 d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge badge-custom mb-2"><?php echo htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></span>
                                <h5 class="card-title fw-bold text-truncate mb-1" title="<?php echo htmlspecialchars($book['title']); ?>"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text text-muted small mb-3">by <?php echo htmlspecialchars($book['author_name'] ?? 'Unknown Author'); ?></p>
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                <?php if ($book['available_copies'] > 0): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fa-solid fa-check me-1"></i>Available</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fa-solid fa-ban me-1"></i>Borrowed</span>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>/index.php?route=book-details&id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary px-3 rounded-3">Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
