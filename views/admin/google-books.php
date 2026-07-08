<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$query = $_GET['query'] ?? '';
$results = [];
$apiError = null;

if (!empty($query)) {
    $apiController = new GoogleBooksController();
    $results = $apiController->searchBooks($query);
    $apiError = $apiController->getLastError();
}
?>

<div class="fade-in-up">
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=admin/books">Manage Books</a></li>
            <li class="breadcrumb-item active" aria-current="page">Google Books Import</li>
        </ol>
    </nav>

    <!-- Search Form -->
    <div class="card card-custom border-0 shadow-sm p-4 mb-4">
        <div class="card-body">
            <h4 class="fw-bold mb-3"><i class="fa-brands fa-google text-primary me-2"></i>Search Google Books API</h4>
            <p class="text-muted small">Query books by title, author, topic, or exact ISBN. Selecting import will download properties and covers to local files.</p>
            
            <form action="<?php echo BASE_URL; ?>/index.php" method="GET">
                <input type="hidden" name="route" value="admin/google-books">
                <div class="input-group input-group-lg mt-3">
                    <input type="text" class="form-control form-control-custom rounded-start-pill border-end-0 fs-6 px-4" name="query" value="<?php echo htmlspecialchars($query); ?>" placeholder="Enter book title, author, or ISBN..." required>
                    <button type="submit" class="btn btn-primary px-5 rounded-end-pill fs-6 fw-semibold" style="background: var(--primary-gradient); border: none;">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results -->
    <?php if (!empty($query)): ?>
        <h4 class="fw-bold mb-4 px-1">Search Results for: <em class="text-primary">"<?php echo htmlspecialchars($query); ?>"</em></h4>
        
        <?php if ($apiError): ?>
            <div class="alert alert-danger border-0 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($apiError); ?>
            </div>
        <?php elseif (empty($results)): ?>
            <div class="card card-custom border-0 shadow-sm p-5 text-center">
                <i class="fa-solid fa-circle-exclamation text-warning fs-1 mb-3"></i>
                <h5>No Books Found</h5>
                <p class="text-muted small mb-0">Google Books returned no matches. Verify search queries or connection setup.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($results as $book): ?>
                    <div class="col-12">
                        <div class="card card-custom border-0 shadow-sm p-3 glass-card">
                            <div class="row g-3 align-items-center">
                               
                                <div class="col-md-2 text-center text-md-start">
                                    <?php if (!empty($book['coverUrl'])): ?>
                                        <img src="<?php echo $book['coverUrl']; ?>" class="rounded shadow-sm border img-fluid" style="max-height: 140px; object-fit: cover;" alt="">
                                    <?php else: ?>
                                        <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center mx-auto mx-md-0" style="width: 90px; height: 120px;"><i class="fa-solid fa-image fs-1"></i></div>
                                    <?php endif; ?>
                                </div>

                                
                                <div class="col-md-7">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-2"><?php echo htmlspecialchars($book['category']); ?></span>
                                    <h5 class="fw-bold mb-1 text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($book['title']); ?></h5>
                                    <p class="text-muted small mb-2">by <strong><?php echo htmlspecialchars($book['authors']); ?></strong> | Pub: <?php echo htmlspecialchars($book['publisher']); ?></p>
                                    <p class="small text-muted mb-0 text-truncate-2" title="<?php echo htmlspecialchars($book['description']); ?>" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; line-height: 1.5;">
                                        <?php echo htmlspecialchars($book['description']); ?>
                                    </p>
                                    <?php if (!empty($book['isbn'])): ?>
                                        <div class="mt-2"><span class="small text-muted">ISBN:</span> <code><?php echo htmlspecialchars($book['isbn']); ?></code></div>
                                    <?php endif; ?>
                                </div>

                              
                                <div class="col-md-3 text-center text-md-end">
                                    <form action="<?php echo BASE_URL; ?>/index.php?action=import-book" method="POST">
                                        <input type="hidden" name="title" value="<?php echo htmlspecialchars($book['title']); ?>">
                                        <input type="hidden" name="authors" value="<?php echo htmlspecialchars($book['authors']); ?>">
                                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($book['category']); ?>">
                                        <input type="hidden" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
                                        <input type="hidden" name="description" value="<?php echo htmlspecialchars($book['description']); ?>">
                                        <input type="hidden" name="publisher" value="<?php echo htmlspecialchars($book['publisher']); ?>">
                                        <input type="hidden" name="published_date" value="<?php echo htmlspecialchars($book['publishedDate']); ?>">
                                        <input type="hidden" name="cover_url" value="<?php echo htmlspecialchars($book['coverUrl']); ?>">
                                        
                                        <button type="submit" class="btn btn-outline-success fw-medium w-100-mobile px-4 py-2" style="border-radius: 8px;">
                                            <i class="fa-solid fa-cloud-arrow-down me-2"></i>Import Book
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    /* Responsive utility for button width */
    @media (max-width: 575.98px) {
        .w-100-mobile {
            width: 100%;
        }
    }
</style>
