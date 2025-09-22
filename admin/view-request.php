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
        
        .no-requests {
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
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
            cursor: pointer;
            transition: border-color 0.2s;
            min-width: 150px;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: #4A90E2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }
        
        .reset-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .reset-btn:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
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
                        <!-- Search and filter will be added here via JavaScript -->
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
                            <tr>
                                <td colspan="6" class="text-center">Loading stock requests...</td>
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
        const viewModal = document.getElementById('viewModal');
        const modalCloses = document.querySelectorAll('.modal-close');
        const stockRequestsTableBody = document.getElementById('stockRequestsTableBody');
        const paginationContainer = document.getElementById('paginationContainer');

        // Variables for search, filter and pagination
        let currentPage = 1;
        let currentSearch = '';
        let currentStatus = '';

        // Modal Functions
        function openModal(modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modal) {
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        modalCloses.forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            });
        });

        // Load Stock Requests with search, filter and pagination
        function loadStockRequests(searchTerm = '', status = '', page = 1) {
            const url = `api/get_stock_requests.php?page=${page}` + 
                       (searchTerm ? `&search=${encodeURIComponent(searchTerm)}` : '') +
                       (status ? `&status=${encodeURIComponent(status)}` : '');
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        renderStockRequests(data.data);
                        renderPagination(data.pagination);
                    } else {
                        showToast('Error', data.message || 'Failed to load stock requests', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error', 'Failed to load stock requests', 'error');
                });
        }

        // Render stock requests to the table
        function renderStockRequests(requests) {
            stockRequestsTableBody.innerHTML = '';
            
            if (requests.length === 0) {
                stockRequestsTableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="no-requests">
                            No stock requests found
                        </td>
                    </tr>
                `;
                return;
            }
            
            requests.forEach(request => {
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
                    loadStockRequests(currentSearch, currentStatus, page);
                });
            });
        }

        // Search and Filter functionality
        function setupStockRequestSearchAndFilter() {
            // Create status filter
            const statusFilter = document.createElement('select');
            statusFilter.classList.add('filter-select');
            statusFilter.innerHTML = `
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="preparing">Preparing</option>
                <option value="for_delivery">For Delivery</option>
                <option value="completed">Completed</option>
            `;
            
            // Create search input
            const searchInput = document.createElement('input');
            searchInput.setAttribute('type', 'text');
            searchInput.setAttribute('placeholder', 'Search by item name...');
            searchInput.classList.add('search-input');
            
            // Add search icon
            const searchContainer = document.createElement('div');
            searchContainer.className = 'search-container';
            searchContainer.innerHTML = '<i class="fas fa-search search-icon"></i>';
            searchContainer.appendChild(searchInput);
            
            // Create reset button
            const resetButton = document.createElement('button');
            resetButton.classList.add('reset-btn');
            resetButton.innerHTML = '<i class="fas fa-redo-alt"></i> Reset';
            resetButton.title = 'Reset all filters';
            
            // Add controls to the table actions in order: Status → Search → Reset
            const tableActions = document.querySelector('.table-actions');
            tableActions.appendChild(statusFilter);
            tableActions.appendChild(searchContainer);
            tableActions.appendChild(resetButton);
            
            let searchTimeout;
            
            // Status filter event
            statusFilter.addEventListener('change', (e) => {
                currentStatus = e.target.value;
                loadStockRequests(currentSearch, currentStatus, 1);
            });
            
            // Search input event
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentSearch = e.target.value.trim();
                    loadStockRequests(currentSearch, currentStatus, 1);
                }, 500);
            });
            
            // Reset button event
            resetButton.addEventListener('click', () => {
                // Reset values
                statusFilter.value = '';
                searchInput.value = '';
                currentSearch = '';
                currentStatus = '';
                
                // Reload data
                loadStockRequests('', '', 1);
                
                // Show feedback
                showToast('Reset', 'All filters have been reset', 'info');
            });
        }

        // Fetch request details for modal
        function fetchRequestDetails(requestId, status) {
            fetch(`api/get_request_details.php?id=${requestId}`)
                .then(response => {
                    if (!response.ok) {
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
            setupStockRequestSearchAndFilter();
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