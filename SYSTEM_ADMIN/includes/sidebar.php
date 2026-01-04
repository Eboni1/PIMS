<?php
// Get current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            <div class="sidebar-logo">
                <img src="../img/trans_logo.png" alt="PIMS Logo" class="img-fluid" style="max-height: 40px; border-radius: 8px;">
            </div>
            <div class="sidebar-title">
                <h6 class="mb-0 text-white">PIMS</h6>
                <small class="text-white-50">Inventory System</small>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>
        <a href="user_management.php" class="sidebar-nav-item <?php echo $current_page == 'user_management.php' ? 'active' : ''; ?>">
            <i class="bi bi-people"></i>
            User Management
        </a>
        <a href="#" class="sidebar-nav-item">
            <i class="bi bi-box"></i>
            Inventory Management
        </a>
        <a href="#" class="sidebar-nav-item">
            <i class="bi bi-tags"></i>
            Categories
        </a>
        <a href="#" class="sidebar-nav-item">
            <i class="bi bi-arrow-left-right"></i>
            Transactions
        </a>
        <a href="#" class="sidebar-nav-item">
            <i class="bi bi-file-text"></i>
            Reports
        </a>
        <a href="#" class="sidebar-nav-item">
            <i class="bi bi-gear"></i>
            System Settings
        </a>
        <a href="#" class="sidebar-nav-item">
            <i class="bi bi-shield-exclamation"></i>
            Security Audit
        </a>
        <a href="#" class="sidebar-nav-item">
            <i class="bi bi-cloud-download"></i>
            Backup System
        </a>
        <a href="logs.php" class="sidebar-nav-item <?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">
            <i class="bi bi-clock-history"></i>
            System Logs
        </a>
        <div class="sidebar-nav-item" style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 2rem;">
            <i class="bi bi-box-arrow-right"></i>
            <a href="../logout.php" onclick="event.preventDefault(); confirmLogout();" style="color: inherit; text-decoration: none;">Logout</a>
        </div>
    </nav>
</aside>
