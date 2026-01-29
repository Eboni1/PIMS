<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark" id="mainNavbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="bi bi-speedometer2"></i>
            <?php echo ucfirst($page_title ?? 'Dashboard'); ?>
        </a>
        
        <div class="navbar-nav ms-auto align-items-center">
            <!-- QR Scanner -->
            <div class="nav-item me-3">
                <a href="scan_qr.php" class="nav-link text-white" title="QR Scanner">
                    <i class="bi bi-qr-code-scan"></i>
                </a>
            </div>
            
            <!-- Search Bar -->
            <div class="nav-item me-3">
                <div class="search-container position-relative">
                    <form class="d-flex" action="search_handler.php" method="GET" id="searchForm">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" id="searchInput" 
                                   placeholder="Search..." autocomplete="off"
                                   value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                            <button class="btn btn-outline-light" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                    <!-- Search Suggestions Dropdown -->
                    <div class="search-suggestions" id="searchSuggestions"></div>
                </div>
            </div>
            
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                    <div class="user-avatar me-3">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></div>
                        <div class="user-role">
                            <?php 
                            $role = htmlspecialchars(ucfirst(str_replace('_', ' ', $_SESSION['role'])));
                            $badge_class = 'bg-secondary';
                            if ($_SESSION['role'] === 'system_admin') {
                                $badge_class = 'bg-danger';
                            } elseif ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'office_admin') {
                                $badge_class = 'bg-warning text-dark';
                            } elseif ($_SESSION['role'] === 'user') {
                                $badge_class = 'bg-success';
                            }
                            ?>
                            <span class="badge <?php echo $badge_class; ?>"><?php echo $role; ?></span>
                        </div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-key"></i> Change Password</a></li>
                    <li><a class="dropdown-item" href="system_settings.php"><i class="bi bi-gear"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
.search-container .form-control {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    border-radius: var(--border-radius);
    width: 250px;
    transition: width 0.3s ease;
}

.search-container .form-control:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
    color: white;
    width: 300px;
}

.search-container .form-control::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.search-container .btn-outline-light {
    border-color: rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.8);
}

.search-container .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
}

/* Search Suggestions Dropdown */
.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0 0 var(--border-radius) var(--border-radius);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1050;
    display: none;
}

.search-suggestion-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: background-color 0.2s ease;
    color: #333;
}

.search-suggestion-item:hover {
    background-color: #f8f9fa;
}

.search-suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-type {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
    margin-left: 0.5rem;
}

.suggestion-asset {
    background: linear-gradient(135deg, #191BA9 0%, #5CC2F2 100%);
    color: white;
}

.suggestion-employee {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.suggestion-text {
    font-weight: 500;
}

.suggestion-meta {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.no-suggestions {
    padding: 1rem;
    text-align: center;
    color: #6c757d;
    font-size: 0.9rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .search-container .form-control {
        width: 180px;
    }
    
    .search-container .form-control:focus {
        width: 200px;
    }
    
    .navbar-brand {
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .search-container .form-control {
        width: 150px;
    }
    
    .search-container .form-control:focus {
        width: 170px;
    }
    
    .search-container .form-control::placeholder {
        font-size: 0.8rem;
    }
}
</style>

<script>
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const searchSuggestions = document.getElementById('searchSuggestions');
const searchForm = document.getElementById('searchForm');

// Search suggestions functionality
searchInput.addEventListener('input', function() {
    const query = this.value.trim();
    
    clearTimeout(searchTimeout);
    
    if (query.length < 2) {
        searchSuggestions.style.display = 'none';
        return;
    }
    
    // Debounce search requests
    searchTimeout = setTimeout(() => {
        fetchSuggestions(query);
    }, 300);
});

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-container')) {
        searchSuggestions.style.display = 'none';
    }
});

function fetchSuggestions(query) {
    fetch('search_suggestions.php?q=' + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => {
            displaySuggestions(data);
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
            searchSuggestions.style.display = 'none';
        });
}

function displaySuggestions(suggestions) {
    if (suggestions.length === 0) {
        searchSuggestions.innerHTML = '<div class="no-suggestions">No results found</div>';
        searchSuggestions.style.display = 'block';
        return;
    }
    
    let html = '';
    suggestions.forEach(item => {
        const typeClass = item.type === 'asset' ? 'suggestion-asset' : 'suggestion-employee';
        const typeText = item.type === 'asset' ? 'Asset' : 'Employee';
        
        html += `
            <div class="search-suggestion-item" onclick="selectSuggestion('${item.url}')">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="suggestion-text">${highlightMatch(item.text, searchInput.value.trim())}</div>
                        ${item.meta ? `<div class="suggestion-meta">${item.meta}</div>` : ''}
                    </div>
                    <span class="suggestion-type ${typeClass}">${typeText}</span>
                </div>
            </div>
        `;
    });
    
    searchSuggestions.innerHTML = html;
    searchSuggestions.style.display = 'block';
}

function highlightMatch(text, query) {
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<strong>$1</strong>');
}

function selectSuggestion(url) {
    window.location.href = url;
}

// Handle form submission
searchForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const query = searchInput.value.trim();
    if (query) {
        window.location.href = `search_handler.php?q=${encodeURIComponent(query)}`;
    }
});

// Keyboard navigation
let currentSuggestionIndex = -1;

searchInput.addEventListener('keydown', function(e) {
    const items = searchSuggestions.querySelectorAll('.search-suggestion-item');
    
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        currentSuggestionIndex = Math.min(currentSuggestionIndex + 1, items.length - 1);
        updateActiveSuggestion(items);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        currentSuggestionIndex = Math.max(currentSuggestionIndex - 1, -1);
        updateActiveSuggestion(items);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (currentSuggestionIndex >= 0 && items[currentSuggestionIndex]) {
            items[currentSuggestionIndex].click();
        } else {
            searchForm.dispatchEvent(new Event('submit'));
        }
    } else if (e.key === 'Escape') {
        searchSuggestions.style.display = 'none';
        currentSuggestionIndex = -1;
    }
});

function updateActiveSuggestion(items) {
    items.forEach((item, index) => {
        if (index === currentSuggestionIndex) {
            item.style.backgroundColor = '#e9ecef';
        } else {
            item.style.backgroundColor = '';
        }
    });
}
</script>
