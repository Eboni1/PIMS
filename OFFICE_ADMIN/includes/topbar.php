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

<style>
/* Notification Bell Styles */
.notification-bell {
    font-size: 1.2rem;
    transition: transform 0.2s ease;
    color: #6c757d;
}

.notification-bell:hover {
    transform: scale(1.1);
    color: var(--primary-color, #191BA9);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 0.7rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid rgba(255, 255, 255, 0.9);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Notification Dropdown Styles */
.notification-dropdown {
    width: 350px;
    max-height: 400px;
    border: none;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    border-radius: var(--border-radius, 8px);
    overflow: hidden;
}

.notification-header {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.notification-header h6 {
    color: #495057;
    font-weight: 600;
    font-size: 0.9rem;
}

.notification-actions a {
    color: #6c757d;
    text-decoration: none;
    padding: 0.25rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.notification-actions a:hover {
    background: #e9ecef;
    color: #495057;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.2s ease;
    cursor: pointer;
    position: relative;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 3px solid var(--primary-color, #191BA9);
}

.notification-item.unread::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0.5rem;
    width: 8px;
    height: 8px;
    background: var(--primary-color, #191BA9);
    border-radius: 50%;
    transform: translateY(-50%);
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.notification-message {
    color: #6c757d;
    font-size: 0.85rem;
    line-height: 1.4;
    margin-bottom: 0.25rem;
}

.notification-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    color: #adb5bd;
}

.notification-type {
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.notification-type.info { background: #d1ecf1; color: #0c5460; }
.notification-type.success { background: #d4edda; color: #155724; }
.notification-type.warning { background: #fff3cd; color: #856404; }
.notification-type.error { background: #f8d7da; color: #721c24; }
.notification-type.system { background: #e2e3e5; color: #383d41; }

.notification-actions-item {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.notification-actions-item button {
    padding: 0.25rem 0.5rem;
    border: none;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.notification-actions-item .btn-mark-read {
    background: #28a745;
    color: white;
}

.notification-actions-item .btn-delete {
    background: #dc3545;
    color: white;
}

.notification-actions-item button:hover {
    opacity: 0.8;
}

.notification-loading {
    padding: 2rem;
    text-align: center;
}

.notification-empty {
    padding: 2rem;
    text-align: center;
    color: #6c757d;
}

.notification-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e9ecef;
    text-align: center;
    background: #f8f9fa;
}

.notification-footer a {
    color: var(--primary-color, #191BA9);
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
}

.notification-footer a:hover {
    text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .notification-dropdown {
        width: 300px;
    }
    
    .notification-title {
        font-size: 0.85rem;
    }
    
    .notification-message {
        font-size: 0.8rem;
    }
}
</style>

<script>
// Notification System
let notificationDropdown;
let notificationList;
let notificationBadge;
let notificationTimeout;

document.addEventListener('DOMContentLoaded', function() {
    notificationDropdown = document.getElementById('notificationDropdown');
    notificationList = document.getElementById('notificationList');
    notificationBadge = document.getElementById('notificationBadge');
    
    // Initialize notification system
    updateNotificationBadge();
    
    // Setup dropdown events
    const notificationBell = document.querySelector('.notification-bell');
    if (notificationBell) {
        notificationBell.addEventListener('click', function() {
            loadNotifications();
        });
    }
    
    // Setup mark all as read
    const markAllReadBtn = document.querySelector('.mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            markAllNotificationsAsRead();
        });
    }
    
    // Auto-refresh notification count every 30 seconds
    setInterval(updateNotificationBadge, 30000);
});

function updateNotificationBadge() {
    fetch('../ADMIN/notifications_handler.php?action=get_count', {
        credentials: 'include'  // Include cookies for session
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Notification count response:', data);
            const count = data.unread_count || 0;
            if (count > 0) {
                notificationBadge.textContent = count > 99 ? '99+' : count;
                notificationBadge.style.display = 'flex';
            } else {
                notificationBadge.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching notification count:', error);
            // Hide badge on error to prevent confusion
            notificationBadge.style.display = 'none';
        });
}

function loadNotifications() {
    // Show loading state
    notificationList.innerHTML = `
        <div class="notification-loading">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    fetch('../ADMIN/notifications_handler.php?action=get_notifications&limit=10', {
        credentials: 'include'  // Include cookies for session
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Notifications response:', data);
            if (data.error) {
                throw new Error(data.error);
            }
            displayNotifications(data.notifications);
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            notificationList.innerHTML = `
                <div class="notification-empty">
                    <i class="bi bi-exclamation-triangle"></i>
                    <p>Error loading notifications</p>
                    <small>${error.message}</small>
                </div>
            `;
        });
}

function displayNotifications(notifications) {
    if (notifications.length === 0) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <i class="bi bi-bell-slash"></i>
                <p>No notifications</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        const unreadClass = notification.is_read ? '' : 'unread';
        const typeClass = `notification-type ${notification.type}`;
        
        html += `
            <div class="notification-item ${unreadClass}" data-id="${notification.id}">
                <div class="notification-content">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-meta">
                        <span class="notification-time">${notification.time_ago}</span>
                        <span class="${typeClass}">${notification.type}</span>
                    </div>
                    ${!notification.is_read ? `
                        <div class="notification-actions-item">
                            <button class="btn-mark-read" onclick="markNotificationAsRead(${notification.id})">
                                <i class="bi bi-check"></i> Mark as read
                            </button>
                            <button class="btn-delete" onclick="deleteNotificationItem(${notification.id})">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    notificationList.innerHTML = html;
    
    // Add click handlers for notification items
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // Don't trigger if clicking on action buttons
            if (e.target.closest('.notification-actions-item')) {
                return;
            }
            
            const notificationId = this.dataset.id;
            const isUnread = this.classList.contains('unread');
            
            if (isUnread) {
                markNotificationAsRead(notificationId);
            }
            
            // Navigate to related URL if available
            // This would need to be implemented based on notification's action_url
        });
    });
}

function markNotificationAsRead(notificationId) {
    fetch('../ADMIN/notifications_handler.php?action=mark_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `notification_id=${notificationId}`,
        credentials: 'include'  // Include cookies for session
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove unread class and actions
            const item = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
            if (item) {
                item.classList.remove('unread');
                const actions = item.querySelector('.notification-actions-item');
                if (actions) {
                    actions.remove();
                }
            }
            updateNotificationBadge();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllNotificationsAsRead() {
    fetch('../ADMIN/notifications_handler.php?action=mark_all_read', {
        method: 'POST',
        credentials: 'include'  // Include cookies for session
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload notifications to show updated state
            loadNotifications();
            updateNotificationBadge();
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function deleteNotificationItem(notificationId) {
    fetch('../ADMIN/notifications_handler.php?action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `notification_id=${notificationId}`,
        credentials: 'include'  // Include cookies for session
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove notification item
            const item = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
            if (item) {
                item.remove();
                
                // Check if there are no more notifications
                const remainingItems = document.querySelectorAll('.notification-item');
                if (remainingItems.length === 0) {
                    notificationList.innerHTML = `
                        <div class="notification-empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>No notifications</p>
                        </div>
                    `;
                }
            }
            updateNotificationBadge();
        }
    })
    .catch(error => {
        console.error('Error deleting notification:', error);
    });
}
</script>
