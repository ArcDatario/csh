<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Dashboard</title>
    <link rel="icon" href="assets/images/analysis.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <link rel="stylesheet" href="assets/css/style.css">
    
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
                <h1 class="header-dashboard">Dashboard</h1>
                
                <div class="user-menu">
                <div class="theme-toggle" id="themeToggle">
                <span style="margin-right:8px;">Dark Mode</span>
                <i class="fas fa-moon"></i>
            </div>
                    
          <?php include "includes/notification.php";?>

    </div>
                    
                    <div class="user-avatar">Csh</div>
                </div>
            </header>
            
          
            
        
            <!-- Table -->
            <section class="table-card fade-in">
    <div class="table-header">
        <h3 class="table-title">Users</h3>
        <div class="table-actions">
            <button class="btn btn-outline">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
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
    </div>
</section>
        </main>
    </div>
    
</div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>



<script src="assets/js/script.js"></script>

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

// Search functionality
function setupUserSearch() {
    const searchInput = document.createElement('input');
    searchInput.setAttribute('type', 'text');
    searchInput.setAttribute('placeholder', 'Search users...');
    searchInput.classList.add('search-input');
    
    searchInput.addEventListener('input', (e) => {
        refreshUsersTable(e.target.value);
    });
    
    // Add search input to the table actions
    const tableActions = document.querySelector('.table-actions');
    tableActions.insertBefore(searchInput, tableActions.firstChild);
    
    // Add some styling
    searchInput.style.padding = '8px';
    searchInput.style.borderRadius = '4px';
    searchInput.style.border = '1px solid #ddd';
    searchInput.style.marginRight = '10px';
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


</body>
</html>