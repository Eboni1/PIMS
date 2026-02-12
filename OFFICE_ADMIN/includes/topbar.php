<!-- Topbar -->
<header class="topbar" id="topbar">
    <div class="topbar-left">
        <!-- Sidebar Toggle Button -->
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-title">
            <h5 class="mb-0"><?php echo $page_title ?? 'Office Admin'; ?></h5>
        </div>
    </div>
    
    <div class="topbar-right">
        <!-- Search -->
        <div class="topbar-search">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search...">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
        
        <!-- Notifications -->
        <div class="topbar-notifications dropdown">
            <button class="btn btn-link position-relative notification-bell" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" id="notificationBadge" style="display: none;">0</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                <li class="notification-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Notifications</h6>
                        <div class="notification-actions">
                            <a href="#" class="mark-all-read" title="Mark all as read">
                                <i class="bi bi-check2-all"></i>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="notification-list" id="notificationList">
                        <div class="notification-loading">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="notification-footer">
                    <a class="dropdown-item text-center" href="../ADMIN/notifications.php">View All Notifications</a>
                </li>
            </ul>
        </div>
        
        <!-- User Menu -->
        <div class="topbar-user dropdown">
            <button class="btn btn-link d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo $_SESSION['first_name'] ?? 'User'; ?></div>
                    <div class="user-role">Office Admin</div>
                </div>
                <i class="bi bi-chevron-down ms-2"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="profile.php">
                    <i class="bi bi-person me-2"></i> Profile
                </a></li>
                <li><a class="dropdown-item" href="settings.php">
                    <i class="bi bi-gear me-2"></i> Settings
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a></li>
            </ul>
        </div>
    </div>
</header>
