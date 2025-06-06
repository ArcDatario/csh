<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Safely check for Field Manager role and redirect
if (isset($_SESSION['admin_role'])) {
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if ($_SESSION['admin_role'] === "Field Manager" && $current_page != 'inventory.php') {
        header('Location: inventory.php');
        exit();
    }
    
    if ($_SESSION['admin_role'] === "Designer" && $current_page != 'orders.php') {
        header('Location: orders.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Requests Dashboard</title>
    <link rel="icon" href="assets/images/inventory.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
    <?php include "includes/link-css.php";?>

    <link rel="stylesheet" href="assets/css/inventory.css">
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
                <h1 class="header-dashboard">Orders</h1>
                
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
                    <h3 class="table-title">Stock Requests</h3>
                    <div class="table-actions">
                        <button class="btn btn-outline">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <button class="btn btn-primary" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <!-- <th>Field Manager</th> --> <!-- Removed Field Manager column -->
                                <th>Item Name</th>
                                <th>Quantity Requested</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="stockRequestsTableBody">
                            <!-- Stock requests will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Status Update Modal -->
    <div class="modal" id="statusModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Request Status</h3>
                <button class="modal-close" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" id="requestId">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" required>
                            <option value="pending">Pending</option>
                            <option value="preparing">Preparing</option>
                            <option value="for_delivery">For Delivery</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline btn-danger modal-close">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/toast.js"></script>
    
    <script>
        // DOM Elements
        const refreshBtn = document.getElementById('refreshBtn');
        const statusModal = document.getElementById('statusModal');
        const statusForm = document.getElementById('statusForm');
        const modalCloses = document.querySelectorAll('.modal-close');
        const stockRequestsTableBody = document.getElementById('stockRequestsTableBody');

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
        refreshBtn.addEventListener('click', loadStockRequests);

        modalCloses.forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            });
        });

        // Load Stock Requests
        function loadStockRequests() {
            fetch('api/get_stock_requests.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        stockRequestsTableBody.innerHTML = '';
                        data.data.forEach(request => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${request.id}</td>
                                <!-- <td>${request.field_manager_name || 'Field Manager #' + request.field_manager_id}</td> --> <!-- Removed Field Manager column -->
                                <td>${request.item_name}</td>
                                <td>${request.quantity_requested}</td>
                                <td>
                                    <span class="status-badge ${getStatusClass(request.status)}">
                                        ${formatStatus(request.status)}
                                    </span>
                                </td>
                                <td>${new Date(request.request_date).toLocaleString()}</td>
                                <td class="actions">
                                    <button class="btn-icon update-status" data-id="${request.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            `;
                            stockRequestsTableBody.appendChild(row);
                        });

                        // Add event listeners for status update buttons
                        document.querySelectorAll('.update-status').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const requestId = this.getAttribute('data-id');
                                document.getElementById('requestId').value = requestId;

                                // Find the request object by id
                                const request = data.data.find(r => r.id == requestId);
                                const statusSelect = document.getElementById('status');

                                // Enable all options first
                                Array.from(statusSelect.options).forEach(opt => opt.disabled = false);

                                // Disable options based on current status and prevent skipping steps
                                if (request.status === 'pending') {
                                    // Only allow "preparing"
                                    statusSelect.querySelector('option[value="pending"]').disabled = false;
                                    statusSelect.querySelector('option[value="preparing"]').disabled = false;
                                    statusSelect.querySelector('option[value="for_delivery"]').disabled = true;
                                    statusSelect.querySelector('option[value="completed"]').disabled = true;
                                } else if (request.status === 'preparing') {
                                    // Only allow "for_delivery"
                                    statusSelect.querySelector('option[value="pending"]').disabled = true;
                                    statusSelect.querySelector('option[value="preparing"]').disabled = false;
                                    statusSelect.querySelector('option[value="for_delivery"]').disabled = false;
                                    statusSelect.querySelector('option[value="completed"]').disabled = true;
                                } else if (request.status === 'for_delivery') {
                                    // Only allow "completed"
                                    statusSelect.querySelector('option[value="pending"]').disabled = true;
                                    statusSelect.querySelector('option[value="preparing"]').disabled = true;
                                    statusSelect.querySelector('option[value="for_delivery"]').disabled = false;
                                    statusSelect.querySelector('option[value="completed"]').disabled = false;
                                } else if (request.status === 'completed') {
                                    // All options disabled except completed
                                    statusSelect.querySelector('option[value="pending"]').disabled = true;
                                    statusSelect.querySelector('option[value="preparing"]').disabled = true;
                                    statusSelect.querySelector('option[value="for_delivery"]').disabled = true;
                                    statusSelect.querySelector('option[value="completed"]').disabled = false;
                                }

                                // Set the current status as selected
                                statusSelect.value = request.status;

                                openModal(statusModal);
                            });
                        });
                    } else {
                        showToast('Error', data.message || 'Failed to load stock requests', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'Failed to load stock requests', 'error');
                });
        }

        // Format status for display
        function formatStatus(status) {
            const statusMap = {
                'pending': 'Pending',
                'preparing': 'Preparing',
                'for_delivery': 'For Delivery',
                'completed': 'Completed'
            };
            return statusMap[status] || status;
        }

        // Get CSS class for status badge
        function getStatusClass(status) {
            const classMap = {
                'pending': 'badge-warning',
                'preparing': 'badge-info',
                'for_delivery': 'badge-primary',
                'completed': 'badge-success'
            };
            return classMap[status] || 'badge-secondary';
        }

        // Update Status
        statusForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const requestId = document.getElementById('requestId').value;
            const status = document.getElementById('status').value;

            fetch('api/update_request_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${requestId}&status=${status}`
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
                    closeModal(statusModal);
                    loadStockRequests();
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
            loadStockRequests();
        });
    </script>

    <?php include "includes/script-src.php";?>
</body>
</html>