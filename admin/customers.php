<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// --- Pagination setup ---
$logs_per_page = 6; // items per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $logs_per_page;

$count_query = "SELECT COUNT(*) as total FROM users";
$count_result = $conn->query($count_query);
$total_items = $count_result ? intval($count_result->fetch_assoc()['total']) : 0;

// --- Calculate total pages ---
$total_pages = ceil($total_items / $logs_per_page);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    
    <link rel="stylesheet" href="assets/css/admintoapprove.css">
   <?php include "includes/link-css.php";?>
    
    <style>
         .image-preview {
        margin-bottom: 20px;
        text-align: center;
        border-radius: 8px;
        overflow: hidden;
        max-height: 200px;
        background: transparent;
        
    }
    
    .image-preview img {
        max-width: 70%;
        max-height: 100px;
        object-fit: contain;
    }
    
    /* Modern File Input Styles */
    .modern-file-input {
        position: relative;
        margin-bottom: 15px;
    }
    
    .modern-file-input input[type="file"] {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
    }
    
    .modern-file-input label {
        display: flex;
        align-items: center;
        width: 100%;
        cursor: pointer;
    }
    
    .file-input-label {
        flex-grow: 1;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px 0 0 4px;
        background: #f8f9fa;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .file-input-btn {
        padding: 8px 16px;
        background: #e9ecef;
        border: 1px solid #ddd;
        border-left: none;
        border-radius: 0 4px 4px 0;
        font-weight: 500;
    }
    
    .modern-file-input:hover .file-input-btn {
        background: #dee2e6;
    }

    .search-container {
        position: relative;
        display: flex;
        align-items: center;
        margin-right: 10px;
    }
    
    .search-icon {
        position: absolute;
        left: 12px;
        color: #6c757d;
        z-index: 1;
    }
    
    .search-input {
        padding: 8px 12px 8px 40px;
        border: 1px solid #ddd;
        border-radius: 4px;
        width: 250px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #4A90E2;
        box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
    }

    .search-input {
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .table-responsive {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .text-center {
        text-align: center;
    }
    
    .table-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    </style>

</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <button class="mobile-menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

        <?php include "includes/sidebar.php";?>
    
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Main Content -->
        <main class="main">
            <header class="header">
                <h1 class="header-dashboard">Customers</h1>
                
                <div class="user-menu">
                <div class="theme-toggle" id="themeToggle" style="display:none;">
                <span style="margin-right:8px;" style="display:none;">Dark Mode</span>
                <i class="fas fa-moon"></i>
            </div>
                    
          <?php include "includes/notification.php";?>

    </div>
                    
                   <?php include "includes/profile.php";?>
                </div>
            </header>
            
            <!-- Table -->
            <section class="table-card fade-in">
    <div class="table-header">
        <h3 class="table-title">Customer Records</h3>
        <div class="table-actions">
            <!-- Search will be added here via JavaScript -->
        </div>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Completed Orders</th>
                </tr>
            </thead>
            <tbody id="UsersTableBody">
                <!-- Data will be populated here from JavaScript -->
                <tr>
                    <td colspan="3" class="text-center">Loading users...</td>
                </tr>
            </tbody>
        </table>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="btn btn-outline">&laquo; Prev</a>
            <?php endif; ?>

            <!-- Always show first page -->
            <a href="?page=1&search=<?= urlencode($search) ?>" class="btn <?= $page == 1 ? 'btn-primary' : 'btn-outline' ?>">1</a>

            <!-- Dots -->
            <?php if ($page > 3): ?>
                <span class="dots">...</span>
            <?php endif; ?>

            <!-- Pages around current -->
            <?php for ($i = max(2, $page - 2); $i <= min($total_pages - 1, $page + 2); $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <!-- Dots -->
            <?php if ($page < $total_pages - 2): ?>
                <span class="dots">...</span>
            <?php endif; ?>

            <!-- Always show last page -->
            <?php if ($total_pages > 1): ?>
                <a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>" class="btn <?= $page == $total_pages ? 'btn-primary' : 'btn-outline' ?>">
                    <?= $total_pages ?>
                </a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="btn btn-outline">Next &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
</section>
        </main>
    </div>
    
</div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

<script>
    function refreshUsersTable(searchTerm = '') {
        fetch('get_users.php' + (searchTerm ? `?search=${encodeURIComponent(searchTerm)}` : ''))
            .then(response => response.json())
            .then(users => {
                const tableBody = document.getElementById('UsersTableBody');
                tableBody.innerHTML = ''; // Clear current rows
                
                if (users.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="3" class="text-center">No users found</td></tr>';
                    return;
                }
                
                users.forEach(user => {
                    const row = document.createElement('tr');
                    
                    // Name column
                    const nameCell = document.createElement('td');
                    nameCell.textContent = user.name;
                    
                    // Email column
                    const emailCell = document.createElement('td');
                    emailCell.textContent = user.email;
                    
                    // Completed Orders column
                    const ordersCell = document.createElement('td');
                    ordersCell.textContent = user.completed_orders;
                    
                    // Append all cells to the row
                    row.appendChild(nameCell);
                    row.appendChild(emailCell);
                    row.appendChild(ordersCell);
                    
                    // Append row to table
                    tableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error loading users:', error);
                showToast('Error', 'Failed to load users', 'error');
            });
    }

    // Search functionality with the same design as inventory
    function setupUserSearch() {
        const searchInput = document.createElement('input');
        searchInput.setAttribute('type', 'text');
        searchInput.setAttribute('placeholder', 'Search users...');
        searchInput.classList.add('search-input');
        
        // Add search icon
        const searchContainer = document.createElement('div');
        searchContainer.className = 'search-container';
        searchContainer.innerHTML = '<i class="fas fa-search search-icon"></i>';
        searchContainer.appendChild(searchInput);
        
        // Add search input to the table actions
        const tableActions = document.querySelector('.table-actions');
        tableActions.insertBefore(searchContainer, tableActions.firstChild);
        
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                refreshUsersTable(e.target.value.trim());
            }, 500);
        });
    }

    // Initialize the table when the page loads
    document.addEventListener('DOMContentLoaded', () => {
        refreshUsersTable();
        setupUserSearch();
    });
</script>

<script>
    function showToast(title, message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer');
        
        // Create toast
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Set toast content
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="fas ${type === 'success' ? 'fa-check' : 
                                type === 'error' ? 'fa-times' : 
                                type === 'warning' ? 'fa-exclamation' : 
                                'fa-info'}"></i>
            </div>
            <div class="toast-content">
                <h4 class="toast-title">${title}</h4>
                <p class="toast-message">${message}</p>
            </div>
            <button class="toast-close">&times;</button>
        `;
        
        // Add toast to container
        toastContainer.appendChild(toast);
        
        // Show toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        // Auto remove toast after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
        
        // Close button
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
    }
</script>

<?php include "includes/script-src.php";?>
</body>
</html>