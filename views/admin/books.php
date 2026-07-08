<?php

if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$bookModel = new Book();
$authorModel = new Author();
$categoryModel = new Category();


$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$addMode = isset($_GET['add']) ? true : false;
$book = null;

if ($editId > 0) {
    $book = $bookModel->findById($editId);
}

$books = $bookModel->getAll();
$authors = $authorModel->getAll();
$categories = $categoryModel->getAll();
?>

<div class="fade-in-up">
   
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=admin/books">Manage Books</a></li>
            <?php if ($book): ?>
                <li class="breadcrumb-item active" aria-current="page">Edit Book</li>
            <?php elseif ($addMode): ?>
                <li class="breadcrumb-item active" aria-current="page">Add Book</li>
            <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page">Inventory</li>
            <?php endif; ?>
        </ol>
    </nav>

    <!-- 1. Edit Book Form Section-->
    <?php if ($book): ?>
        <div class="card card-custom border-0 shadow-sm p-4 p-md-5 mb-4">
            <div class="card-body">
                <h4 class="fw-bold mb-4 text-warning"><i class="fa-solid fa-pen-to-square me-2"></i>Modify Book Listing</h4>
                
                <form action="<?php echo BASE_URL; ?>/index.php?action=edit-book" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="bookTitle" class="form-label small fw-medium">Book Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-custom" id="bookTitle" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="bookIsbn" class="form-label small fw-medium">ISBN</label>
                            <input type="text" class="form-control form-control-custom" id="bookIsbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn'] ?: ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="bookAuthor" class="form-label small fw-medium">Author</label>
                            <select class="form-select form-control-custom" id="bookAuthor" name="author_id">
                                <option value="">Select Author...</option>
                                <?php foreach ($authors as $auth): ?>
                                    <option value="<?php echo $auth['id']; ?>" <?php echo intval($book['author_id']) === intval($auth['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($auth['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bookCategory" class="form-label small fw-medium">Category</label>
                            <select class="form-select form-control-custom" id="bookCategory" name="category_id">
                                <option value="">Select Category...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo intval($book['category_id']) === intval($cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="bookDesc" class="form-label small fw-medium">Description</label>
                            <textarea class="form-control form-control-custom" id="bookDesc" name="description" rows="3"><?php echo htmlspecialchars($book['description']); ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="bookPublisher" class="form-label small fw-medium">Publisher</label>
                            <input type="text" class="form-control form-control-custom" id="bookPublisher" name="publisher" value="<?php echo htmlspecialchars($book['publisher']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="bookYear" class="form-label small fw-medium">Publication Year</label>
                            <input type="number" class="form-control form-control-custom" id="bookYear" name="publication_year" value="<?php echo htmlspecialchars($book['publication_year']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="bookShelf" class="form-label small fw-medium">Shelf Location</label>
                            <input type="text" class="form-control form-control-custom" id="bookShelf" name="shelf_location" value="<?php echo htmlspecialchars($book['shelf_location']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="bookQty" class="form-label small fw-medium">Total Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-custom" id="bookQty" name="quantity" min="1" value="<?php echo $book['quantity']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="bookCover" class="form-label small fw-medium">Cover Image</label>
                            <input type="file" class="form-control form-control-custom" id="bookCover" name="cover_image" accept="image/*">
                            <?php if (!empty($book['cover_image'])): ?>
                                <div class="mt-2 small text-muted">Current cover file: <code><?php echo htmlspecialchars($book['cover_image']); ?></code></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-12 mt-3 d-flex justify-content-end gap-2">
                            <a href="<?php echo BASE_URL; ?>/index.php?route=admin/books" class="btn btn-outline-secondary fw-medium px-4">Cancel</a>
                            <button type="submit" class="btn btn-warning fw-medium px-4">Update Book</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <!-- 2. Add Book Form Section -->
    <?php elseif ($addMode): ?>
        <div class="card card-custom border-0 shadow-sm p-4 p-md-5 mb-4">
            <div class="card-body">
                <h4 class="fw-bold mb-4 text-primary"><i class="fa-solid fa-book-medical me-2"></i>Register New Book</h4>
                
                <form action="<?php echo BASE_URL; ?>/index.php?action=add-book" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="bookTitle" class="form-label small fw-medium">Book Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-custom" id="bookTitle" name="title" placeholder="e.g. Clean Code" required>
                        </div>
                        <div class="col-md-4">
                            <label for="bookIsbn" class="form-label small fw-medium">ISBN</label>
                            <input type="text" class="form-control form-control-custom" id="bookIsbn" name="isbn" placeholder="ISBN-10 or 13">
                        </div>
                        <div class="col-md-6">
                            <label for="bookAuthor" class="form-label small fw-medium">Author</label>
                            <select class="form-select form-control-custom" id="bookAuthor" name="author_id">
                                <option value="">Select Author...</option>
                                <?php foreach ($authors as $auth): ?>
                                    <option value="<?php echo $auth['id']; ?>"><?php echo htmlspecialchars($auth['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="bookCategory" class="form-label small fw-medium">Category</label>
                            <select class="form-select form-control-custom" id="bookCategory" name="category_id">
                                <option value="">Select Category...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="bookDesc" class="form-label small fw-medium">Description</label>
                            <textarea class="form-control form-control-custom" id="bookDesc" name="description" rows="3" placeholder="Summary details..."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="bookPublisher" class="form-label small fw-medium">Publisher</label>
                            <input type="text" class="form-control form-control-custom" id="bookPublisher" name="publisher" placeholder="e.g. Prentice Hall">
                        </div>
                        <div class="col-md-4">
                            <label for="bookYear" class="form-label small fw-medium">Publication Year</label>
                            <input type="number" class="form-control form-control-custom" id="bookYear" name="publication_year" placeholder="e.g. 2008">
                        </div>
                        <div class="col-md-4">
                            <label for="bookShelf" class="form-label small fw-medium">Shelf Location</label>
                            <input type="text" class="form-control form-control-custom" id="bookShelf" name="shelf_location" placeholder="e.g. Shelf A-1">
                        </div>
                        <div class="col-md-6">
                            <label for="bookQty" class="form-label small fw-medium">Total Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-custom" id="bookQty" name="quantity" min="1" value="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="bookCover" class="form-label small fw-medium">Cover Image</label>
                            <input type="file" class="form-control form-control-custom" id="bookCover" name="cover_image" accept="image/*">
                        </div>
                        <div class="col-12 mt-3 d-flex justify-content-end gap-2">
                            <a href="<?php echo BASE_URL; ?>/index.php?route=admin/books" class="btn btn-outline-secondary fw-medium px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary fw-medium px-4" style="background: var(--primary-gradient); border: none;">Save Book</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- 3. Books Datatable List Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 mt-4">
        <h4 class="fw-bold mb-0">Book Catalog Inventory</h4>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/index.php?route=admin/google-books" class="btn btn-outline-primary fw-medium" style="border-radius: 10px;">
                <i class="fa-brands fa-google me-2"></i>Import from Google Books
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?route=admin/books&add=1" class="btn btn-primary fw-medium" style="border-radius: 10px; background: var(--primary-gradient); border: none;">
                <i class="fa-solid fa-plus me-2"></i>Add New Book
            </a>
        </div>
    </div>

    <div class="card card-custom border-0 shadow-sm p-4">
        <div class="card-body">
            <?php if (empty($books)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-book-open fs-1 mb-3"></i>
                    <h5 class="fw-bold">No books found in catalog.</h5>
                    <p class="small">Add a book or import one dynamically to get started.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive table-responsive-custom">
                    <table class="table table-custom align-middle datatable-custom w-100">
                        <thead>
                            <tr>
                                <th>Cover</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>ISBN</th>
                                <th>Shelf</th>
                                <th>Copies</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $row): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['cover_image']) && file_exists(UPLOAD_PATH . '/' . $row['cover_image'])): ?>
                                            <img src="<?php echo BASE_URL; ?>/uploads/covers/<?php echo $row['cover_image']; ?>" class="rounded shadow-sm" style="width: 40px; height: 52px; object-fit: cover;" alt="">
                                        <?php elseif (!empty($row['cover_image']) && strpos($row['cover_image'], 'http') === 0): ?>
                                            <img src="<?php echo $row['cover_image']; ?>" class="rounded shadow-sm" style="width: 40px; height: 52px; object-fit: cover;" alt="">
                                        <?php else: ?>
                                            <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 52px;"><i class="fa-solid fa-image" style="font-size: 0.8rem;"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['title']); ?></span>
                                        <span class="text-muted small d-block">Pub: <?php echo htmlspecialchars($row['publisher'] ?: 'N/A'); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['author_name'] ?? 'Unknown Author'); ?></td>
                                    <td><?php echo htmlspecialchars($row['category_name'] ?? 'General'); ?></td>
                                    <td><code><?php echo htmlspecialchars($row['isbn'] ?: 'N/A'); ?></code></td>
                                    <td><?php echo htmlspecialchars($row['shelf_location'] ?: 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-light-custom text-dark border px-2 py-1">
                                            <strong><?php echo $row['available_copies']; ?></strong> / <?php echo $row['quantity']; ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="<?php echo BASE_URL; ?>/index.php?route=book-details&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info" title="Details"><i class="fa-solid fa-eye"></i></a>
                                            <a href="<?php echo BASE_URL; ?>/index.php?route=admin/books&edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                            <button class="btn btn-sm btn-outline-danger" onclick="window.confirmAction('Delete Book', 'Are you sure you want to delete this book listing? Associated checkouts will block this.', 'Delete', '<?php echo BASE_URL; ?>/index.php?action=delete-book&id=<?php echo $row['id']; ?>')" title="Delete"><i class="fa-solid fa-trash"></i></button>
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
