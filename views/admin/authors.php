<?php

if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$authorModel = new Author();
$authors = $authorModel->getAll();


$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editAuthor = null;
if ($editId > 0) {
    $editAuthor = $authorModel->findById($editId);
}
?>

<div class="fade-in-up">
  
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Authors</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Add/Edit Author Panel -->
        <div class="col-lg-4">
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="card-body">
                    <?php if ($editAuthor): ?>
                        <h4 class="fw-bold mb-4 text-warning"><i class="fa-solid fa-user-pen me-2"></i>Edit Author Details</h4>
                        
                        <form action="<?php echo BASE_URL; ?>/index.php?action=edit-author" method="POST">
                            <input type="hidden" name="id" value="<?php echo $editAuthor['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="authName" class="form-label small fw-medium">Author Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="authName" name="name" value="<?php echo htmlspecialchars($editAuthor['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="authNat" class="form-label small fw-medium">Nationality</label>
                                <input type="text" class="form-control form-control-custom" id="authNat" name="nationality" value="<?php echo htmlspecialchars($editAuthor['nationality'] ?: ''); ?>" placeholder="e.g. American">
                            </div>

                            <div class="mb-4">
                                <label for="authBio" class="form-label small fw-medium">Short Biography</label>
                                <textarea class="form-control form-control-custom" id="authBio" name="biography" rows="5" placeholder="Biography details..."><?php echo htmlspecialchars($editAuthor['biography'] ?: ''); ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning fw-medium">Update Author</button>
                                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/authors" class="btn btn-outline-secondary fw-medium">Cancel Edit</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <h4 class="fw-bold mb-4 text-primary"><i class="fa-solid fa-user-plus me-2"></i>Register Author</h4>
                        
                        <form action="<?php echo BASE_URL; ?>/index.php?action=add-author" method="POST">
                            <div class="mb-3">
                                <label for="authName" class="form-label small fw-medium">Author Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="authName" name="name" placeholder="e.g. Robert C. Martin" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="authNat" class="form-label small fw-medium">Nationality</label>
                                <input type="text" class="form-control form-control-custom" id="authNat" name="nationality" placeholder="e.g. American">
                            </div>

                            <div class="mb-4">
                                <label for="authBio" class="form-label small fw-medium">Short Biography</label>
                                <textarea class="form-control form-control-custom" id="authBio" name="biography" rows="5" placeholder="Brief biography details..."></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary fw-medium" style="border-radius: 10px; background: var(--primary-gradient); border: none;">Add Author</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Authors Index Table -->
        <div class="col-lg-8">
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-4">Authors List</h4>
                    
                    <?php if (empty($authors)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users-slash fs-1 mb-3"></i>
                            <p class="mb-0">No author records available in database.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive table-responsive-custom">
                            <table class="table table-custom align-middle datatable-custom w-100">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Nationality</th>
                                        <th>Biography</th>
                                        <th>Books Count</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($authors as $row): ?>
                                        <tr class="<?php echo $editId === intval($row['id']) ? 'table-warning' : ''; ?>">
                                            <td><strong class="text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['nationality'] ?: 'N/A'); ?></td>
                                            <td><span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?php echo htmlspecialchars($row['biography'] ?: ''); ?>"><?php echo htmlspecialchars($row['biography'] ?: 'N/A'); ?></span></td>
                                            <td><span class="badge bg-light-custom text-dark border px-2 py-1"><?php echo $row['book_count']; ?> books</span></td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin/authors&edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="window.confirmAction('Delete Author', 'Are you sure you want to delete author \'<?php echo addslashes($row['name']); ?>\'? This will set author to NULL in associated book records.', 'Delete', '<?php echo BASE_URL; ?>/index.php?action=delete-author&id=<?php echo $row['id']; ?>')" title="Delete"><i class="fa-solid fa-trash"></i></button>
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
