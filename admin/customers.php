<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
// Redirect Field Managers to inventory
if ($_SESSION['admin_role'] === "Field Manager" && 
    !in_array($current_page, ['inventory.php', 'field-processing-order.php'])) {
    header('Location: field-processing-order.php');
    exit();
}

// --- Pagination setup ---
$logs_per_page = 7; // items per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $logs_per_page;

// --- Count total users ---
$count_query = "SELECT COUNT(*) as total FROM users";
$count_result = $conn->query($count_query);
$total_items = $count_result ? intval($count_result->fetch_assoc()['total']) : 0;

// --- Calculate total pages ---
$total_pages = ceil($total_items / $logs_per_page);

// --- Fetch users for current page ---
$users_query = "SELECT * FROM users LIMIT $offset, $logs_per_page";
$users_result = $conn->query($users_query);
$users = [];
if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers Dashboard</title>
    <link rel="icon" href="assets/images/customers.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
    <?php include "includes/link-css.php";?>

    <link rel="stylesheet" href="assets/css/admintoapprove.css">
    
    <style>
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
        
        .table-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .no-items {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
        
        .text-center {
            text-align: center;
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
                        <tbody id="usersTableBody">
                            <!-- Users will be loaded here via JavaScript -->
                            <tr>
                                <td colspan="3" class="text-center">
                                    Loading customers...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="pagination" id="paginationContainer">
                        <!-- Pagination will be loaded via JavaScript -->
                    </div>
                </div>
            </section>
        </main>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="assets/js/toast.js"></script>
    
    <script>
        // DOM Elements
        const usersTableBody = document.getElementById('usersTableBody');
        const paginationContainer = document.getElementById('paginationContainer');

        // Current state
        let currentPage = 1;
        let currentSearch = '';

        // Load Users with search and pagination
        function refreshUsersTable(searchTerm = '', page = 1) {
            const url = `get_users.php?page=${page}` + (searchTerm ? `&search=${encodeURIComponent(searchTerm)}` : '');
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        renderUsers(data.data);
                        renderPagination(data.pagination);
                    } else {
                        showToast('Error', data.message || 'Failed to load users', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'Failed to load users', 'error');
                });
        }

        // Render users to the table
        function renderUsers(users) {
            usersTableBody.innerHTML = '';
            
            if (users.length === 0) {
                usersTableBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="no-items">
                            No users found
                        </td>
                    </tr>
                `;
                return;
            }
            
            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td>${user.completed_orders}</td>
                `;
                usersTableBody.appendChild(row);
            });
        }

        // Render pagination
        function renderPagination(pagination) {
            if (!pagination) return;
            
            const { current_page, total_pages } = pagination;
            currentPage = current_page;
            
            let paginationHTML = '';
            
            // Previous button
            if (current_page > 1) {
                paginationHTML += `<a href="#" class="btn btn-outline" data-page="${current_page - 1}">&laquo; Prev</a>`;
            }
            
            // First page
            paginationHTML += `<a href="#" class="btn ${current_page == 1 ? 'btn-primary' : 'btn-outline'}" data-page="1">1</a>`;
            
            // Dots before current page
            if (current_page > 3) {
                paginationHTML += `<span class="dots">...</span>`;
            }
            
            // Pages around current
            for (let i = Math.max(2, current_page - 2); i <= Math.min(total_pages - 1, current_page + 2); i++) {
                paginationHTML += `<a href="#" class="btn ${i == current_page ? 'btn-primary' : 'btn-outline'}" data-page="${i}">${i}</a>`;
            }
            
            // Dots after current page
            if (current_page < total_pages - 2) {
                paginationHTML += `<span class="dots">...</span>`;
            }
            
            // Last page (if different from first page)
            if (total_pages > 1) {
                paginationHTML += `<a href="#" class="btn ${current_page == total_pages ? 'btn-primary' : 'btn-outline'}" data-page="${total_pages}">${total_pages}</a>`;
            }
            
            // Next button
            if (current_page < total_pages) {
                paginationHTML += `<a href="#" class="btn btn-outline" data-page="${current_page + 1}">Next &raquo;</a>`;
            }
            
            paginationContainer.innerHTML = paginationHTML;
            
            // Add event listeners to pagination buttons
            document.querySelectorAll('#paginationContainer a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = parseInt(this.getAttribute('data-page'));
                    refreshUsersTable(currentSearch, page);
                });
            });
        }

        // Search functionality
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
                    currentSearch = e.target.value.trim();
                    refreshUsersTable(currentSearch, 1);
                }, 500);
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            refreshUsersTable();
            setupUserSearch();
        });
    </script>

    <?php include "includes/script-src.php";?>
</body>
</html>