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
// FILTER + PAGINATION LOGIC
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

// Pagination
$orders_per_page = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $orders_per_page;
$default_limit = 100;

// build $where_clauses as you already have, then count total:
$count_query = "SELECT COUNT(*) as total 
                FROM orders o 
                INNER JOIN users u ON o.user_id = u.id
                WHERE " . implode(" AND ", $where_clauses);
$count_result = $conn->query($count_query);
$total_orders = $count_result ? intval($count_result->fetch_assoc()['total']) : 0;

if (empty($filter_print) && empty($filter_start) && empty($filter_end) && empty($filter_search) && $total_orders > $default_limit) {
    $total_orders = $default_limit;
}
$total_pages = ceil($total_orders / $orders_per_page);

// Fetch orders
$query = "SELECT o.*, u.name, u.email, u.phone_number 
          FROM orders o 
          INNER JOIN users u ON o.user_id = u.id
          $where_sql
          ORDER BY o.created_at DESC";

if (empty($filter_print) && empty($filter_start) && empty($filter_end) && empty($filter_search)) {
    $query .= " LIMIT $default_limit";
}

$result = $conn->query($query);
$all_orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$orders = array_slice($all_orders, $offset, $orders_per_page);

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
    <?php
    require_once '../db_connection.php';

    // Fetch only orders with status 'processing'
    $sql = "SELECT orders.*, users.name 
            FROM orders 
            INNER JOIN users ON orders.user_id = users.id 
            WHERE orders.status = 'processing' AND orders.is_for_processing ='no'
            ORDER BY orders.created_at DESC";
    $result = $conn->query($sql);
    ?>
    <tbody id="admins-table-body">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($order = $result->fetch_assoc()):
                        // Fetch shirt items for this order
        $items_sql = "SELECT shirt_color, quantity 
                      FROM items 
                      WHERE order_id = " . intval($order['id']);
        $items_result = $conn->query($items_sql);

        $shirtItems = [];
        if ($items_result && $items_result->num_rows > 0) {
            while ($item = $items_result->fetch_assoc()) {
                $shirtItems[] = $item;
            }
        } ?>
                
                <tr>
                    <td><?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <div class="user-cell">
                            <img src="../user/<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>" alt="file design" width="50" height="50">
                            <span><?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <span class="status status-warning">
                            <?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-outline view-quote-modal" 
                                data-id="<?php echo htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-ticket="<?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-design="<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-print-type="<?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-quantity="<?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-items='<?php echo json_encode($shirtItems, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                            Process
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No orders currently for processing</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="btn btn-outline">&laquo; Prev</a>
                    <?php endif; ?>

                    <!-- Always show first page -->
                    <a href="?page=1" class="btn <?= $page == 1 ? 'btn-primary' : 'btn-outline' ?>">1</a>

                    <!-- Dots -->
                    <?php if ($page > 3): ?>
                        <span class="dots">...</span>
                    <?php endif; ?>

                    <!-- Pages around current -->
                    <?php for ($i = max(2, $page - 2); $i <= min($total_pages - 1, $page + 2); $i++): ?>
                        <a href="?page=<?= $i ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Dots -->
                    <?php if ($page < $total_pages - 2): ?>
                        <span class="dots">...</span>
                    <?php endif; ?>

                    <!-- Always show last page -->
                    <?php if ($total_pages > 1): ?>
                        <a href="?page=<?= $total_pages ?>" class="btn <?= $page == $total_pages ? 'btn-primary' : 'btn-outline' ?>">
                            <?= $total_pages ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="btn btn-outline">Next &raquo;</a>
                    <?php endif; ?>
                </div>
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
          <div class="design-buttons">
           <button class="view-design-btn" id="modal-view-btn">View</button>
            <button class="download-design-btn">Download</button>
          </div>
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
      <button id="processing-modal-confirm" class="quote-modal-btn btn-process">Mark as On Process</button>
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
          <div class="design-buttons">
            <button class="view-design-btn">View</button>
            <button class="download-design-btn">Download</button>
          </div>
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
      <button id="onprocess-modal-ship" class="quote-modal-btn btn-process">Mark as To Ship</button>
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
// Get DOM elements
const processingModal = document.getElementById('processingModal');
const processingModalClose = document.querySelector('.quote-modal-close');
const confirmBtn = document.getElementById('processing-modal-confirm');
const closeBtn = document.getElementById('processing-modal-close');

// Handle view button click
function handleViewButtonClick() {
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

// Handle confirm processing
function handleConfirmProcessing() {
    const id = document.getElementById('processing-modal-id').value;
    const ticket = document.getElementById('processing-modal-ticket-input').value;

    // Show loading state
    const originalText = confirmBtn.textContent;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';

    fetch('functions/update_processing.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&ticket=${ticket}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            processingModal.style.display = 'none';
            refreshOrdersTable(); // Refresh table after successful update
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

// Modal close handlers
function closeModal() {
    processingModal.style.display = 'none';
}

function handleWindowClick(event) {
    if (event.target === processingModal) {
        closeModal();
    }
}

// Table refresh functionality
function refreshOrdersTable() {
        // Get all filter values from the form
        const printType = document.querySelector('select[name="print_type"]').value;
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const search = document.querySelector('input[name="search"]').value;
        const params = new URLSearchParams(new FormData(document.querySelector('.filter-form')));
    fetch('functions/get_processing_orders.php?' + params.toString())
        .then(response => response.text())
        .then(data => {
            document.getElementById('admins-table-body').innerHTML = data;
            attachEventListeners(); // Reattach event listeners after refresh
        })
        .catch(error => console.error('Error refreshing table:', error));
}

// Image Viewer functionality
function setupImageViewer() {
    const imageViewerModal = document.getElementById('imageViewerModal');
    const closeViewer = document.querySelector('.close-viewer');

    // View button functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-design-btn')) {
            const container = e.target.closest('.design-image-container');
            const imgElement = container.querySelector('img');
            const ticket = document.getElementById('processing-modal-ticket').textContent;
            
            // Get the actual design file from the button's data attribute
            const viewButton = document.querySelector('.view-quote-modal[data-ticket="' + ticket + '"]');
            const designFile = viewButton.getAttribute('data-design');
            
            // Check if it's an image format that can be displayed in browser
            const fileExtension = designFile.split('.').pop().toLowerCase();
            const displayableFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            
            if (displayableFormats.includes(fileExtension)) {
                // Show the actual image
                document.getElementById('expandedDesignImage').src = '../user/' + designFile;
                imageViewerModal.style.display = 'block';
            } else {
                // This shouldn't happen since we hide the button, but just in case
                showToast('Cannot Preview', 'This file format cannot be previewed in the browser. Please download the file to view it.', 'warning');
            }
        }
    });

    // Close button functionality
    closeViewer.addEventListener('click', function() {
        imageViewerModal.style.display = 'none';
    });

    // Close when clicking outside image
    imageViewerModal.addEventListener('click', function(e) {
        if (e.target === imageViewerModal) {
            imageViewerModal.style.display = 'none';
        }
    });
}

// Download functionality
function setupDownloadButtons() {
    // Remove any previous event listener to avoid multiple downloads
    const downloadBtn = document.querySelector('.download-design-btn');
    if (downloadBtn) {
        // Clone the button to remove previous listeners
        const newBtn = downloadBtn.cloneNode(true);
        downloadBtn.parentNode.replaceChild(newBtn, downloadBtn);

        newBtn.addEventListener('click', function() {
            const container = newBtn.closest('.design-image-container');
            const imgElement = container.querySelector('img');
            const ticket = document.getElementById('processing-modal-ticket').textContent;
            const printType = document.getElementById('processing-modal-print-type').textContent;
            
            // Get the actual design file path from the button's data attribute
            const viewButton = document.querySelector('.view-quote-modal[data-ticket="' + ticket + '"]');
            const designFile = viewButton.getAttribute('data-design');
            
            // Create download link for the actual file, not the thumbnail
            const link = document.createElement('a');
            link.href = '../user/' + designFile;
            
            // Extract filename and extension
            const filename = designFile.split('/').pop();
            const extension = filename.split('.').pop();
            
            link.download = `${ticket}-${printType.toLowerCase().replace(/ /g, '-')}.${extension}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
}

// Attach all event listeners
function attachEventListeners() {
    // View buttons
    document.querySelectorAll('.view-quote-modal').forEach(button => {
        button.addEventListener('click', handleViewButtonClick);
    });
    
    // Modal buttons
    confirmBtn.addEventListener('click', handleConfirmProcessing);
    closeBtn.addEventListener('click', closeModal);
    processingModalClose.addEventListener('click', closeModal);
    window.addEventListener('click', handleWindowClick);
    
    // Image viewer and download
    setupImageViewer();
    setupDownloadButtons();
}

// Initialize
function init() {
    attachEventListeners();
    refreshOrdersTable();
    
    // Set up periodic refresh (every 5 seconds)
    setInterval(refreshOrdersTable, 5000);
}

// Toast notification function
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

// Start the application
document.addEventListener('DOMContentLoaded', init);
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    // Function to switch tabs
   // Function to switch tabs
function switchTab(tabId) {
    // Remove active class from all buttons
    tabButtons.forEach(btn => btn.classList.remove('active'));
    
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
        content.style.display = 'none';
    });
    
    // Add active class to clicked button
    const activeButton = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }
    
    // Show corresponding content
    const activeContent = document.getElementById(`${tabId}-table`);
    if (activeContent) {
        activeContent.classList.add('active');
        activeContent.style.display = 'block';
        
        // Update the title based on the active tab
        const titleElement = document.getElementById('table-title');
        if (tabId === 'processing') {
            titleElement.innerHTML = 'Processing Orders <span class="badge">' + tabCounts.processing + '</span>';
            refreshOrdersTable(); // Use your existing refresh function
        } else if (tabId === 'on-process') {
            titleElement.innerHTML = 'On Process Orders <span class="badge">' + tabCounts['on-process'] + '</span>';
            updateOnProcessTable(); // Use the function from onprocess-table.php
        }
    }
    
    // Update URL parameter without reloading
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.history.replaceState({}, '', url);
    
    // Save to localStorage
    localStorage.setItem('activeTab', tabId);
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
</script>
<?php include "includes/script-src.php";?>
</body>
</html>