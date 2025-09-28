<?php
require_once 'auth_check.php';
require_once '../db_connection.php';

// Protect login
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Safely check for Field Manager role and redirect
if (isset($_SESSION['admin_role'])) {
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // This condition is incorrect and should be removed.
    // if ($_SESSION['admin_role'] === "Field Manager" && $current_page != 'inventory.php') {
    //     header('Location: inventory.php');
    //     exit();
    // }
    
    // This logic is for the Designer role and seems correct for their needs.
    if ($_SESSION['admin_role'] === "Designer" && $current_page != 'orders.php') {
        header('Location: orders.php');
        exit();
    }
}

// Determine which tab is active
$active_tab = $_GET['tab'] ?? 'processing';

$tab_titles = [
    'processing' => 'Processing Orders',
    'on-process' => 'On Process Orders', 
];

// Then update your switch statement to use this array
$title = $tab_titles[$active_tab] ?? 'To Processing Orders';

// Set status, processing flag, and title based on active tab
$is_for_processing = null; // default null
switch ($active_tab) {
    case 'on-process':
        $status_filter = "processing";
        $is_for_processing = "yes";
        $title = "On Process Orders";
        break;
    default: // processing
        $status_filter = "processing";
        $is_for_processing = "no";
        $title = "Processing Orders";
        break;
}

// Calculate counts for all tabs
$tab_counts = [
    'processing' => 0,
    'on-process' => 0,
];

foreach ($tab_counts as $tab => $count) {
    $tab_status_filter = "";
    $tab_is_for_processing = null;
    
    switch ($tab) {
        case 'on-process':
            $tab_status_filter = "processing";
            $tab_is_for_processing = "yes";
            break;
        default: // processing
            $tab_status_filter = "processing";
            $tab_is_for_processing = "no";
            break;
    }
    
    // Build WHERE clause for this tab
    $tab_where_clauses = ["o.status = '" . $conn->real_escape_string($tab_status_filter) . "'"];
    
    if ($tab_is_for_processing !== null) {
        $tab_where_clauses[] = "o.is_for_processing = '" . $conn->real_escape_string($tab_is_for_processing) . "'";
    }
    
    $tab_where_sql = !empty($tab_where_clauses) ? "WHERE " . implode(" AND ", $tab_where_clauses) : "";
    
    // Count query for this tab
    $tab_count_query = "SELECT COUNT(*) as total 
                       FROM orders o
                       INNER JOIN users u ON o.user_id = u.id
                       $tab_where_sql";
    
    $tab_count_result = $conn->query($tab_count_query);
    $tab_counts[$tab] = $tab_count_result ? intval($tab_count_result->fetch_assoc()['total']) : 0;
}

// Output the counts as JavaScript variables
echo '<script>';
echo 'const tabCounts = ' . json_encode($tab_counts) . ';';
echo '</script>';

// -----------------------------
// FILTER + PAGINATION LOGIC (FIXED)
// -----------------------------
$filter_print  = $_GET['print_type'] ?? '';
$filter_start  = $_GET['start_date'] ?? '';
$filter_end    = $_GET['end_date'] ?? '';
$filter_search = $_GET['search'] ?? '';

// Build WHERE clause
$where_clauses = [
    "o.status = '" . $conn->real_escape_string($status_filter) . "'"
];

// Only include is_for_processing if set
if ($is_for_processing !== null) {
    $where_clauses[] = "o.is_for_processing = '" . $conn->real_escape_string($is_for_processing) . "'";
}

if ($filter_print !== '') {
    $where_clauses[] = "o.print_type = '" . $conn->real_escape_string($filter_print) . "'";
}
if ($filter_start !== '' && $filter_end !== '') {
    $where_clauses[] = "DATE(o.created_at) BETWEEN '" . $conn->real_escape_string($filter_start) . "' 
                        AND '" . $conn->real_escape_string($filter_end) . "'";
}
if ($filter_search !== '') {
    $search = $conn->real_escape_string($filter_search);
    $where_clauses[] = "(o.ticket LIKE '%$search%' OR u.name LIKE '%$search%')";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Pagination - ALWAYS count actual filtered records
$orders_per_page = 7;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $orders_per_page;

// Count ACTUAL filtered records (remove the default_limit logic)
$count_query = "SELECT COUNT(*) as total 
                FROM orders o 
                INNER JOIN users u ON o.user_id = u.id
                $where_sql";
$count_result = $conn->query($count_query);
$total_orders = $count_result ? intval($count_result->fetch_assoc()['total']) : 0;
$total_pages = ceil($total_orders / $orders_per_page);

// Ensure page is within valid range
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $orders_per_page;
}

// Fetch orders with proper pagination
$query = "SELECT o.*, u.name, u.email, u.phone_number 
          FROM orders o 
          INNER JOIN users u ON o.user_id = u.id
          $where_sql
          ORDER BY o.created_at DESC
          LIMIT $orders_per_page OFFSET $offset";

$result = $conn->query($query);
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$page = 1; // default
if ($active_tab == 'onprocess' && isset($_GET['page_onprocess'])) {
    $page = (int)$_GET['page_onprocess'];
} elseif ($active_tab == 'processing' && isset($_GET['page_processing'])) {
    $page = (int)$_GET['page_processing'];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <?php include "includes/link-css.php";?>
    <link rel="stylesheet" href="assets/css/admintoapprove.css">

    <style>
        .table-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.tab-btn {
    padding: 8px 16px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    font-weight: 500;
    color: #666;
}

.tab-btn.active {
    color: #333;
    border-bottom-color: #4CAF50;
    font-weight: 600;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
/* On Pickup Modal Specific Styles */
.btn-warning {
    background-color: #ffc107;
    color: #212529;
    border: none;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
    border: none;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
    border: none;
}

.quote-modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.address-value {
    display: inline-block;
    max-width: 100%;
    word-break: break-word;
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
            <div class="table-tabs">
                <button class="tab-btn <?= $active_tab === 'processing' ? 'active' : '' ?>" data-tab="processing">Processing</button>
                <button class="tab-btn <?= $active_tab === 'on-process' ? 'active' : '' ?>" data-tab="on-process">On Process</button>
            </div>
            <!-- Table -->
             <section class="table-card fade-in">
              <div class="table-header">
                <h3 class="table-title" id="table-title">
                    <?= $title ?>
                    <span class="badge"><?= $tab_counts[$active_tab] ?></span>
                </h3>
                  <div class="table-actions">
                      <!-- FILTER FORM -->
                      <form method="GET" class="filter-form">
                        <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
                          <!-- Print Type -->
                          <select name="print_type">
                              <option value="">All Print Types</option>
                              <option value="Direct to Film Print" <?= ($_GET['print_type'] ?? '') === 'Direct to Film Print' ? 'selected' : '' ?>>Direct to Film Print</option>
                              <option value="Screen Printing" <?= ($_GET['print_type'] ?? '') === 'Screen Printing' ? 'selected' : '' ?>>Screen Printing</option>
                              <option value="Emboss Print" <?= ($_GET['print_type'] ?? '') === 'Emboss Print' ? 'selected' : '' ?>>Emboss Print</option>
                              <option value="Hi-Density Print" <?= ($_GET['print_type'] ?? '') === 'Hi-Density Print' ? 'selected' : '' ?>>Hi-Density Print</option>
                              <option value="Glitters Print" <?= ($_GET['print_type'] ?? '') === 'Glitters Print' ? 'selected' : '' ?>>Glitters Print</option>
                              <option value="Silk Screen Print" <?= ($_GET['print_type'] ?? '') === 'Silk Screen Print' ? 'selected' : '' ?>>Silk Screen Print</option>
                          </select>

                          <!-- Date Range -->
                          <input type="date" name="start_date" value="<?= htmlspecialchars($filter_start) ?>">
                          <input type="date" name="end_date" value="<?= htmlspecialchars($filter_end) ?>">

                          <!-- Search -->
                          <input type="text" name="search" placeholder="Search by ticket or name" value="<?= htmlspecialchars($filter_search) ?>">

                          <!-- Styled Buttons -->
                          <button type="submit" class="btn btn-outline">
                              <i class="fas fa-filter"></i>
                              <span>Filter</span>
                          </button>
                          <a href="field-processing-order.php" class="btn btn-outline">
                              <i class="fas fa-undo"></i>
                              <span>Reset</span>
                          </a>
                      </form>
                  </div>
              </div>
    
<!-- Processing Table (wrapped in tab-content) -->
<div id="processing-table" class="table-responsive tab-content active">
    <table>
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>Design</th>
                <th>Print Type</th>
                <th>Quantity</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="admins-table-body">

        </tbody>
    </table>
<!-- Pagination for Processing -->
<div class="pagination"></div>
                </div>

                <?php include "includes/tables/onprocess-table.php"; ?>
            </section>
        </main>
</div>
  
<div id="processingModal" class="quote-modal">
  <div class="quote-modal-content">
    <span class="quote-modal-close">&times;</span>
    <h2>Order Processing</h2>
    <div class="quote-modal-body">
      <!-- Ticket -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Ticket #:</span>
        <span id="processing-modal-ticket" class="quote-modal-value"></span>
      </div>
      
      <!-- Design with buttons -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Design:</span>
        <div class="design-image-container">
          <img id="processing-modal-design" src="" alt="Design" class="design-image">
        </div>
      </div>
      
      <!-- Print Type -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Print Type:</span>
        <span id="processing-modal-print-type" class="quote-modal-value"></span>
      </div>
      
      <!-- Quantity -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Quantity:</span>
        <span id="processing-modal-quantity" class="quote-modal-value"></span>
      </div>

        <!-- Shirt Colors & Quantities Section -->
        <div class="quote-modal-row">
            <span class="quote-modal-label">Items:</span>
            <div id="quote-modal-shirt-items" class="shirt-items-container">
                <!-- JS will populate this dynamically -->
            </div>
        </div>
      
      <!-- Hidden fields -->
      <input type="hidden" id="processing-modal-id" name="id">
      <input type="hidden" id="processing-modal-ticket-input" name="ticket">
    </div>
    <div class="quote-modal-footer">
      <button id="processing-confirm-btn" class="quote-modal-btn btn-process">Mark as On Process</button>
      <button id="processing-modal-close" class="quote-modal-btn btn-close">Close</button>
    </div>
  </div>
</div>



<!-- On Process Modal -->
<div id="onProcessModal" class="quote-modal">
  <div class="quote-modal-content">
    <span class="quote-modal-close">&times;</span>
    <h2>Order Details</h2>
    <div class="quote-modal-body">
      <!-- Ticket -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Ticket #:</span>
        <span id="onprocess-modal-ticket" class="quote-modal-value"></span>
      </div>
      
      <!-- Customer Info -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Customer:</span>
        <span id="onprocess-modal-name" class="quote-modal-value"></span>
      </div>
      
      <div class="quote-modal-row">
        <span class="quote-modal-label">Email:</span>
        <span id="onprocess-modal-email" class="quote-modal-value"></span>
      </div>
      
      <div class="quote-modal-row">
        <span class="quote-modal-label">Mobile:</span>
        <span id="onprocess-modal-mobile" class="quote-modal-value"></span>
      </div>
      
      <div class="quote-modal-row">
        <span class="quote-modal-label">Address:</span>
        <span id="onprocess-modal-address" class="quote-modal-value address-value"></span>
      </div>
      
      <!-- Design with buttons -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Design:</span>
        <div class="design-image-container">
          <img id="onprocess-modal-design" src="" alt="Design" class="design-image">
        </div>
      </div>
      
      <!-- Print Type -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Print Type:</span>
        <span id="onprocess-modal-print-type" class="quote-modal-value"></span>
      </div>
      
      <!-- Quantity -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Quantity:</span>
        <span id="onprocess-modal-quantity" class="quote-modal-value"></span>
      </div>

              <!-- Shirt Colors & Quantities Section -->
    <div class="quote-modal-row">
            <span class="quote-modal-label">Items:</span>
            <div id="quote-modal-shirt-items" class="shirt-items-container">
                <!-- JS will populate this dynamically -->
            </div>
    </div>
      
      <!-- Pricing -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Unit Price:</span>
        <span id="onprocess-modal-pricing" class="quote-modal-value"></span>
      </div>
      
      <!-- Subtotal -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Subtotal:</span>
        <span id="onprocess-modal-subtotal" class="quote-modal-value"></span>
      </div>
      
      <!-- Notes -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Notes:</span>
        <span id="onprocess-modal-note" class="quote-modal-value"></span>
      </div>
      
      <!-- Processing Date -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Processing Date:</span>
        <span id="onprocess-modal-process-date" class="quote-modal-value"></span>
      </div>
      
      <!-- Hidden fields -->
      <input type="hidden" id="onprocess-modal-id" name="id">
      <input type="hidden" id="onprocess-modal-user-id" name="user_id">
      <input type="hidden" id="onprocess-modal-ticket-input" name="ticket">
    </div>
    <div class="quote-modal-footer">
      <button id="onprocess-confirm-btn" class="quote-modal-btn btn-process">Mark as To Ship</button>
      <button id="onprocess-modal-close" class="quote-modal-btn btn-close">Close</button>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmShipModal" class="quote-modal">
  <div class="quote-modal-content" style="max-width: 500px;">
    <h2>Confirm Shipment</h2>
    <div class="quote-modal-body">
      <p>Are you sure you want to mark this order as "To Ship"?</p>
      <p>This will notify the customer and the management team.</p>
    </div>
    <div class="quote-modal-footer">
      <button id="confirm-ship-yes" class="quote-modal-btn btn-process">Yes, Ship It</button>
      <button id="confirm-ship-no" class="quote-modal-btn btn-close">Cancel</button>
    </div>
  </div>
</div>

<!-- Image Viewer Modal -->
<div id="imageViewerModal" class="image-viewer-modal">
  <span class="close-viewer">&times;</span>
  <img class="image-viewer-content" id="expandedDesignImage">
  <div id="viewerLoading" class="viewer-loading">Loading...</div>
</div>
    
<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
        // Toast function
    function showToast(title, message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer');
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
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
        
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
        
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
    }
// ============================
// MODAL HANDLING
// ============================

// Utility: close modal
function closeModal(modal) {
    if (modal) {
        modal.style.display = "none";
    }
}

// Utility: open modal
function openModal(modal) {
    if (modal) {
        modal.style.display = "block";
    }
}

// Processing Modal
const processingModal = document.getElementById("processingModal");
const processingCloseX = processingModal.querySelector(".quote-modal-close");
const processingCloseBtn = document.getElementById("processing-modal-close");

processingCloseX.addEventListener("click", () => closeModal(processingModal));
processingCloseBtn.addEventListener("click", () => closeModal(processingModal));

// On Process Modal
const onProcessModal = document.getElementById("onProcessModal");
const onProcessCloseX = onProcessModal.querySelector(".quote-modal-close");
const onProcessCloseBtn = document.getElementById("onprocess-modal-close");

onProcessCloseX.addEventListener("click", () => closeModal(onProcessModal));
onProcessCloseBtn.addEventListener("click", () => closeModal(onProcessModal));

// Confirm Ship Modal
const confirmShipModal = document.getElementById("confirmShipModal");
const confirmShipNoBtn = document.getElementById("confirm-ship-no");

confirmShipNoBtn.addEventListener("click", () => closeModal(confirmShipModal));

// Image Viewer Modal
const imageViewerModal = document.getElementById("imageViewerModal");
const closeViewer = imageViewerModal.querySelector(".close-viewer");

closeViewer.addEventListener("click", () => closeModal(imageViewerModal));

// Also allow clicking outside modal content to close
window.addEventListener("click", function(e) {
    if (e.target === processingModal) closeModal(processingModal);
    if (e.target === onProcessModal) closeModal(onProcessModal);
    if (e.target === confirmShipModal) closeModal(confirmShipModal);
    if (e.target === imageViewerModal) closeModal(imageViewerModal);
});
</script>

<script>
// ============================
// 1. GLOBAL STATE
// ============================
let activeFilters = null;
let refreshInterval = null;

// ============================
// 2. REFRESH HANDLER
// ============================
function refreshActiveTab() {
    const activeTab = localStorage.getItem('activeTab') || 'processing';
    const urlParams = new URLSearchParams(window.location.search);

    // Get the correct page parameter based on active tab
    let currentPage = 1;
    if (activeTab === 'processing') {
        currentPage = urlParams.get('page_processing') || 1;
    } else if (activeTab === 'on-process') {
        currentPage = urlParams.get('page_onprocess') || 1;
    }

    if (activeFilters) {
        // If filters exist → keep them, but add current page
        activeFilters.delete('page'); // clean old param
        if (currentPage > 1) {
            activeFilters.set('page', currentPage);
        }
        applyFilters(activeTab, activeFilters);
    } else {
        // Otherwise → refresh with current page and tab
        const params = new URLSearchParams();
        params.set('tab', activeTab);

        // Clean + re-apply page param
        params.delete('page');
        if (currentPage > 1) {
            params.set('page', currentPage);
        }

        if (activeTab === 'processing') {
            fetch('functions/get_processing_orders.php?' + params.toString())
                .then(response => response.json()) // parse JSON
                .then(data => {
                    const tbody = document.getElementById('admins-table-body');

                    if (data.total_records == 0) {
                        tbody.innerHTML = `<tr><td colspan="7" class="text-center">No orders currently for processing</td></tr>`;
                        document.querySelector('#processing-table .pagination').innerHTML = '';
                    } else {
                        tbody.innerHTML = data.table;
                        document.querySelector('#processing-table .pagination').innerHTML = data.pagination;
                    }

                    attachProcessingListeners();
                    attachProcessingPaginationListeners();
                })
                .catch(error => console.error('Error filtering table:', error));

        } else if (activeTab === 'on-process') {
            fetch('api/get_onprocess_orders.php?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    // If there are no records, show a placeholder row
                    if (data.total_records === 0) {
                        document.getElementById('onprocess-table-body').innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center">No orders currently for on-process</td>
                            </tr>
                        `;
                        document.querySelector('#on-process-table .pagination').innerHTML = '';
                    } else {
                        // Update table and pagination normally
                        document.getElementById('onprocess-table-body').innerHTML = data.table;
                        document.querySelector('#on-process-table .pagination').innerHTML = data.pagination;
                    }

                    // Reattach listeners
                    attachOnProcessListeners();
                    attachOnProcessPaginationListeners();
                })
                .catch(error => console.error('Error paginating on-process:', error));
        }
    }
}


// ============================
// 3. FILTERS (shared)
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('.filter-form');
    
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Get form data
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            
            // Store filters globally
            activeFilters = params;

            // Determine which table to update based on active tab
            const activeTab = localStorage.getItem('activeTab') || 'processing';
            
            applyFilters(activeTab, params);
        });
    }

    // Reset button handler (if you have one)
    const resetBtn = document.querySelector('.reset-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            activeFilters = null; // Clear filters
        });
    }
});


// Apply filters to table
// ============================
function applyFilters(activeTab, params) {
    if (activeTab === 'processing') {
        fetch('functions/get_processing_orders.php?' + params.toString())
            .then(response => response.json()) // parse JSON
            .then(data => {
                const tbody = document.getElementById('admins-table-body');

                if (data.total_records == 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center">No orders currently for processing</td></tr>`;
                    document.querySelector('#processing-table .pagination').innerHTML = '';
                } else {
                    tbody.innerHTML = data.table;
                    document.querySelector('#processing-table .pagination').innerHTML = data.pagination;
                }

                attachProcessingListeners();
                attachProcessingPaginationListeners();
            })
            .catch(error => console.error('Error filtering table:', error));

    } else if (activeTab === 'on-process') {
        fetch('api/get_onprocess_orders.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                // If there are no records, show a placeholder row
                if (data.total_records === 0) {
                    document.getElementById('onprocess-table-body').innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">No orders currently for on-process</td>
                        </tr>
                    `;
                    document.querySelector('#on-process-table .pagination').innerHTML = '';
                } else {
                    // Update table and pagination normally
                    document.getElementById('onprocess-table-body').innerHTML = data.table;
                    document.querySelector('#on-process-table .pagination').innerHTML = data.pagination;
                }

                // Reattach listeners
                attachOnProcessListeners();
                attachOnProcessPaginationListeners();
            })
            .catch(error => console.error('Error paginating on-process:', error));
    }
}

// For ON-PROCESS tab
function attachOnProcessPaginationListeners() {
    document.querySelectorAll('#on-process-table .pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const url = new URL(this.href);
            const page = url.searchParams.get('page') || 1;

            // Merge filters + page
            const params = activeFilters ? new URLSearchParams(activeFilters) : new URLSearchParams();
            params.set('page', page);

            // Fetch JSON from API
            fetch('api/get_onprocess_orders.php?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    // If there are no records, show a placeholder row
                    if (data.total_records === 0) {
                        document.getElementById('onprocess-table-body').innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center">No orders currently for on-process</td>
                            </tr>
                        `;
                        document.querySelector('#on-process-table .pagination').innerHTML = '';
                    } else {
                        // Update table and pagination normally
                        document.getElementById('onprocess-table-body').innerHTML = data.table;
                        document.querySelector('#on-process-table .pagination').innerHTML = data.pagination;
                    }

                    // Reattach listeners
                    attachOnProcessListeners();
                    attachOnProcessPaginationListeners();
                })
                .catch(error => console.error('Error paginating on-process:', error));

            // Update URL in browser
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', 'on-process');
            newUrl.searchParams.set('page_onprocess', page);
            window.history.replaceState({}, '', newUrl);
        });
    });
}

// For PROCESSING tab
function attachProcessingPaginationListeners() {
    document.querySelectorAll('#processing-table .pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();

            const url = new URL(this.href);
            const page = url.searchParams.get('page') || 1;

            // Merge filters + page
            const params = activeFilters ? new URLSearchParams(activeFilters) : new URLSearchParams();
            params.set('page', page);
                fetch('functions/get_processing_orders.php?' + params.toString())
                    .then(response => response.json()) // parse JSON
                    .then(data => {
                        const tbody = document.getElementById('admins-table-body');

                        if (data.total_records == 0) {
                            tbody.innerHTML = `<tr><td colspan="7" class="text-center">No orders currently for processing</td></tr>`;
                            document.querySelector('#processing-table .pagination').innerHTML = '';
                        } else {
                            tbody.innerHTML = data.table;
                            document.querySelector('#processing-table .pagination').innerHTML = data.pagination;
                        }

                        attachProcessingListeners();
                        attachProcessingPaginationListeners();
                    })
                    .catch(error => console.error('Error filtering table:', error));


            // Update URL in browser
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', 'processing');
            newUrl.searchParams.set('page_processing', page);
            window.history.replaceState({}, '', newUrl);
        });
    });
}


// ============================
// 4. TAB SWITCHING
// ============================
// ============================
// UPDATE TITLE FUNCTION
// ============================
function updateTableTitle(tabId) {
    const titleMap = {
        'processing': 'Processing Orders',
        'on-process': 'On Process Orders'
    };
    
    const tableTitle = document.getElementById('table-title');
    if (tableTitle) {
        const titleText = titleMap[tabId] || 'Processing Orders';
        const badgeCount = tabCounts[tabId] || 0;
        
        tableTitle.innerHTML = `${titleText} <span class="badge">${badgeCount}</span>`;
    }
}
document.addEventListener('DOMContentLoaded', function() { 
    const tabButtons = document.querySelectorAll('.tab-btn');
    
function switchTab(tabId) {
    // Reset ALL filter forms
    document.querySelectorAll('form.filter-form').forEach(form => form.reset());

    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
        content.style.display = 'none';
    });

    // Activate clicked tab
    const activeButton = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
    if (activeButton) activeButton.classList.add('active');

    const activeContent = document.getElementById(`${tabId}-table`);
    if (activeContent) {
        activeContent.classList.add('active');
        activeContent.style.display = 'block';

        // Update the table title dynamically
        updateTableTitle(tabId);

        // Clear filters after switching
        activeFilters = null;

        // Update URL: only keep the relevant page parameter
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);

        // Remove old page params
        url.searchParams.delete('page');
        url.searchParams.delete('page_processing');
        url.searchParams.delete('page_onprocess');

        // Set current page = 1
        if (tabId === 'processing') url.searchParams.set('page_processing', 1);
        if (tabId === 'on-process') url.searchParams.set('page_onprocess', 1);

        window.history.replaceState({}, '', url);

        // Save active tab
        localStorage.setItem('activeTab', tabId);

        // Reload fresh data starting from page 1
        refreshActiveTab();
    }
}


        
    // Set up click handlers
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
        });
    });
    
    // Check for saved tab or default to 'processing'
    const savedTab = localStorage.getItem('activeTab');
    if (savedTab && (savedTab === 'processing' || savedTab === 'on-process')) {
        switchTab(savedTab);
    } else {
        switchTab('processing');
    }
    
    // Initialize the active tab content
    const activeTab = localStorage.getItem('activeTab') || 'processing';
    const activeContent = document.getElementById(`${activeTab}-table`);
    if (activeContent) {
        activeContent.style.display = 'block';
    }
});

// ============================
// 5. EVENT LISTENERS
// ============================

// Confirm Processing
function handleConfirmProcessing() {
    const id = document.getElementById('processing-modal-id').value;
    const ticket = document.getElementById('processing-modal-ticket-input').value;

    // Loading state
    const confirmBtn = document.getElementById('processing-confirm-btn');
    const processingModal = document.getElementById('processingModal');
    const originalText = confirmBtn.textContent;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';

    fetch('functions/update_processing.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&ticket=${ticket}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            processingModal.style.display = 'none';
            refreshActiveTab();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred while updating order', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
    });
}

function handleConfirmShipment() {
    const id = document.getElementById('onprocess-modal-id').value;
    const ticket = document.getElementById('onprocess-modal-ticket-input').value;
    const userId = document.getElementById('onprocess-modal-user-id').value;
    const email = document.getElementById('onprocess-modal-email').value;
    const quantity = document.getElementById('onprocess-modal-quantity').textContent;
    const pricing = document.getElementById('onprocess-modal-pricing').textContent.replace('₱','');
    const subtotal = document.getElementById('onprocess-modal-subtotal').textContent.replace('₱','');

    const confirmBtn = document.getElementById('onprocess-confirm-btn');
    const shipmentModal = document.getElementById('onprocessModal');
    const originalText = confirmBtn.textContent;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';

    const payload = { id, ticket, user_id: userId, email, quantity, pricing, subtotal };
    console.log("Confirm Shipment Data (JSON):", payload);

    fetch('api/confirm_shipment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        console.log("PHP response:", data);
        if (data.success) {
            showToast('Success', data.message, 'success');
            document.getElementById('onProcessModal').style.display = 'none';
            refreshActiveTab();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showToast('Error', 'Something went wrong', 'error');
    })
    .finally(() => {
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
    });
}

// Processing-specific
function attachProcessingListeners() {
    document.querySelectorAll('.view-quote-modal').forEach(btn => {
        btn.addEventListener('click', handleProcessingViewClick);
    });

    const confirmBtn = document.getElementById('processing-confirm-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', handleConfirmProcessing);
    }
}

// On-process-specific
function attachOnProcessListeners() {
    document.querySelectorAll('.view-on-process-modal').forEach(btn => {
        btn.addEventListener('click', handleOnProcessViewClick);
    });

    const confirmBtn = document.getElementById('onprocess-confirm-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', handleConfirmShipment);
    }
}

// ============================
// 6. VIEW HANDLER
// ============================
function handleOnProcessViewClick() {
    const orderData = {
        id: this.getAttribute('data-id'),
        userId: this.getAttribute('data-user-id'),
        ticket: this.getAttribute('data-ticket'),
        design: this.getAttribute('data-design'),
        mobile: this.getAttribute('data-mobile'),
        name: this.getAttribute('data-name'),
        printType: this.getAttribute('data-print-type'),
        quantity: this.getAttribute('data-quantity'),
        date: this.getAttribute('data-date'),
        status: this.getAttribute('data-status'),
        note: this.getAttribute('data-note'),
        address: this.getAttribute('data-address'),
        email: this.getAttribute('data-email'),
        pricing: this.getAttribute('data-pricing'),
        subtotal: this.getAttribute('data-subtotal'),
        viewable: this.getAttribute('data-viewable') === 'yes'
    };

    let items = [];
    try {
        const rawItems = this.getAttribute('data-items');
        console.log("Raw data-items:", rawItems);
        items = JSON.parse(rawItems || "[]");
        console.log("Parsed items:", items);
    } catch (e) {
        console.error("Error parsing shirt items:", e);
    }

    // Store data in modal
    document.getElementById('onProcessModal').setAttribute('data-current-id', orderData.id);
    document.getElementById('onprocess-modal-id').value = orderData.id;
    document.getElementById('onprocess-modal-user-id').value = orderData.userId;
    document.getElementById('onprocess-modal-email').value = orderData.email;
    document.getElementById('onprocess-modal-ticket-input').value = orderData.ticket;

    // Populate modal fields
    document.getElementById('onprocess-modal-ticket').textContent = orderData.ticket;
    document.getElementById('onprocess-modal-name').textContent = orderData.name;
    document.getElementById('onprocess-modal-mobile').textContent = orderData.mobile || 'N/A';
    document.getElementById('onprocess-modal-address').textContent = orderData.address || 'N/A';
    document.getElementById('onprocess-modal-email').textContent = orderData.email;
    document.getElementById('onprocess-modal-print-type').textContent = orderData.printType;
    document.getElementById('onprocess-modal-quantity').textContent = orderData.quantity;
    document.getElementById('onprocess-modal-pricing').textContent = orderData.pricing ? '₱' + parseFloat(orderData.pricing).toFixed(2) : 'N/A';
    document.getElementById('onprocess-modal-subtotal').textContent = orderData.subtotal ? '₱' + parseFloat(orderData.subtotal).toFixed(2) : 'N/A';
    document.getElementById('onprocess-modal-note').textContent = orderData.note || 'No notes';

    // 🆕 Populate Shirt Colors & Quantities
    const modal = document.getElementById("onProcessModal");
    const itemsContainer = modal.querySelector("#quote-modal-shirt-items");
    itemsContainer.innerHTML = "";

    if (items.length > 0) {
        items.forEach(item => {
            const div = document.createElement("div");
            div.classList.add("shirt-item");
            div.innerHTML = `
                <span class="shirt-color">${item.shirt_color}</span>
                <span class="shirt-qty">${item.quantity}</span>
            `;
            itemsContainer.appendChild(div);
        });
    } else {
        itemsContainer.innerHTML = "<em>No shirt colors added</em>";
    }

    // Design preview (same as you had)
    const fileExtension = orderData.design.split('.').pop().toLowerCase();
    const imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    if (imageFormats.includes(fileExtension)) {
        document.getElementById('onprocess-modal-design').src = '../user/' + orderData.design;
    } else {
        let placeholderSrc = '../file.png';
        if (fileExtension === 'psd') placeholderSrc = '../photoshop.png';
        if (fileExtension === 'pdf') placeholderSrc = '../pdf.png';
        if (fileExtension === 'ai') placeholderSrc = '../illustrator.png';
        document.getElementById('onprocess-modal-design').src = placeholderSrc;
    }

    document.getElementById('onprocess-modal-process-date').textContent = orderData.date || new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    // Show/hide view button
    const viewButtons = document.querySelectorAll('#onProcessModal .view-design-btn');
    viewButtons.forEach(button => {
        button.style.display = orderData.viewable ? 'inline-block' : 'none';
    });

    // Show modal
    document.getElementById('onProcessModal').style.display = 'block';
}

function handleProcessingViewClick() {
    const id = this.getAttribute('data-id');
    const ticket = this.getAttribute('data-ticket');
    const design = this.getAttribute('data-design');
    const printType = this.getAttribute('data-print-type');
    const quantity = this.getAttribute('data-quantity');
    const isViewable = this.getAttribute('data-viewable') === 'yes';
    const items = JSON.parse(this.getAttribute('data-items') || "[]"); // parse items safely


    // Store data in modal
    processingModal.setAttribute('data-current-id', id);

    // Populate modal fields
    document.getElementById('processing-modal-ticket').textContent = ticket;

    // Determine if we should show the actual file or a placeholder
    const fileExtension = design.split('.').pop().toLowerCase();
    const imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    
    if (imageFormats.includes(fileExtension)) {
        // Show the actual image file
        document.getElementById('processing-modal-design').src = '../user/' + design;
    } else {
        // Show appropriate placeholder based on file type
        let placeholderSrc = '../file.png'; // default placeholder
        if (fileExtension === 'psd') placeholderSrc = '../photoshop.png';
        if (fileExtension === 'pdf') placeholderSrc = '../pdf.png';
        if (fileExtension === 'ai') placeholderSrc = '../illustrator.png';
        
        document.getElementById('processing-modal-design').src = placeholderSrc;
    }
    
    document.getElementById('processing-modal-print-type').textContent = printType;
    document.getElementById('processing-modal-quantity').textContent = quantity;
    document.getElementById('processing-modal-id').value = id;
    document.getElementById('processing-modal-ticket-input').value = ticket;

    // Show/hide view button based on file type
    const viewButton = document.querySelector('.view-design-btn');
    if (viewButton) {
        if (isViewable) {
            viewButton.style.display = 'inline-block';
        } else {
            viewButton.style.display = 'none';
        }
    }

   // 👉 Populate Shirt Colors & Quantities
    const itemsContainer = document.getElementById("quote-modal-shirt-items");
    itemsContainer.innerHTML = "";
    if (items.length > 0) {
        items.forEach(item => {
            const div = document.createElement("div");
            div.classList.add("shirt-item");
            div.innerHTML = `
                <span class="shirt-color">${item.shirt_color}</span>
                <span class="shirt-qty">${item.quantity}</span>
            `;
            itemsContainer.appendChild(div);
        });
    } else {
        itemsContainer.innerHTML = "<em>No shirt colors added</em>";
    }

    // Show modal
    processingModal.style.display = 'block';
}
</script>

<?php include "includes/script-src.php";?>
</body>
</html>