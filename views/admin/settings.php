<?php

if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$logModel = new ActivityLog();
$logs = $logModel->getRecentLogs(150); 
?>

<div class="fade-in-up">
 
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Audit & Activity Logs</li>
        </ol>
    </nav>

    <div class="card card-custom border-0 shadow-sm p-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i>System Activity Audit Log</h4>
                <span class="badge bg-secondary px-3 py-2"><?php echo count($logs); ?> logs</span>
            </div>

            <?php if (empty($logs)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-history fs-1 mb-3"></i>
                    <p class="mb-0">No system activities recorded yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive table-responsive-custom">
                    <table class="table table-custom align-middle datatable-custom w-100">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Performed By</th>
                                <th>Action Event</th>
                                <th>Details / Parameters</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $row): ?>
                                <tr>
                                    <td><?php echo date('M d, Y H:i:s', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <?php if ($row['email']): ?>
                                            <span class="fw-bold text-dark-theme-override" style="color: var(--text-color);"><?php echo htmlspecialchars($row['email']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small italic">System System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $action = $row['action'];
                                        $badgeClass = 'bg-secondary';
                                        
                                        if (strpos($action, 'Approve') !== false || strpos($action, 'Return') !== false) {
                                            $badgeClass = 'bg-success';
                                        } elseif (strpos($action, 'Delete') !== false || strpos($action, 'Reject') !== false) {
                                            $badgeClass = 'bg-danger';
                                        } elseif (strpos($action, 'Add') !== false || strpos($action, 'Register') !== false) {
                                            $badgeClass = 'bg-primary';
                                        } elseif (strpos($action, 'Login') !== false) {
                                            $badgeClass = 'bg-info text-dark';
                                        }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> px-2.5 py-1.5"><?php echo htmlspecialchars($action); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-muted small"><?php echo htmlspecialchars($row['details']); ?></span>
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
