<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bookModel = new Book();
$book = $bookModel->findById($id);

if (!$book) {
    Session::setFlash('error', 'The requested book could not be found.');
    header('Location: ' . BASE_URL . '/index.php?route=books');
    exit;
}

// Get recommendations 
$recommendations = [];
if (!empty($book['category_id'])) {
    $recommendations = $bookModel->getRecommendations($book['category_id'], $book['id'], 4);
}

// Check borrowing state for members
$hasActiveRequest = false;
if (Auth::isMember()) {
    $reqModel = new BorrowRequest();
    $hasActiveRequest = $reqModel->hasActiveRequestOrBorrow(Auth::getMemberId(), $book['id']);
}
?>

<div class="container py-5 fade-in-up">
   
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=home">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=books">Books</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($book['title']); ?></li>
        </ol>
    </nav>

    <!-- Main Book Panel -->
    <div class="card card-custom border-0 shadow-sm p-4 p-md-5 mb-5">
        <div class="row g-5">
            <!-- Cover image -->
            <div class="col-lg-4 text-center">
                <div class="book-cover-wrapper mx-auto mb-4" style="max-width: 280px; height: 400px; box-shadow: 0 15px 30px rgba(0,0,0,0.15);">
                    <?php if (!empty($book['cover_image']) && file_exists(UPLOAD_PATH . '/' . $book['cover_image'])): ?>
                        <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $book['cover_image']; ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <?php elseif (!empty($book['cover_image']) && strpos($book['cover_image'], 'http') === 0): ?>
                        <img src="<?php echo $book['cover_image']; ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <?php else: ?>
                        <div class="w-100 h-100 bg-secondary d-flex flex-column align-items-center justify-content-center text-white">
                            <i class="fa-solid fa-image fs-1 mb-2"></i>
                            <small class="text-uppercase tracking-wider">No Cover</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Availability Badges -->
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <?php if ($book['available_copies'] > 0): ?>
                        <span class="badge bg-success border border-success-subtle px-3 py-2 fs-6"><i class="fa-solid fa-circle-check me-2"></i><?php echo $book['available_copies']; ?> Available</span>
                    <?php else: ?>
                        <span class="badge bg-danger border border-danger-subtle px-3 py-2 fs-6"><i class="fa-solid fa-circle-xmark me-2"></i>Out of Stock</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Details Block -->
            <div class="col-lg-8 d-flex flex-column justify-content-between">
                <div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded mb-3 fs-6"><?php echo htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></span>
                    <h1 class="fw-extrabold display-5 mb-2"><?php echo htmlspecialchars($book['title']); ?></h1>
                    
                    <h5 class="text-muted fw-normal mb-4">
                        by <strong class="text-primary"><?php echo htmlspecialchars($book['author_name'] ?? 'Unknown Author'); ?></strong>
                        <?php if (!empty($book['author_nationality'])): ?>
                            <span class="small text-muted">(<?php echo htmlspecialchars($book['author_nationality']); ?>)</span>
                        <?php endif; ?>
                    </h5>
                    
                    <!-- Description -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-2">Description</h5>
                        <p class="text-muted" style="line-height: 1.7;"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    </div>

                    <!-- Metadata Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-sm-6">
                            <div class="p-3 border rounded-3 bg-light-custom">
                                <span class="text-muted small d-block">ISBN</span>
                                <strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($book['isbn'] ?: 'N/A'); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="p-3 border rounded-3 bg-light-custom">
                                <span class="text-muted small d-block">Publisher</span>
                                <strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($book['publisher'] ?: 'N/A'); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="p-3 border rounded-3 bg-light-custom">
                                <span class="text-muted small d-block">Publication Year</span>
                                <strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($book['publication_year'] ?: 'N/A'); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="p-3 border rounded-3 bg-light-custom">
                                <span class="text-muted small d-block">Shelf Location</span>
                                <strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($book['shelf_location'] ?: 'N/A'); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Panel -->
                <div class="pt-3 border-top mt-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="small text-muted d-block">Total Library Stock: <strong><?php echo $book['quantity']; ?> copies</strong></span>
                        </div>
                        
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <?php if (Auth::isMember()): ?>
                                <?php if ($hasActiveRequest): ?>
                                    <button class="btn btn-secondary fw-medium px-4 py-2 w-100-mobile" disabled>
                                        <i class="fa-solid fa-spinner me-2"></i>Requested / Active
                                    </button>
                                <?php elseif ($book['available_copies'] <= 0): ?>
                                    <button class="btn btn-secondary fw-medium px-4 py-2 w-100-mobile" disabled>
                                        <i class="fa-solid fa-ban me-2"></i>Out of Stock
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>/index.php?action=request-book&book_id=<?php echo $book['id']; ?>" class="btn btn-primary fw-medium px-4 py-2 w-100-mobile" style="border-radius: 10px; background: var(--primary-gradient); border: none;">
                                        <i class="fa-solid fa-bookmark me-2"></i>Borrow Book
                                    </a>
                                <?php endif; ?>
                            <?php elseif (!Auth::isLoggedIn()): ?>
                                <a href="<?php echo BASE_URL; ?>/index.php?route=login" class="btn btn-primary fw-medium px-4 py-2 w-100-mobile" style="border-radius: 10px; background: var(--primary-gradient); border: none;">
                                    <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In to Borrow
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations Grid -->
    <?php if (!empty($recommendations)): ?>
        <div class="mt-5 py-3">
            <h3 class="fw-bold mb-4">Recommended Reads</h3>
            <div class="row g-4">
                <?php foreach ($recommendations as $rec): ?>
                    <div class="col-xl-3 col-md-6 col-sm-6">
                        <div class="card card-custom book-card h-100 border-0 shadow-sm">
                            <div class="p-3">
                                <div class="book-cover-wrapper" style="height: 240px;">
                                    <?php if (!empty($rec['cover_image']) && file_exists(UPLOAD_PATH . '/' . $rec['cover_image'])): ?>
                                        <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $rec['cover_image']; ?>" class="book-cover-img" alt="<?php echo htmlspecialchars($rec['title']); ?>">
                                    <?php elseif (!empty($rec['cover_image']) && strpos($rec['cover_image'], 'http') === 0): ?>
                                        <img src="<?php echo $rec['cover_image']; ?>" class="book-cover-img" alt="<?php echo htmlspecialchars($rec['title']); ?>">
                                    <?php else: ?>
                                        <div class="w-100 h-100 bg-secondary d-flex flex-column align-items-center justify-content-center text-white">
                                            <i class="fa-solid fa-image fs-2 mb-2"></i>
                                            <small class="small text-uppercase">No Cover</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body pt-0 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title fw-bold text-truncate mb-1" title="<?php echo htmlspecialchars($rec['title']); ?>"><?php echo htmlspecialchars($rec['title']); ?></h6>
                                    <p class="card-text text-muted small mb-2">by <?php echo htmlspecialchars($rec['author_name']); ?></p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                    <span class="small text-muted"><?php echo $rec['available_copies']; ?> left</span>
                                    <a href="<?php echo BASE_URL; ?>/index.php?route=book-details&id=<?php echo $rec['id']; ?>" class="btn btn-sm btn-outline-primary px-3 py-1 rounded-3">Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
