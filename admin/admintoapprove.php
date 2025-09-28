<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Role-based redirect (Field Manager, Designer restrictions)
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

// Include database connection
require_once '../db_connection.php';

// --- Filter setup ---
$filter_print  = isset($_GET['print_type']) ? trim($_GET['print_type']) : '';
$filter_start  = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end    = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filter_search = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Pagination setup ---
$orders_per_page = 7;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $orders_per_page;

// --- Maximum default orders ---
$default_limit = 100;

// --- Build WHERE clauses (your existing code is fine) ---
$where_clauses = [];
$where_clauses[] = "o.status = 'pending'";

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

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}


// --- Simple count ---
$count_query = "SELECT COUNT(*) as total 
                FROM orders o
                INNER JOIN users u ON o.user_id = u.id
                WHERE o.status = 'pending'";
$count_result = $conn->query($count_query);
$total_orders = $count_result ? intval($count_result->fetch_assoc()['total']) : 0;

// Apply default limit if no filters
if (empty($filter_print) && empty($filter_start) && empty($filter_end) && empty($filter_search) && $total_orders > $default_limit) {
    $total_orders = $default_limit;
}
$total_pages = ceil($total_orders / $orders_per_page);

// --- Simple query - let get_admin_orders.php handle pagination ---
$query = "SELECT o.* 
          FROM orders o 
          INNER JOIN users u ON o.user_id = u.id
          WHERE o.status = 'pending'
          ORDER BY o.created_at DESC
          LIMIT 6"; // Just get first 6 for initial load

$result = $conn->query($query);
$orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>



  
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <?php include "includes/link-css.php";?>
    <link rel="stylesheet" href="assets/css/admintoapprove.css">
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
                        <span style="margin-right:8px;">Dark Mode</span>
                        <i class="fas fa-moon"></i>
                    </div>
                    <?php include "includes/notification.php";?>
                </div>
                <?php include "includes/profile.php";?>
            </header>
            
            <!-- Table -->
            <section class="table-card fade-in">
              <div class="table-header">
                  <h3 class="table-title">
                      Pending Orders 
                      <span class="badge"><?= $total_orders ?></span>
                  </h3>
                  <div class="table-actions">
                      <!-- FILTER FORM -->
                      <form method="GET" class="filter-form">
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
                          <a href="admintoapprove.php" class="btn btn-outline">
                              <i class="fas fa-undo"></i>
                              <span>Reset</span>
                          </a>
                      </form>
                  </div>
              </div>

                <div class="table-responsive">
                    <table id="admins-table">
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
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($order = $result->fetch_assoc()): ?>
                                <?php
                                $items_sql = "SELECT shirt_color, quantity FROM items WHERE order_id = " . intval($order['id']);
                                $items_result = $conn->query($items_sql);
                                $shirtItems = [];
                                if ($items_result && $items_result->num_rows > 0) {
                                    while ($item = $items_result->fetch_assoc()) {
                                        $shirtItems[] = $item;
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['ticket']) ?></td>
                                    <td>
                                        <div class="user-cell">
                                            <img src="../user/<?= htmlspecialchars($order['design_file']) ?>" alt="file design" width="50" height="50">
                                            <span><?= htmlspecialchars($order['name']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($order['print_type']) ?></td>
                                    <td><?= htmlspecialchars($order['quantity']) ?></td>
                                    <td><?= htmlspecialchars(date('M d, Y', strtotime($order['created_at']))) ?></td>
                                    <td><span class="status status-warning"><?= htmlspecialchars($order['status']) ?></span></td>
                                    <td>
                                        <button class="btn btn-outline view-quote-modal" 
                                            data-id="<?= $order['id'] ?>"
                                            data-user-id="<?= $order['user_id'] ?>"
                                            data-pricing="<?= $order['pricing'] ?>"
                                            data-subtotal="<?= $order['subtotal'] ?>"
                                            data-ticket="<?= $order['ticket'] ?>"
                                            data-design="<?= $order['design_file'] ?>"
                                            data-mobile="<?= $order['phone_number'] ?>"
                                            data-name="<?= $order['name'] ?>"
                                            data-print-type="<?= $order['print_type'] ?>"
                                            data-quantity="<?= $order['quantity'] ?>"
                                            data-date="<?= date('M d, Y', strtotime($order['created_at'])) ?>"
                                            data-status="<?= $order['status'] ?>"
                                            data-note="<?= $order['note'] ?>"
                                            data-address="<?= $order['address'] ?>"
                                            data-items='<?= json_encode($shirtItems, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                            View
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7">No orders found</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination" id="paginationContainer">
                    <?php 
                    // Build pagination links with preserved filters
                    $query_params = [];
                    if (!empty($filter_print)) $query_params['print_type'] = $filter_print;
                    if (!empty($filter_start)) $query_params['start_date'] = $filter_start;
                    if (!empty($filter_end)) $query_params['end_date'] = $filter_end;
                    if (!empty($filter_search)) $query_params['search'] = $filter_search;
                    
                    if ($page > 1): 
                        $query_params['page'] = $page - 1;
                    ?>
                        <a href="?<?= http_build_query($query_params) ?>" class="btn btn-outline">&laquo; Prev</a>
                    <?php endif; ?>

                    <!-- Always show first page -->
                    <?php $query_params['page'] = 1; ?>
                    <a href="?<?= http_build_query($query_params) ?>" class="btn <?= $page == 1 ? 'btn-primary' : 'btn-outline' ?>">1</a>

                    <!-- Dots -->
                    <?php if ($page > 3): ?>
                        <span class="dots">...</span>
                    <?php endif; ?>

                    <!-- Pages around current -->
                    <?php for ($i = max(2, $page - 2); $i <= min($total_pages - 1, $page + 2); $i++): 
                        $query_params['page'] = $i;
                    ?>
                        <a href="?<?= http_build_query($query_params) ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Dots -->
                    <?php if ($page < $total_pages - 2): ?>
                        <span class="dots">...</span>
                    <?php endif; ?>

                    <!-- Always show last page -->
                    <?php if ($total_pages > 1): 
                        $query_params['page'] = $total_pages;
                    ?>
                        <a href="?<?= http_build_query($query_params) ?>" class="btn <?= $page == $total_pages ? 'btn-primary' : 'btn-outline' ?>">
                            <?= $total_pages ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): 
                        $query_params['page'] = $page + 1;
                    ?>
                        <a href="?<?= http_build_query($query_params) ?>" class="btn btn-outline">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- Modals & Scripts (your modal code unchanged) -->
     <div id="quoteModal" class="quote-modal">
      <div class="quote-modal-content">
        <span class="quote-modal-close">&times;</span>
        <h2>Order Details</h2>
        <div class="quote-modal-body">
          <!-- Group 1: Ticket and Customer in one row -->
          <div class="quote-modal-row grouped-row">
            <div class="grouped-item">
              <span class="quote-modal-label">Ticket #:</span>
              <span id="quote-modal-ticket" class="quote-modal-value"></span>
            </div>
            <div class="grouped-item">
              <span class="quote-modal-label">Customer:</span>
              <span id="quote-modal-name" class="quote-modal-value"></span>
            </div>
          </div>
          
          <!-- Group 2: Image with buttons and details -->
          <div class="quote-modal-row grouped-row-2">
            <div class="grouped-item">
              <span class="quote-modal-label">Design:</span>
              <div class="design-image-container">
                <img id="quote-modal-design" src="" alt="Design" class="design-image">
                <div class="design-buttons">
                  <button class="view-design-btn">View</button>
                  <button class="download-design-btn">Download</button>
                </div>
              </div>
            </div>
            <div class="grouped-item details-column">
              <div class="detail-row">
                <span class="quote-modal-label">Mobile #:</span>
                <span id="quote-modal-mobile" class="quote-modal-value"></span>
              </div>
              <div class="detail-row">
                <span class="quote-modal-label">Print Type:</span>
                <span id="quote-modal-print-type" class="quote-modal-value"></span>
              </div>
              <div class="detail-row">
                <span class="quote-modal-label">Quantity:</span>
                <span id="quote-modal-quantity" class="quote-modal-value"></span>
              </div>
              <!-- Shirt Colors & Quantities Section -->
              <div class="detail-row">
                <span class="quote-modal-label">Items:</span>
                <div id="quote-modal-shirt-items" class="shirt-items-container">
                </div>
              </div>

            </div>
          </div>

          
          
          <!-- Note -->
          <div class="quote-modal-row">
            <span class="quote-modal-label">Note:</span>
            <span id="quote-modal-note" class="quote-modal-value note-value"></span>
          </div>
          
          <!-- Group 3: Date and Status -->
          <div class="quote-modal-row grouped-row">
            <div class="grouped-item">
              <span class="quote-modal-label">Date:</span>
              <span id="quote-modal-date" class="quote-modal-value"></span>
            </div>
            <div class="grouped-item">
              <span class="quote-modal-label">Status:</span>
              <span id="quote-modal-status" class="quote-modal-value"></span>
            </div>
          </div>
          
          <!-- Address -->
          <div class="quote-modal-row">
            <span class="quote-modal-label">Address:</span>
            <span id="quote-modal-address" class="quote-modal-value address-value"></span>
          </div>

          <div class="quote-modal-row grouped-row">
            <div class="grouped-item">
              <span class="quote-modal-label">Price per pcs:</span>
              <span id="quote-modal-price" class="quote-modal-price-value"></span>
            </div>
            <div class="grouped-item">
              <span class="quote-modal-label">Subtotal:</span>
              <span id="quote-modal-subtotal" class="quote-modal-subtotal-value"></span>
            </div>
          </div>
          
          <!-- Subtotal -->
          <div class="quote-modal-row">
          <input type="number" id="subtotal-value" class="subtotal-value" name="subtotal" hidden>
          <input type="number" id="pricing-value" class="pricing-value" name="pricing" hidden>

          <input type="number" id="user_id" class="user_id" name="user_id" hidden>

          <input type="number" id="ticket-value-input" class="ticket-value-input" name="ticket-value-input" hidden>
          
            <span id="subtotal-text" class="subtotal-text">Updated: ₱</span>
          </div>
        </div>
        <div class="quote-modal-footer">
      <input type="number" id="quote-modal-input" placeholder="Update Price per pcs" name="price">
      <button id="quote-modal-cancel" class="quote-modal-btn btn-danger" style="margin-right:8px; background-color:#e53935; color:#fff; border:none;">Cancel</button>
      <button id="quote-modal-save" class="quote-modal-btn">Approved</button>
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
    // Modal Cancel button handler
    const cancelBtn = document.getElementById('quote-modal-cancel');
    cancelBtn.addEventListener('click', function() {
      const id = quoteModal.getAttribute('data-current-id');
      const userId = document.getElementById('user_id').value;
      const ticket = document.getElementById('ticket-value-input').value;
      const reason = prompt('Please enter the reason for cancellation:');
      if (reason === null) return;
      if (!reason.trim()) {
        showToast('Error', 'Cancellation reason is required.', 'error');
        return;
      }
      cancelBtn.disabled = true;
      cancelBtn.textContent = 'Cancelling...';
      const formData = new FormData();
      formData.append('id', id);
      formData.append('user_id', userId);
      formData.append('ticket', ticket);
      formData.append('action', 'cancel');
      formData.append('cancellation_reason', reason);
      fetch('functions/approved_pricing.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Success', data.message, 'success');
          quoteModal.style.display = 'none';
          refreshDesignersTable();
        } else {
          showToast('Error', data.message, 'error');
        }
      })
      .catch(error => {
        showToast('Error', 'An error occurred while cancelling the order', 'error');
        console.error('Error:', error);
      })
      .finally(() => {
        cancelBtn.disabled = false;
        cancelBtn.textContent = 'Cancel';
      });
    });
    // Get DOM elements
    const quoteModal = document.getElementById('quoteModal');
    const quoteModalClose = document.querySelector('.quote-modal-close');
    const priceInput = document.getElementById('quote-modal-input');
    const quantitySpan = document.getElementById('quote-modal-quantity');
    const subtotalInput = document.getElementById('subtotal-value');
    const subtotalText = document.getElementById('subtotal-text');
    const saveBtn = document.getElementById('quote-modal-save');

    // Price calculation handler
    function handlePriceCalculation() {
        const pricePerPiece = parseFloat(priceInput.value.trim());
        const quantity = parseInt(quantitySpan.textContent.trim());

        if (!isNaN(pricePerPiece) && pricePerPiece >= 0 && !isNaN(quantity) && quantity > 0) {
            const subtotal = pricePerPiece * quantity;
            subtotalInput.value = subtotal.toFixed(2);
            subtotalText.textContent = `Updated: ₱${subtotal.toFixed(2)}`;
        } else {
            subtotalInput.value = '';
            subtotalText.textContent = 'Updated: ₱0.00';
        }
    }

    function handleViewButtonClick() {
        const id = this.getAttribute('data-id');
        const userId = this.getAttribute('data-user-id');
        const ticket = this.getAttribute('data-ticket');
        const design = this.getAttribute('data-design');
        const mobile = this.getAttribute('data-mobile');
        const name = this.getAttribute('data-name');
        const printType = this.getAttribute('data-print-type');
        const quantity = this.getAttribute('data-quantity');
        const date = this.getAttribute('data-date');
        const status = this.getAttribute('data-status');
        const note = this.getAttribute('data-note');
        const address = this.getAttribute('data-address');
        const pricing = this.getAttribute('data-pricing');
        const subtotal = this.getAttribute('data-subtotal');
        const isViewable = this.getAttribute('data-viewable') === 'yes';
        const items = JSON.parse(this.getAttribute('data-items') || "[]"); // 👈 parse shirt items

        // Store data in modal
        quoteModal.setAttribute('data-current-id', id);
        quoteModal.setAttribute('data-design-file', design);
        quoteModal.setAttribute('data-is-viewable', isViewable);

        // Determine the correct image source for display
        let imageSrc;
        if (isViewable) {
            imageSrc = '../user/' + design;
        } else {
            const fileExtension = design.split('.').pop().toLowerCase();
            if (fileExtension === 'psd') {
                imageSrc = '../photoshop.png';
            } else if (fileExtension === 'pdf') {
                imageSrc = '../pdf.png';
            } else if (fileExtension === 'ai') {
                imageSrc = '../illustrator.png';
            } else {
                imageSrc = '../file.png';
            }
        }

        // Populate modal fields
        document.getElementById('quote-modal-ticket').textContent = ticket;
        document.getElementById('quote-modal-name').textContent = name;
        document.getElementById('quote-modal-design').src = imageSrc;
        document.getElementById('quote-modal-print-type').textContent = printType;
        document.getElementById('quote-modal-quantity').textContent = quantity;
        document.getElementById('quote-modal-date').textContent = date;
        document.getElementById('quote-modal-status').textContent = status;
        document.getElementById('quote-modal-note').textContent = note || 'N/A';
        document.getElementById('quote-modal-address').textContent = address || 'N/A';
        document.getElementById('quote-modal-mobile').textContent = mobile || 'N/A';
        document.getElementById('user_id').value = userId;
        document.getElementById('ticket-value-input').value = ticket;

        // Populate pricing and subtotal fields
        document.getElementById('quote-modal-price').textContent = pricing ? `₱${parseFloat(pricing).toFixed(2)}` : 'N/A';
        document.getElementById('quote-modal-subtotal').textContent = subtotal ? `₱${parseFloat(subtotal).toFixed(2)}` : 'N/A';

        document.getElementById('pricing-value').value = pricing ? parseFloat(pricing) : '';
        document.getElementById('subtotal-value').value = subtotal ? parseFloat(subtotal) : '';

        // Reset input fields for manual update
        priceInput.value = '';
        subtotalText.textContent = 'Updated: ₱0.00';

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
        quoteModal.style.display = 'block';
    }

    // Save quote handler
    function handleSaveQuote() {
        const quoteAmount = priceInput.value;
        const subtotalAmount = subtotalInput.value;
        const id = quoteModal.getAttribute('data-current-id');
        const userId = document.getElementById('user_id').value;
        const ticket = document.getElementById('ticket-value-input').value;
        const quantity = document.getElementById('quote-modal-quantity').textContent.trim();
        const pricingValue = document.getElementById('pricing-value').value;

        // Only validate quoteAmount if it's not empty (allow empty if pricingValue exists)
        if (quoteAmount && isNaN(quoteAmount)) {
            showToast('Error', 'Please enter a valid quote amount', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('id', id);
        formData.append('price', quoteAmount);
        formData.append('subtotal', subtotalAmount);
        formData.append('user_id', userId);
        formData.append('ticket', ticket);
        formData.append('quantity', quantity);
        formData.append('pricing', pricingValue);

        // Show loading state
        const originalText = saveBtn.textContent;
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        fetch('functions/approved_pricing.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                quoteModal.style.display = 'none';
                refreshDesignersTable();
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'An error occurred while updating pricing', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        });
    }

    // Modal close handlers
    function closeModal() {
        quoteModal.style.display = 'none';
    }

    function handleWindowClick(event) {
        if (event.target === quoteModal) {
            closeModal();
        }
    }

// Table refresh functionality with pagination
function refreshDesignersTable(page = null) {
    const currentPage = page || getCurrentPageFromURL();
    const params = new URLSearchParams(window.location.search);
    
    // Remove existing page parameter and set new one
    params.delete('page');
    if (currentPage > 1) {
        params.set('page', currentPage);
    }
    
    fetch('functions/get_admin_orders.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            // Update table body
            document.getElementById('admins-table-body').innerHTML = data.table;
            
            // Update pagination
            document.getElementById('paginationContainer').innerHTML = data.pagination;
            
            // Update the badge count
            document.querySelector('.table-title .badge').textContent = data.total_orders;
            
            attachEventListeners();
        })
        .catch(error => console.error('Error refreshing table:', error));
}

// Get current page from URL
function getCurrentPageFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return parseInt(urlParams.get('page')) || 1;
}

// Handle pagination clicks
function handlePaginationClicks() {
    document.addEventListener('click', function(e) {
        const paginationLink = e.target.closest('.pagination a');
        if (paginationLink) {
            e.preventDefault();
            const url = new URL(paginationLink.href);
            const page = url.searchParams.get('page') || 1;
            
            // Update URL without reloading the entire page
            window.history.pushState({}, '', url.toString());
            
            refreshDesignersTable(page);
        }
    });
}

// Update your init function
function init() {
    attachEventListeners();
    handlePaginationClicks();
    refreshDesignersTable();
    
    // Set up periodic refresh (every 5 seconds)
    setInterval(refreshDesignersTable, 5000);
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        refreshDesignersTable();
    });
}
    // Attach all event listeners
    function attachEventListeners() {
        // Price calculation
        priceInput.addEventListener('input', handlePriceCalculation);
        
        // View buttons
        document.querySelectorAll('.view-quote-modal').forEach(button => {
            button.addEventListener('click', handleViewButtonClick);
        });
        
        // Modal close
        quoteModalClose.addEventListener('click', closeModal);
        window.addEventListener('click', handleWindowClick);
        
        // Save button
        saveBtn.addEventListener('click', handleSaveQuote);
    }



    // Start the application
    document.addEventListener('DOMContentLoaded', init);

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

    // Image Viewer Modal
    const imageViewerModal = document.createElement('div');
    imageViewerModal.className = 'image-viewer-modal';
    imageViewerModal.innerHTML = `
      <span class="close-viewer">&times;</span>
      <img class="image-viewer-content" id="viewed-image">
    `;
    document.body.appendChild(imageViewerModal);

    // View button functionality
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('view-design-btn')) {
        // Get the actual design file path, not the placeholder
        const designFile = quoteModal.getAttribute('data-design-file');
        const isViewable = quoteModal.getAttribute('data-is-viewable') === 'true';
        
        // Only show the image if it's viewable
        if (isViewable) {
            document.getElementById('viewed-image').src = '../user/' + designFile;
            imageViewerModal.style.display = 'block';
        } else {
            showToast('Info', 'This file type cannot be previewed. Please download the file to view it.', 'info');
        }
      }
    });

    // Close button functionality
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('close-viewer')) {
        imageViewerModal.style.display = 'none';
      }
    });

    // Close viewer when clicking outside the image
    imageViewerModal.addEventListener('click', function (e) {
      if (e.target === imageViewerModal) {
        imageViewerModal.style.display = 'none';
      }
    });

    // Download button functionality
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('download-design-btn')) {
        // Get the actual design file from the modal attribute
        const designFile = quoteModal.getAttribute('data-design-file');
        const ticket = document.getElementById('quote-modal-ticket').textContent;
        const printType = document.getElementById('quote-modal-print-type').textContent;
        
        // Create the actual file path for download
        const filePath = '../user/' + designFile;
        
        // Extract filename from the actual design file
        const filename = designFile.split('/').pop();
        const extension = filename.split('.').pop();
        
        // Create download link for the actual file
        const link = document.createElement('a');
        link.href = filePath;
        link.download = `${ticket}-${printType.toLowerCase().replace(/ /g, '-')}.${extension}`;
        link.target = '_blank'; // Open in new tab for better UX
        
        // Add link to document, click it, then remove it
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showToast('Download', 'Download started', 'success');
      }
    });

    // Close image viewer
    document.querySelector('.close-viewer').onclick = function() {
      imageViewerModal.style.display = 'none';
    }

    // Close when clicking outside image
    imageViewerModal.onclick = function(e) {
      if (e.target === imageViewerModal) {
        imageViewerModal.style.display = 'none';
      }
    }
    </script>

    <script src="assets/js/orders.js"></script>
    <?php include "includes/script-src.php";?>

</body>
</html>
