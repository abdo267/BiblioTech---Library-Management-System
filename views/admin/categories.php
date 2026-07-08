<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$categoryModel = new Category();
$categories = $categoryModel->getAll();


$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editCategory = null;
if ($editId > 0) {
    $editCategory = $categoryModel->findById($editId);
}
?>

<div class="fade-in-up">
  
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Categories</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Add/Edit Category Panel -->
        <div class="col-lg-4">
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="card-body">
                    <?php if ($editCategory): ?>
                        <h4 class="fw-bold mb-4 text-warning"><i class="fa-solid fa-tags me-2"></i>Edit Category</h4>
                        
                        <form action="<?php echo BASE_URL; ?>/index.php?action=edit-category" method="POST">
                            <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                            
                            <div class="mb-4">
                                <label for="catName" class="form-label small fw-medium">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="catName" name="name" value="<?php echo htmlspecialchars($editCategory['name']); ?>" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning fw-medium">Update Category</button>
                                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/categories" class="btn btn-outline-secondary fw-medium">Cancel Edit</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <h4 class="fw-bold mb-4 text-primary"><i class="fa-solid fa-folder-plus me-2"></i>Create Category</h4>
                        
                        <form action="<?php echo BASE_URL; ?>/index.php?action=add-category" method="POST">
                            <div class="mb-4">
                                <label for="catName" class="form-label small fw-medium">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="catName" name="name" placeholder="e.g. Programming, Novels" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary fw-medium" style="border-radius: 10px; background: var(--primary-gradient); border: none;">Create Category</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Categories Index Table -->
        <div class="col-lg-8">
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-4">Categories List</h4>
                    
                    <?php if (empty($categories)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-tags fs-1 mb-3"></i>
                            <p class="mb-0">No categories found in database.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive table-responsive-custom">
                            <table class="table table-custom align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Category ID</th>
                                        <th>Name</th>
                                        <th>Books Associated</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $row): ?>
                                        <tr class="<?php echo $editId === intval($row['id']) ? 'table-warning' : ''; ?>">
                                            <td><code>#<?php echo $row['id']; ?></code></td>
                                            <td><strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                            <td><span class="badge bg-light-custom text-dark border px-2 py-1"><?php echo $row['book_count']; ?> books</span></td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin/categories&edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="window.confirmAction('Delete Category', 'Are you sure you want to delete category \'<?php echo addslashes($row['name']); ?>\'? Associated books will have their category set to NULL.', 'Delete', '<?php echo BASE_URL; ?>/index.php?action=delete-category&id=<?php echo $row['id']; ?>')" title="Delete"><i class="fa-solid fa-trash"></i></button>
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
