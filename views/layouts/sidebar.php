<?php


if (!defined('SECURE_ACCESS')) {
    exit('No direct script access allowed');
}

$currentRoute = $_GET['route'] ?? '';
?>
<div class="admin-sidebar d-flex flex-column" id="adminSidebar">
    <div class="sidebar-header py-4 px-4 d-flex align-items-center justify-content-between">
        <a class="d-flex align-items-center fw-bold text-white fs-4 text-decoration-none" href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard">
            <i class="fa-solid fa-book-open me-2 text-primary fs-3"></i>
            <span>BiblioTech</span>
        </a>
    </div>
    
    <div class="flex-grow-1 py-3" style="overflow-y: auto;">
        <div class="px-4 mb-2 text-uppercase text-xs fw-semibold tracking-wider text-muted small">Menu</div>
        
        <ul class="nav flex-column mb-4">
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/dashboard" class="nav-link <?php echo $currentRoute === 'admin/dashboard' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        <div class="px-4 mb-2 text-uppercase text-xs fw-semibold tracking-wider text-muted small">Catalog</div>
        
        <ul class="nav flex-column mb-4">
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/books" class="nav-link <?php echo (strpos($currentRoute, 'admin/books') === 0) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-book"></i>
                    <span>Books</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/google-books" class="nav-link <?php echo $currentRoute === 'admin/google-books' ? 'active' : ''; ?>">
                    <i class="fa-brands fa-google"></i>
                    <span>Google Books Import</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/authors" class="nav-link <?php echo $currentRoute === 'admin/authors' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-user-pen"></i>
                    <span>Authors</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/categories" class="nav-link <?php echo $currentRoute === 'admin/categories' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
        </ul>

        <div class="px-4 mb-2 text-uppercase text-xs fw-semibold tracking-wider text-muted small">Circulation & Members</div>
        
        <ul class="nav flex-column mb-4">
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/borrow-requests" class="nav-link <?php echo $currentRoute === 'admin/borrow-requests' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-rotate"></i>
                    <span>Borrow Requests</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/members" class="nav-link <?php echo $currentRoute === 'admin/members' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i>
                    <span>Members</span>
                </a>
            </li>
        </ul>

        <div class="px-4 mb-2 text-uppercase text-xs fw-semibold tracking-wider text-muted small">Administration</div>
        
        <ul class="nav flex-column mb-3">
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/reports" class="nav-link <?php echo $currentRoute === 'admin/reports' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Reports & Statistics</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>/index.php?route=admin/settings" class="nav-link <?php echo $currentRoute === 'admin/settings' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-history"></i>
                    <span>Activity Logs</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<script>
   
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('sidebarCollapseBtn');
        const sidebar = document.getElementById('adminSidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.toggle('active-mobile');
            });
          
            document.addEventListener('click', (e) => {
                if (sidebar.classList.contains('active-mobile') && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                    sidebar.classList.remove('active-mobile');
                }
            });
        }
    });
</script>

<style>
   
    @media (max-width: 991.98px) {
        .admin-sidebar {
            position: fixed;
            left: -280px;
            z-index: 1040;
            height: 100vh;
        }
        .admin-sidebar.active-mobile {
            left: 0;
            box-shadow: 5px 0 15px rgba(0,0,0,0.2);
        }
    }
</style>
