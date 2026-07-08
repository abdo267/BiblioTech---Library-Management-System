<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}


$bookModel = new Book();
$categoryModel = new Category();
$authorModel = new Author();


$search = $_GET['search'] ?? '';
$catId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$authId = isset($_GET['author_id']) ? intval($_GET['author_id']) : 0;
$availability = $_GET['availability'] ?? '';
$sort = $_GET['sort'] ?? 'b.id';
$order = $_GET['order'] ?? 'DESC';


$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 8; 
$offset = ($currentPage - 1) * $limit;


$filters = [
    'search' => $search,
    'category_id' => $catId,
    'author_id' => $authId,
    'availability' => $availability
];


$totalBooks = $bookModel->countAll($filters);
$totalPages = max(1, ceil($totalBooks / $limit));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $limit; 

$books = $bookModel->getAll($filters, $limit, $offset, $sort, $order);


$categories = $categoryModel->getAll();
$authors = $authorModel->getAll();


function buildQueryString($page, $params = []) {
    $currentParams = $_GET;
    $currentParams['page'] = $page;
    foreach ($params as $k => $v) {
        $currentParams[$k] = $v;
    }
    return BASE_URL . '/index.php?' . http_build_query($currentParams);
}
?>

<div class="container py-5 fade-in-up">
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=home">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Books Catalog</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Filter Form-->
        <div class="col-lg-3">
            <div class="card card-custom border-0 shadow-sm p-4 sticky-top" style="top: 90px; z-index: 10;">
                <h4 class="fw-bold mb-4"><i class="fa-solid fa-filter me-2 text-primary"></i>Filters</h4>
                
                <form action="<?php echo BASE_URL; ?>/index.php" method="GET">
                    <input type="hidden" name="route" value="books">
                    
                    <!-- Search Input -->
                    <div class="mb-3">
                        <label for="filterSearch" class="form-label small fw-medium">Search Keyword</label>
                        <input type="text" class="form-control form-control-custom" id="filterSearch" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Title, ISBN, author...">
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label for="filterCategory" class="form-label small fw-medium">Category</label>
                        <select class="form-select form-control-custom" id="filterCategory" name="category_id">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $catId === intval($cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Author Filter -->
                    <div class="mb-3">
                        <label for="filterAuthor" class="form-label small fw-medium">Author</label>
                        <select class="form-select form-control-custom" id="filterAuthor" name="author_id">
                            <option value="">All Authors</option>
                            <?php foreach ($authors as $auth): ?>
                                <option value="<?php echo $auth['id']; ?>" <?php echo $authId === intval($auth['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($auth['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Availability Filter -->
                    <div class="mb-3">
                        <label for="filterAvail" class="form-label small fw-medium">Availability Status</label>
                        <select class="form-select form-control-custom" id="filterAvail" name="availability">
                            <option value="">All States</option>
                            <option value="available" <?php echo $availability === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="unavailable" <?php echo $availability === 'unavailable' ? 'selected' : ''; ?>>Borrowed</option>
                        </select>
                    </div>

                    <!-- Sort Option -->
                    <div class="mb-4">
                        <label for="filterSort" class="form-label small fw-medium">Sort By</label>
                        <select class="form-select form-control-custom" id="filterSort" name="sort">
                            <option value="b.id" <?php echo $sort === 'b.id' ? 'selected' : ''; ?>>Recently Added</option>
                            <option value="b.title" <?php echo $sort === 'b.title' ? 'selected' : ''; ?>>Book Title (A-Z)</option>
                            <option value="b.publication_year" <?php echo $sort === 'b.publication_year' ? 'selected' : ''; ?>>Publication Year</option>
                            <option value="b.available_copies" <?php echo $sort === 'b.available_copies' ? 'selected' : ''; ?>>Available Quantity</option>
                        </select>
                        
                        <select class="form-select form-control-custom mt-2" name="order">
                            <option value="DESC" <?php echo $order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                            <option value="ASC" <?php echo $order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-medium" style="border-radius: 10px; background: var(--primary-gradient); border: none;"><i class="fa-solid fa-check me-2"></i>Apply Filters</button>
                        <a href="<?php echo BASE_URL; ?>/index.php?route=books" class="btn btn-outline-secondary fw-medium" style="border-radius: 10px;">Clear Filters</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Books Column Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0">Book Listings <span class="fs-6 text-muted fw-normal">(<?php echo $totalBooks; ?> items found)</span></h3>
            </div>

            <!-- Books Cards -->
            <?php if (empty($books)): ?>
                <div class="card card-custom border-0 shadow-sm p-5 text-center">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fa-solid fa-book-open"></i></div>
                        <h4 class="fw-bold">No Books Found</h4>
                        <p class="text-muted">Try adjusting search keywords or filters to find what you want.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4 mb-5">
                    <?php foreach ($books as $book): ?>
                        <div class="col-xl-3 col-md-6 col-sm-6">
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
                </div>

                <!-- Pagination navigation menu -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Books catalog pagination">
                        <ul class="pagination justify-content-center">
                            <!-- Previous button -->
                            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-item page-link border-0 shadow-sm mx-1 rounded-3" href="<?php echo buildQueryString($currentPage - 1); ?>" aria-label="Previous">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $currentPage === $i ? 'active' : ''; ?>">
                                    <a class="page-item page-link border-0 shadow-sm mx-1 rounded-3 <?php echo $currentPage === $i ? 'bg-primary text-white' : ''; ?>" href="<?php echo buildQueryString($i); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next button -->
                            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-item page-link border-0 shadow-sm mx-1 rounded-3" href="<?php echo buildQueryString($currentPage + 1); ?>" aria-label="Next">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
