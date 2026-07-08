<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$memberModel = new Member();
$members = $memberModel->getAll();


$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$editMember = null;
if ($editId > 0) {
    $editMember = $memberModel->findById($editId);
}
?>

<div class="fade-in-up">
  
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Members</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Add/Edit Member Panel-->
        <div class="col-lg-4">
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="card-body">
                    <?php if ($editMember): ?>
                        <h4 class="fw-bold mb-4 text-warning"><i class="fa-solid fa-user-pen me-2"></i>Edit Member Profile</h4>
                        
                        <form action="<?php echo BASE_URL; ?>/index.php?action=edit-member" method="POST">
                            <input type="hidden" name="id" value="<?php echo $editMember['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Email Address</label>
                                <input type="email" class="form-control form-control-custom text-muted bg-light" value="<?php echo htmlspecialchars($editMember['email']); ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="memName" class="form-label small fw-medium">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="memName" name="full_name" value="<?php echo htmlspecialchars($editMember['full_name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="memPhone" class="form-label small fw-medium">Phone Number</label>
                                <input type="text" class="form-control form-control-custom" id="memPhone" name="phone" value="<?php echo htmlspecialchars($editMember['phone'] ?: ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="memAddr" class="form-label small fw-medium">Residential Address</label>
                                <textarea class="form-control form-control-custom" id="memAddr" name="address" rows="3"><?php echo htmlspecialchars($editMember['address'] ?: ''); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="memStatus" class="form-label small fw-medium">Membership Status</label>
                                <select class="form-select form-control-custom" id="memStatus" name="status">
                                    <option value="active" <?php echo $editMember['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $editMember['status'] === 'inactive' ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning fw-medium">Save Changes</button>
                                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/members" class="btn btn-outline-secondary fw-medium">Cancel Edit</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <h4 class="fw-bold mb-4 text-primary"><i class="fa-solid fa-user-plus me-2"></i>Register Member</h4>
                        
                        <form action="<?php echo BASE_URL; ?>/index.php?action=add-member" method="POST">
                            <div class="mb-3">
                                <label for="memName" class="form-label small fw-medium">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-custom" id="memName" name="full_name" placeholder="John Doe" required>
                            </div>

                            <div class="mb-3">
                                <label for="memEmail" class="form-label small fw-medium">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-custom" id="memEmail" name="email" placeholder="john@example.com" required>
                            </div>

                            <div class="mb-3">
                                <label for="memPass" class="form-label small fw-medium">Initial Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-custom" id="memPass" name="password" placeholder="At least 6 characters" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="memPhone" class="form-label small fw-medium">Phone Number</label>
                                <input type="text" class="form-control form-control-custom" id="memPhone" name="phone" placeholder="+1 234 5678">
                            </div>

                            <div class="mb-3">
                                <label for="memAddr" class="form-label small fw-medium">Residential Address</label>
                                <textarea class="form-control form-control-custom" id="memAddr" name="address" rows="3" placeholder="Street name, City"></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="memStatus" class="form-label small fw-medium">Membership Status</label>
                                <select class="form-select form-control-custom" id="memStatus" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Suspended</option>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary fw-medium" style="border-radius: 10px; background: var(--primary-gradient); border: none;">Add Member</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Members Index Table-->
        <div class="col-lg-8">
            <div class="card card-custom border-0 shadow-sm p-4">
                <div class="card-body">
                    <h4 class="fw-bold mb-4">Registered Members</h4>
                    
                    <?php if (empty($members)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users-slash fs-1 mb-3"></i>
                            <p class="mb-0">No members registered in the database.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive table-responsive-custom">
                            <table class="table table-custom align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Contact Email</th>
                                        <th>Phone</th>
                                        <th>Registration</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($members as $row): ?>
                                        <tr class="<?php echo $editId === intval($row['id']) ? 'table-warning' : ''; ?>">
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                                        <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                                    </div>
                                                    <span class="fw-bold text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><code><?php echo htmlspecialchars($row['email']); ?></code></td>
                                            <td><?php echo htmlspecialchars($row['phone'] ?: 'N/A'); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($row['registration_date'])); ?></td>
                                            <td>
                                                <?php if ($row['status'] === 'active'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Suspended</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="<?php echo BASE_URL; ?>/index.php?route=admin/members&edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit Profile"><i class="fa-solid fa-pen"></i></a>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="window.confirmAction('Delete Member', 'Are you sure you want to delete member \'<?php echo addslashes($row['full_name']); ?>\'? This deletes associated login credentials completely.', 'Delete', '<?php echo BASE_URL; ?>/index.php?action=delete-member&id=<?php echo $row['id']; ?>')" title="Delete Member"><i class="fa-solid fa-trash"></i></button>
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
