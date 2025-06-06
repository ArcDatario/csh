<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
// Redirect Field Managers if needed
if ($_SESSION['admin_role'] === "Field Manager" && 
    !in_array($current_page, ['inventory.php', 'field-processing-order.php', 'view-request.php'])) {
    header('Location: field-processing-order.php');
    exit();
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

    <!-- View Details Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Stock Request Details</h3>
                <button class="modal-close" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="request-details">
                    <div class="detail-row">
                        <span class="detail-label">Request ID:</span>
                        <span class="detail-value" id="detailId"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Item Name:</span>
                        <span class="detail-value" id="detailItemName"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Quantity:</span>
                        <span class="detail-value" id="detailQuantity"></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value" id="detailStatus"></span>
                    </div>
                    
                    <!-- Dynamic fields based on status -->
                    <div class="detail-row" id="requestedDateRow">
                        <span class="detail-label">Requested Date:</span>
                        <span class="detail-value" id="detailRequestedDate"></span>
                    </div>
                    <div class="detail-row" id="preparingDateRow" style="display:none;">
                        <span class="detail-label">Preparing Date:</span>
                        <span class="detail-value" id="detailPreparingDate"></span>
                    </div>
                    <div class="detail-row" id="deliveryDateRow" style="display:none;">
                        <span class="detail-label">For Delivery Date:</span>
                        <span class="detail-value" id="detailDeliveryDate"></span>
                    </div>
                    <div class="detail-row" id="completedDateRow" style="display:none;">
                        <span class="detail-label">Completed Date:</span>
                        <span class="detail-value" id="detailCompletedDate"></span>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline btn-danger modal-close">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/toast.js"></script>
    
    <script>
        // DOM Elements
        const refreshBtn = document.getElementById('refreshBtn');
        const viewModal = document.getElementById('viewModal');
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
                                <td>${request.item_name}</td>
                                <td>${request.quantity_requested}</td>
                                <td>
                                    <span class="status-badge ${getStatusClass(request.status)}">
                                        ${formatStatus(request.status)}
                                    </span>
                                </td>
                                <td>${new Date(request.request_date).toLocaleString()}</td>
                                <td class="actions">
                                    <button class="btn-icon view-details" data-id="${request.id}" data-status="${request.status}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            `;
                            stockRequestsTableBody.appendChild(row);
                        });

                        // Add event listeners for view buttons
                        document.querySelectorAll('.view-details').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const requestId = this.getAttribute('data-id');
                                const status = this.getAttribute('data-status');
                                fetchRequestDetails(requestId, status);
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

        // Fetch request details for modal
       // Fetch request details for modal
function fetchRequestDetails(requestId, status) {
    fetch(`api/get_request_details.php?id=${requestId}`)
        .then(response => {
            if (!response.ok) {
                // Get the error message from the response if possible
                return response.json().then(err => {
                    throw new Error(err.message || 'Failed to load request details');
                }).catch(() => {
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                populateModal(data.data, status);
                openModal(viewModal);
            } else {
                showToast('Error', data.message || 'Failed to load request details', 'error');
                console.error('API Error:', data.message);
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            showToast('Error', error.message || 'Failed to load request details', 'error');
        });
}

        // Populate modal with request details
        function populateModal(request, status) {
            // Basic details
            document.getElementById('detailId').textContent = request.id;
            document.getElementById('detailItemName').textContent = request.item_name;
            document.getElementById('detailQuantity').textContent = request.quantity_requested;
            document.getElementById('detailStatus').textContent = formatStatus(status);
            
            // Hide all date rows first
            document.getElementById('requestedDateRow').style.display = 'none';
            document.getElementById('preparingDateRow').style.display = 'none';
            document.getElementById('deliveryDateRow').style.display = 'none';
            document.getElementById('completedDateRow').style.display = 'none';
            
            // Always show requested date
            document.getElementById('requestedDateRow').style.display = 'flex';
            document.getElementById('detailRequestedDate').textContent = new Date(request.request_date).toLocaleString();
            
            // Show additional fields based on status
            switch(status) {
                case 'preparing':
                    document.getElementById('preparingDateRow').style.display = 'flex';
                    document.getElementById('detailPreparingDate').textContent = 
                        request.prepairing_date ? new Date(request.prepairing_date).toLocaleString() : 'Not set';
                    break;
                case 'for_delivery':
                    document.getElementById('preparingDateRow').style.display = 'flex';
                    document.getElementById('detailPreparingDate').textContent = 
                        request.prepairing_date ? new Date(request.prepairing_date).toLocaleString() : 'Not set';
                    document.getElementById('deliveryDateRow').style.display = 'flex';
                    document.getElementById('detailDeliveryDate').textContent = 
                        request.delivery_date ? new Date(request.delivery_date).toLocaleString() : 'Not set';
                    break;
                case 'completed':
                    document.getElementById('preparingDateRow').style.display = 'flex';
                    document.getElementById('detailPreparingDate').textContent = 
                        request.prepairing_date ? new Date(request.prepairing_date).toLocaleString() : 'Not set';
                    document.getElementById('deliveryDateRow').style.display = 'flex';
                    document.getElementById('detailDeliveryDate').textContent = 
                        request.delivery_date ? new Date(request.delivery_date).toLocaleString() : 'Not set';
                    document.getElementById('completedDateRow').style.display = 'flex';
                    document.getElementById('detailCompletedDate').textContent = 
                        request.completed_date ? new Date(request.completed_date).toLocaleString() : 'Not set';
                    break;
                default: // pending
                    // Only show requested date
                    break;
            }
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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadStockRequests();
        });
    </script>

    <style>
        .request-details {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .detail-row {
            display: flex;
            align-items: center;
        }
        
        .detail-label {
            font-weight: 600;
            width: 150px;
            color: #555;
        }
        
        .detail-value {
            flex: 1;
        }
    </style>

    <?php include "includes/script-src.php";?>
</body>
</html>