<?php
require_once 'auth_check.php';
require_once '../db_connection.php';

// Protect login
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Role restrictions
if (isset($_SESSION['admin_role'])) {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($_SESSION['admin_role'] === "Field Manager" && $current_page != 'inventory.php') {
        header("Location: inventory.php");
        exit();
    }
    if ($_SESSION['admin_role'] === "Designer" && $current_page != 'orders.php') {
        header("Location: orders.php");
        exit();
    }
}

// Determine which tab is active
$active_tab = $_GET['tab'] ?? 'to-pickup';

$tab_titles = [
    'to-pickup' => 'To Pickup Orders',
    'on-pickup' => 'On Pickup Orders', 
    'to-ship' => 'To Ship Orders',
    'completed' => 'Completed Orders',
    'cancel' => 'Cancelled Orders'
];

// Then update your switch statement to use this array
$title = $tab_titles[$active_tab] ?? 'To Pickup Orders';

// Set status, pickup flag, and title based on active tab
$is_for_pickup = null; // default null
switch ($active_tab) {
    case 'on-pickup':
        $status_filter = "to-pick-up";
        $is_for_pickup = "yes";
        $title = "On Pickup Orders";
        break;
    case 'to-ship':
        $status_filter = "to_ship";
        $title = "To Ship Orders";
        break;
    case 'completed':
        $status_filter = "completed";
        $title = "Completed Orders";
        break;
    case 'cancel':
        $status_filter = "cancelled";
        $title = "Cancelled Orders";
        break;
    default: // to-pickup
        $status_filter = "to-pick-up";
        $is_for_pickup = "no";
        $title = "To Pickup Orders";
        break;
}

// Calculate counts for all tabs
$tab_counts = [
    'to-pickup' => 0,
    'on-pickup' => 0,
    'to-ship' => 0,
    'completed' => 0,
    'cancel' => 0
];

foreach ($tab_counts as $tab => $count) {
    $tab_status_filter = "";
    $tab_is_for_pickup = null;
    
    switch ($tab) {
        case 'on-pickup':
            $tab_status_filter = "to-pick-up";
            $tab_is_for_pickup = "yes";
            break;
        case 'to-ship':
            $tab_status_filter = "to_ship";
            break;
        case 'completed':
            $tab_status_filter = "completed";
            break;
        case 'cancel':
            $tab_status_filter = "cancelled";
            break;
        default: // to-pickup
            $tab_status_filter = "to-pick-up";
            $tab_is_for_pickup = "no";
            break;
    }
    
    // Build WHERE clause for this tab
    $tab_where_clauses = ["o.status = '" . $conn->real_escape_string($tab_status_filter) . "'"];
    
    if ($tab_is_for_pickup !== null) {
        $tab_where_clauses[] = "o.is_for_pickup = '" . $conn->real_escape_string($tab_is_for_pickup) . "'";
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

// Only include is_for_pickup if set
if ($is_for_pickup !== null) {
    $where_clauses[] = "o.is_for_pickup = '" . $conn->real_escape_string($is_for_pickup) . "'";
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
    <link rel="stylesheet" href="assets/css/quote-modal.css">
    <link rel="stylesheet" href="assets/css/admintoapprove.css">
   
<style>
    /* Apply to all modal close buttons, regardless of class */
.toship-modal-close,
.completed-modal-close,
.quote-modal-close {
  position: absolute;
  top: 12px;
  right: 16px;
  font-size: 24px;
  font-weight: bold;
  color: #444;
  cursor: pointer;
  transition: color 0.2s ease;
}

.toship-modal-close:hover,
.completed-modal-close:hover,
.quote-modal-close:hover {
  color: #000;
}

/* Make sure modal content is relatively positioned */
.quote-modal-content {
  position: relative;
}
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
#onpickup-modal-shirt-items,
#pickup-modal-shirt-items,#quote-modal-shirt-items,
#completed-modal-shirt-items  {
  background: #f9f9f9; /* subtle background */
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 8px 12px;
  margin-top: 5px;
}

.shirt-item {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  border-bottom: 1px dashed #ddd;
  font-size: 13px;
}

.shirt-item:last-child {
  border-bottom: none; /* remove last line */
}

.shirt-color {
  font-weight: 500;
  color: #333;
}

.shirt-qty {
  color: #555;
}

</style>
</head>

<body>
    <div class="container">
        
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
                <button class="tab-btn <?= $active_tab === 'to-pickup' ? 'active' : '' ?>" data-tab="to-pickup">To Pickup</button>
                <button class="tab-btn <?= $active_tab === 'on-pickup' ? 'active' : '' ?>" data-tab="on-pickup">On Pickup</button>
                <button class="tab-btn <?= $active_tab === 'to-ship' ? 'active' : '' ?>" data-tab="to-ship">To Ship</button>
                <button class="tab-btn <?= $active_tab === 'completed' ? 'active' : '' ?>" data-tab="completed">Completed</button>
                <button class="tab-btn <?= $active_tab === 'cancel' ? 'active' : '' ?>" data-tab="cancel">Cancelled</button>
            </div>
            <!-- Table -->
            <section class="table-card fade-in">
              <div class="table-header">
                <h3 class="table-title" id="table-title">
                    <?= $title ?>
                    <span class="badge"><?= $total_orders ?></span>
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
                          <a href="to-pick-up-orders.php" class="btn btn-outline">
                              <i class="fas fa-undo"></i>
                              <span>Reset</span>
                          </a>
                      </form>
                  </div>
              </div>
                
                <div id="to-pickup-table" class="table-responsive tab-content active">
    <table id="pickup-table">
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
      <tbody id="pickup-table-body">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($order = $result->fetch_assoc()): 
            // Extract just the filename from the path
            $designFilePath = $order['design_file'];
            $filename = basename($designFilePath);
            
            // Get the file extension
            $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            // Set thumbnail based on file extension
            if ($fileExtension === 'psd') {
                $thumbnail = "../photoshop.png";
            } elseif ($fileExtension === 'pdf') {
                $thumbnail = "../pdf.png";
            } elseif ($fileExtension === 'ai') {
                $thumbnail = "../illustrator.png";
            } else {
                // For image files, use the actual file
                $thumbnail = "../user/" . $designFilePath;
            }
        ?>
            <tr>
                <td><?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <div class="user-cell">
                        <img src="<?php echo $thumbnail; ?>" alt="file design" width="50" height="50" onerror="this.onerror=null; this.src='../placeholder-image.png';">
                        <span><?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <span class="status status-success">
                        <?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </td>
                <td>
                    <!-- In the PHP section where you generate the table rows -->
<button class="btn btn-outline view-pickup-modal" 
        data-id="<?php echo htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?>"
        data-user-id="<?php echo htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8'); ?>"
        data-ticket="<?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?>"
        data-design="<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>"
        data-mobile="<?php echo htmlspecialchars($order['phone_number'], ENT_QUOTES, 'UTF-8'); ?>"
        data-name="<?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?>"
        data-print-type="<?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?>"
        data-quantity="<?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?>"
        data-date="<?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?>"
        data-status="<?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>"
        data-note="<?php echo htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8'); ?>"
        data-address="<?php echo htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8'); ?>"
        data-email="<?php echo htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8'); ?>"
        data-pricing="<?php echo htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8'); ?>"
        data-subtotal="<?php echo htmlspecialchars($order['subtotal'], ENT_QUOTES, 'UTF-8'); ?>"
        data-thumbnail="<?php echo $thumbnail; ?>"> <!-- Add this line -->
    View
</button>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7">No orders ready for pickup</td>
        </tr>
    <?php endif; ?>
</tbody>
    </table>
    <div class="pagination">
    <?php 
    // Build base URL with all parameters except page
    $base_url = "?tab=" . urlencode($active_tab);
    if (!empty($filter_print)) $base_url .= "&print_type=" . urlencode($filter_print);
    if (!empty($filter_start)) $base_url .= "&start_date=" . urlencode($filter_start);
    if (!empty($filter_end)) $base_url .= "&end_date=" . urlencode($filter_end);
    if (!empty($filter_search)) $base_url .= "&search=" . urlencode($filter_search);
    ?>
    
    <?php if ($page > 1): ?>
        <a href="<?= $base_url ?>&page=<?= $page - 1 ?>" class="btn btn-outline">&laquo; Prev</a>
    <?php endif; ?>

    <!-- Always show first page -->
    <a href="<?= $base_url ?>&page=1" class="btn <?= $page == 1 ? 'btn-primary' : 'btn-outline' ?>">1</a>

    <!-- Dots -->
    <?php if ($page > 3): ?>
        <span class="dots">...</span>
    <?php endif; ?>

    <!-- Pages around current -->
    <?php for ($i = max(2, $page - 2); $i <= min($total_pages - 1, $page + 2); $i++): ?>
        <a href="<?= $base_url ?>&page=<?= $i ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <!-- Dots -->
    <?php if ($page < $total_pages - 2): ?>
        <span class="dots">...</span>
    <?php endif; ?>

    <!-- Always show last page -->
    <?php if ($total_pages > 1): ?>
        <a href="<?= $base_url ?>&page=<?= $total_pages ?>" class="btn <?= $page == $total_pages ? 'btn-primary' : 'btn-outline' ?>">
            <?= $total_pages ?>
        </a>
    <?php endif; ?>

    <?php if ($page < $total_pages): ?>
        <a href="<?= $base_url ?>&page=<?= $page + 1 ?>" class="btn btn-outline">Next &raquo;</a>
    <?php endif; ?>
</div>

</div>

<!-- On Pickup Table -->
<?php include "includes/tables/onpickup-table.php"; ?>
<?php include "includes/tables/to-ship-table.php"; ?>
<?php include "includes/tables/completed-table.php"; ?>
<?php include "includes/tables/cancelled-table.php"; ?>
            </section>
        </main>
    </div>
    <!-- On Pickup Modal -->
<div id="onPickupModal" class="quote-modal">
    <div class="quote-modal-content">
        <span class="quote-modal-close">&times;</span>
        <h2>Order On Pickup</h2>
        <div class="quote-modal-body">
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Ticket #:</span>
                    <span id="onpickup-modal-ticket" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Attempt:</span>
                    <span id="onpickup-modal-attempt" class="quote-modal-value"></span>
                </div>
            </div>
            
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Customer:</span>
                    <span id="onpickup-modal-name" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Mobile #:</span>
                    <span id="onpickup-modal-mobile" class="quote-modal-value"></span>
                </div>
            </div>
            
            <div class="quote-modal-row">
                <span class="quote-modal-label">Address:</span>
                <span id="onpickup-modal-address" class="quote-modal-value address-value"></span>
            </div>

            <div class="quote-modal-row">
                <span class="quote-modal-label">Items:</span>
                <div id="onpickup-modal-shirt-items" class="quote-modal-value"></div>
            </div>
            
            <div class="quote-modal-row">
                <span class="quote-modal-label">Last Pickup Attempt:</span>
                <span id="onpickup-modal-last-attempt" class="quote-modal-value"></span>
            </div>
        </div>
        <div class="quote-modal-footer">
            <input type="hidden" id="onpickup-modal-id">
            <input type="hidden" id="onpickup-modal-user-id">
            <input type="hidden" id="onpickup-modal-email">
            <input type="hidden" id="onpickup-modal-ticket">
            <input type="hidden" id="onpickup-modal-attempt">
            
        
<div class="quote-modal-footer">
    <button id="onpickup-reattempt" class="quote-modal-btn btn-warning">Re-attempt</button>
    <button id="onpickup-failed" class="quote-modal-btn btn-danger">Failed</button>
    <button id="onpickup-reject" class="quote-modal-btn btn-outline">Reject</button>
    <button id="onpickup-close" class="quote-modal-btn btn-secondary">Close</button>
</div>
  <button id="onpickup-pickedup" class="quote-modal-btn btn-success">Picked Up</button>
        </div>
    </div>
</div>
  
    <!-- Pickup Modal -->
    <div id="pickupModal" class="quote-modal">
        <div class="quote-modal-content">
          
            <h2>Order Ready for Pickup</h2>
            <div class="quote-modal-body">
                <!-- Group 1: Ticket and Customer in one row -->
                <div class="quote-modal-row grouped-row">
                    <div class="grouped-item">
                        <span class="quote-modal-label">Ticket #:</span>
                        <span id="pickup-modal-ticket" class="quote-modal-value"></span>
                    </div>
                    <div class="grouped-item">
                        <span class="quote-modal-label">Customer:</span>
                        <span id="pickup-modal-name" class="quote-modal-value"></span>
                    </div>
                </div>
                
                <!-- Group 2: Image with buttons and details -->
                <div class="quote-modal-row grouped-row-2">
                        <div class="grouped-item">
                            <span class="quote-modal-label">Design:</span>
                            <div class="design-image-container">
                                <img id="pickup-modal-design" src="" alt="Design" class="design-image">
                                <div class="design-buttons">
                                    <button class="view-design-btn">View</button>
                                    <button class="download-design-btn">Download</button>
                                </div>
                            </div>
                        </div>
                    <div class="grouped-item details-column">
                        <div class="detail-row">
                            <span class="quote-modal-label">Mobile #:</span>
                            <span id="pickup-modal-mobile" class="quote-modal-value"></span>
                        </div>
                        <div class="detail-row">
                            <span class="quote-modal-label">Print Type:</span>
                            <span id="pickup-modal-print-type" class="quote-modal-value"></span>
                        </div>
                        <div class="detail-row">
                            <span class="quote-modal-label">Quantity:</span>
                            <span id="pickup-modal-quantity" class="quote-modal-value"></span>
                        </div>
                        <!-- Shirt Colors & Quantities Section -->
                        <div class="detail-row">
                            <span class="quote-modal-label">Items:</span>
                            <div id="pickup-modal-shirt-items" class="quote-modal-value shirt-items">
                            </div>
                        </div>

                    </div>
                </div>
                


                <!-- Note -->
                <div class="quote-modal-row">
                    <span class="quote-modal-label">Note:</span>
                    <span id="pickup-modal-note" class="quote-modal-value note-value"></span>
                </div>
                
                <!-- Group 3: Date and Status -->
                <div class="quote-modal-row grouped-row">
                    <div class="grouped-item">
                        <span class="quote-modal-label">Date:</span>
                        <span id="pickup-modal-date" class="quote-modal-value"></span>
                    </div>
                    <div class="grouped-item">
                        <span class="quote-modal-label">Status:</span>
                        <span id="pickup-modal-status" class="quote-modal-value"></span>
                    </div>
                </div>
                
                <!-- Address -->
                <div class="quote-modal-row">
                    <span class="quote-modal-label">Address:</span>
                    <span id="pickup-modal-address" class="quote-modal-value address-value"></span>
                </div>
                
                <!-- Pricing Information -->
                <div class="quote-modal-row grouped-row">
                    <div class="grouped-item">
                        <span class="quote-modal-label">Unit Price:</span>
                        <span id="pickup-modal-pricing" class="quote-modal-value">₱0.00</span>
                    </div>
                    <div class="grouped-item">
                        <span class="quote-modal-label">Subtotal:</span>
                        <span id="pickup-modal-subtotal" class="quote-modal-value">₱0.00</span>
                    </div>
                </div>
            </div>
            <div class="quote-modal-footer">
                <input type="hidden" id="pickup-modal-id">
                <input type="hidden" id="pickup-modal-user-id">
                <input type="hidden" id="pickup-modal-email">
                <input type="hidden" id="pickup-modal-ticket">
                <input type="hidden" id="pickup-modal-quantity">
                <input type="hidden" id="pickup-modal-pricing">
                <input type="hidden" id="pickup-modal-subtotal">
                <input type="hidden" id="pickup-modal-address">
                
                <button id="pickup-modal-confirm" class="quote-modal-btn btn-primary">Confirm Pickup</button>
                <button id="pickup-modal-close" class="quote-modal-btn btn-outline">Close</button>
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


    <script src="assets/js/to-pick-up-table-switching.js"></script>
    <script src="assets/js/to-pick-up-image-viewer.js"></script>
    <script src="assets/js/to-pick-up-confirm.js"></script>

    <script>
        // DOM Elements
const onPickupModal = document.getElementById('onPickupModal');
const onPickupModalClose = document.querySelector('#onPickupModal .quote-modal-close');
const reattemptBtn = document.getElementById('onpickup-reattempt');
const failedBtn = document.getElementById('onpickup-failed');
const rejectBtn = document.getElementById('onpickup-reject');
const closeOnPickupBtn = document.getElementById('onpickup-close');
const pickedUpBtn = document.getElementById('onpickup-pickedup');

// View button click handler for on-pickup orders
function handleOnPickupViewButtonClick() {
    const id = this.getAttribute('data-id');
    const userId = this.getAttribute('data-user-id');
    const ticket = this.getAttribute('data-ticket');
    const name = this.getAttribute('data-name');
    const mobile = this.getAttribute('data-mobile');
    const address = this.getAttribute('data-address');
    const email = this.getAttribute('data-email');
    const attempt = this.closest('tr').querySelector('td:nth-child(6)').textContent.trim();
    
        // Populate shirt colors & quantities
    const shirtItemsContainer = document.getElementById('onpickup-modal-shirt-items');
    shirtItemsContainer.innerHTML = ''; // Clear previous content

    const itemsData = this.getAttribute('data-items');
    console.log('data-items attribute:', itemsData); // <-- Check the raw data

    if (itemsData) {
        try {
            const shirtItems = JSON.parse(itemsData);
            console.log('Parsed shirt items:', shirtItems); // <-- Check parsed JSON
            if (shirtItems.length > 0) {
                shirtItems.forEach(item => {
        const div = document.createElement("div");
        div.classList.add("shirt-item");
        div.innerHTML = `
          <span class="shirt-color">${item.shirt_color}</span>
          <span class="shirt-qty">${item.quantity}</span>
        `;
        shirtItemsContainer.appendChild(div); // ✅ keep same container variable
      });
            } else {
                shirtItemsContainer.textContent = 'N/A';
            }
        } catch (e) {
            console.error('Failed to parse shirt items JSON', e);
            shirtItemsContainer.textContent = 'N/A';
        }
    } else {
        console.log('No itemsData found');
        shirtItemsContainer.textContent = 'N/A';
    }

    
    // Store data in modal
    onPickupModal.setAttribute('data-current-id', id);
    document.getElementById('onpickup-modal-id').value = id;
    document.getElementById('onpickup-modal-user-id').value = userId;
    document.getElementById('onpickup-modal-email').value = email;
    document.getElementById('onpickup-modal-ticket').value = ticket;
    document.getElementById('onpickup-modal-attempt').value = attempt;
    
    // Populate modal fields
    document.getElementById('onpickup-modal-ticket').textContent = ticket;
    document.getElementById('onpickup-modal-attempt').textContent = attempt;
    document.getElementById('onpickup-modal-name').textContent = name;
    document.getElementById('onpickup-modal-mobile').textContent = mobile || 'N/A';
    document.getElementById('onpickup-modal-address').textContent = address || 'N/A';
    document.getElementById('onpickup-modal-last-attempt').textContent = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Show modal
    onPickupModal.style.display = 'block';
}

// Handle reattempt
function handleReattempt() {
    const id = onPickupModal.getAttribute('data-current-id');
    const userId = document.getElementById('onpickup-modal-user-id').value;
    const email = document.getElementById('onpickup-modal-email').value;
    const ticket = document.getElementById('onpickup-modal-ticket').value;
    const attempt = document.getElementById('onpickup-modal-attempt').value;

    // Show loading state
    const originalText = reattemptBtn.textContent;
    reattemptBtn.disabled = true;
    reattemptBtn.textContent = 'Processing...';

    fetch('functions/onpickup_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'reattempt',
            id: id,
            user_id: userId,
            email: email,
            ticket: ticket,
            attempt: attempt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            onPickupModal.style.display = 'none';
            refreshPickupTable();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        reattemptBtn.disabled = false;
        reattemptBtn.textContent = originalText;
    });
}

// Handle failed
function handleFailed() {
    const id = onPickupModal.getAttribute('data-current-id');
    const userId = document.getElementById('onpickup-modal-user-id').value;
    const email = document.getElementById('onpickup-modal-email').value;
    const ticket = document.getElementById('onpickup-modal-ticket').value;
    const attempt = document.getElementById('onpickup-modal-attempt').value;

    // Show loading state
    const originalText = failedBtn.textContent;
    failedBtn.disabled = true;
    failedBtn.textContent = 'Processing...';

    fetch('functions/onpickup_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'failed',
            id: id,
            user_id: userId,
            email: email,
            ticket: ticket,
            attempt: attempt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            onPickupModal.style.display = 'none';
            refreshPickupTable();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        failedBtn.disabled = false;
        failedBtn.textContent = originalText;
    });
}

// Handle reject
function handleReject() {
    if (!confirm('Are you sure you want to reject this order? This action cannot be undone.')) {
        return;
    }

    const id = onPickupModal.getAttribute('data-current-id');
    const userId = document.getElementById('onpickup-modal-user-id').value;
    const email = document.getElementById('onpickup-modal-email').value;
    const ticket = document.getElementById('onpickup-modal-ticket').value;
    const attempt = document.getElementById('onpickup-modal-attempt').value;

    // Show loading state
    const originalText = rejectBtn.textContent;
    rejectBtn.disabled = true;
    rejectBtn.textContent = 'Processing...';

    fetch('functions/onpickup_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'reject',
            id: id,
            user_id: userId,
            email: email,
            ticket: ticket,
            attempt: attempt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            onPickupModal.style.display = 'none';
            refreshPickupTable();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        rejectBtn.disabled = false;
        rejectBtn.textContent = originalText;
    });
}
function handlePickedUp() {
    const id = onPickupModal.getAttribute('data-current-id');
    const userId = document.getElementById('onpickup-modal-user-id').value;
    const email = document.getElementById('onpickup-modal-email').value;
    const ticket = document.getElementById('onpickup-modal-ticket').value;
    const attempt = document.getElementById('onpickup-modal-attempt').value;

    // Show loading state
    const originalText = pickedUpBtn.textContent;
    pickedUpBtn.disabled = true;
    pickedUpBtn.textContent = 'Processing...';

    fetch('functions/onpickup_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'pickedup',
            id: id,
            user_id: userId,
            email: email,
            ticket: ticket,
            attempt: attempt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            onPickupModal.style.display = 'none';
            refreshPickupTable();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        pickedUpBtn.disabled = false;
        pickedUpBtn.textContent = originalText;
    });
}

// Modal close handlers
function closeOnPickupModal() {
    onPickupModal.style.display = 'none';
}

function handleWindowClick(event) {
    if (event.target === onPickupModal) {
        closeOnPickupModal();
    }
}

// Attach all event listeners
function attachOnPickupEventListeners() {
    // View buttons
    document.querySelectorAll('.view-on-pickup-modal').forEach(button => {
        button.addEventListener('click', handleOnPickupViewButtonClick);
    });
    
    // Modal close
    onPickupModalClose.addEventListener('click', closeOnPickupModal);
    closeOnPickupBtn.addEventListener('click', closeOnPickupModal);
    window.addEventListener('click', handleWindowClick);
    
    // Action buttons
    reattemptBtn.addEventListener('click', handleReattempt);
    failedBtn.addEventListener('click', handleFailed);
    rejectBtn.addEventListener('click', handleReject);
    pickedUpBtn.addEventListener('click', handlePickedUp);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    attachOnPickupEventListeners();
    
    // Use existing refresh function from to-pick-up-confirm.js
    if (typeof refreshPickupTable !== 'function') {
        function refreshPickupTable() {
            location.reload(); // Fallback if not defined
        }
    }
});
    </script>


<?php include "includes/script-src.php";?>
</body>
</html>