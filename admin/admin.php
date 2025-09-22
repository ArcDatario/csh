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
    
    <?php include "includes/link-css.php";?>

    <link rel="stylesheet" href="assets/css/admin.css">

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
        
        .no-admins {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
        
        .text-center {
            text-align: center;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .dots {
            padding: 8px 12px;
            color: #6c757d;
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
                <h1 class="header-dashboard">Admins</h1>
                
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
                    <h3 class="table-title">Admin Accounts</h3>
                    <div class="table-actions">
                        <!-- Search will be added here via JavaScript -->
                        <button class="btn btn-primary" id="addAdminBtn">
                            <i class="fas fa-plus"></i>
                            <span>Add Admin</span>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>FullName</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="worksTableBody">
                            <!-- Admins will be loaded here via JavaScript -->
                            <tr>
                                <td colspan="4" class="text-center">Loading admins...</td>
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

    <!-- Add/Edit Admin Modal -->
    <div class="modal" id="adminModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Admin</h3>
            <button class="modal-close" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="adminForm">
                <input type="hidden" id="adminId">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-username" required placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-fullname" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select class="form-role" required>
                        <option value="" disabled selected>Select Role</option>
                        <option value="owner">Owner</option>
                        <option value="General Manager">General Manager</option>
                        <option value="Secretary">Secretary</option>
                        <option value="Field Manager">Field Manager</option>
                        <option value="Designer">Designer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-password" placeholder="Create password">
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-confirm-password" placeholder="Confirm password">
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline btn-danger modal-close">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content small">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this admin? This action cannot be undone.</p>
            <input type="hidden" id="deleteId">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline modal-close">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDelete">Delete Admin</button>
        </div>
    </div>
</div>

<script>
    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    });
</script>

<script src="assets/js/toast.js"></script>

<script>
    // Toast Function
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

    // DOM Elements
    const addAdminBtn = document.getElementById('addAdminBtn');
    const adminModal = document.getElementById('adminModal');
    const deleteModal = document.getElementById('deleteModal');
    const adminForm = document.getElementById('adminForm');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    const modalCloses = document.querySelectorAll('.modal-close');
    const worksTableBody = document.getElementById('worksTableBody');
    const paginationContainer = document.getElementById('paginationContainer');

    // Variables for search and pagination
    let currentPage = 1;
    let currentSearch = '';

    // Modal Functions
    function openModal(modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    // Event Listeners
    addAdminBtn.addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = 'Add Admin';
        adminForm.reset();
        document.getElementById('adminId').value = '';
        document.querySelector('.form-password').placeholder = 'Create password';
        document.querySelector('.form-confirm-password').placeholder = 'Confirm password';
        document.querySelector('.form-password').value = '';
        document.querySelector('.form-confirm-password').value = '';
        openModal(adminModal);
    });

    modalCloses.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });

    // Load Admins with search and pagination
    function loadAdmins(searchTerm = '', page = 1) {
        const url = `api/get_admins.php?page=${page}` + (searchTerm ? `&search=${encodeURIComponent(searchTerm)}` : '');
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderAdmins(data.data);
                    renderPagination(data.pagination);
                } else {
                    showToast('Error', data.message || 'Failed to load admins', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to load admins', 'error');
            });
    }

    // Render admins to the table
    function renderAdmins(admins) {
        worksTableBody.innerHTML = '';
        
        if (admins.length === 0) {
            worksTableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="no-admins">
                        No admins found
                    </td>
                </tr>
            `;
            return;
        }
        
        admins.forEach(admin => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${admin.username}</td>
                <td>${admin.fullname}</td>
                <td>${admin.role}</td>
                <td class="actions">
                    <button class="btn-icon edit-admin" data-id="${admin.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete-admin" data-id="${admin.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            worksTableBody.appendChild(row);
        });

        // Add event listeners to the newly created elements
        addEventListenersToAdmins();
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
                loadAdmins(currentSearch, page);
            });
        });
    }

    // Add event listeners to admin items
    function addEventListenersToAdmins() {
        // Edit admin buttons
        document.querySelectorAll('.edit-admin').forEach(btn => {
            btn.addEventListener('click', function() {
                const adminId = this.getAttribute('data-id');
                editAdmin(adminId);
            });
        });

        // Delete admin buttons
        document.querySelectorAll('.delete-admin').forEach(btn => {
            btn.addEventListener('click', function() {
                const adminId = this.getAttribute('data-id');
                document.getElementById('deleteId').value = adminId;
                openModal(deleteModal);
            });
        });
    }

    // Search functionality
    function setupAdminSearch() {
        const searchInput = document.createElement('input');
        searchInput.setAttribute('type', 'text');
        searchInput.setAttribute('placeholder', 'Search admins...');
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
                loadAdmins(currentSearch, 1);
            }, 500);
        });
    }

    // Edit Admin
    function editAdmin(id) {
        fetch(`api/get_admin.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('modalTitle').textContent = 'Edit Admin';
                    document.getElementById('adminId').value = data.id;
                    document.querySelector('.form-username').value = data.username;
                    document.querySelector('.form-fullname').value = data.fullname;
                    document.querySelector('.form-role').value = data.role;
                    // Clear password fields when editing
                    document.querySelector('.form-password').value = '';
                    document.querySelector('.form-confirm-password').value = '';
                    document.querySelector('.form-password').placeholder = 'Leave blank to keep current password';
                    document.querySelector('.form-confirm-password').placeholder = 'Leave blank to keep current password';
                    openModal(adminModal);
                } else {
                    showToast('Error', data.message || 'Failed to load admin data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to load admin data', 'error');
            });
    }

    // Save Admin (Add/Edit)
    adminForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('adminId').value;
        const username = document.querySelector('.form-username').value;
        const fullname = document.querySelector('.form-fullname').value;
        const role = document.querySelector('.form-role').value;
        const password = document.querySelector('.form-password').value;
        const confirmPassword = document.querySelector('.form-confirm-password').value;

        if (password && password !== confirmPassword) {
            showToast('Error', 'Passwords do not match', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('id', id);
        formData.append('username', username);
        formData.append('fullname', fullname);
        formData.append('role', role);
        if (password) formData.append('password', password);

        const url = id ? 'api/update_admin.php' : 'api/add_admin.php';

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                closeModal(adminModal);
                // Refresh the table with current search and page
                loadAdmins(currentSearch, currentPage);
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'An error occurred', 'error');
        });
    });

    // Delete Admin
    confirmDeleteBtn.addEventListener('click', function() {
        const id = document.getElementById('deleteId').value;

        fetch('api/delete_admin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                closeModal(deleteModal);
                // Refresh the table with current search and page
                loadAdmins(currentSearch, currentPage);
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'An error occurred', 'error');
        });
    });

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadAdmins();
        setupAdminSearch();
    });
</script>

<?php include "includes/script-src.php";?>
</body>
</html>