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
    <link rel="stylesheet" href="assets/css/admin.css">

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
                    <h3 class="table-title">Admin Management</h3>
                    <div class="table-actions">
                        <button class="btn btn-outline">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
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
                        </tbody>
                    </table>
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
                    <label for="username">Username</label>
                    <input type="text" id="username" required placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" required>
                        <option value="" disabled selected>Select Role</option>
                       <!-- filepath: c:\xampp\htdocs\csh\admin\admin.php -->
                    <option value="owner">Owner</option>
                    <option value="General Manager">General Manager</option>
                    <option value="Secretary">Secretary</option>
                    <option value="Field Manager">Field Manager</option>
                    <option value="Designer">Designer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" required placeholder="Create password">
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirmPassword" required placeholder="Confirm password">
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
     document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    });
</script>

    <script src="assets/js/script.js"></script>
    <script src="assets/js/toast.js"></script>
    
    <script>
        // Toast Function
       

        // DOM Elements
        const addAdminBtn = document.getElementById('addAdminBtn');
        const adminModal = document.getElementById('adminModal');
        const deleteModal = document.getElementById('deleteModal');
        const adminForm = document.getElementById('adminForm');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const modalCloses = document.querySelectorAll('.modal-close');
        const worksTableBody = document.getElementById('worksTableBody');

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
            document.getElementById('password').required = true;
            document.getElementById('confirmPassword').required = true;
            openModal(adminModal);
        });

        modalCloses.forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            });
        });

        // Load Admins
        function loadAdmins() {
            fetch('api/get_admins.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        worksTableBody.innerHTML = '';
                        data.data.forEach(admin => {
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

                        // Add event listeners to edit and delete buttons
                        document.querySelectorAll('.edit-admin').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const adminId = this.getAttribute('data-id');
                                editAdmin(adminId);
                            });
                        });

                        document.querySelectorAll('.delete-admin').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const adminId = this.getAttribute('data-id');
                                document.getElementById('deleteId').value = adminId;
                                openModal(deleteModal);
                            });
                        });
                    } else {
                        showToast('Error', data.message || 'Failed to load admins', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'Failed to load admins', 'error');
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
                        document.getElementById('username').value = data.username;
                        document.getElementById('fullname').value = data.fullname;
                        document.getElementById('role').value = data.role;
                        document.getElementById('password').required = false;
                        document.getElementById('confirmPassword').required = false;
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
            const username = document.getElementById('username').value;
            const fullname = document.getElementById('fullname').value;
            const role = document.getElementById('role').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
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
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    closeModal(adminModal);
                    loadAdmins();
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
                    loadAdmins();
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
        });
    </script>
</body>
</html>