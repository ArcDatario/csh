<?php 
require '../auth_check.php';
redirectIfNotLoggedIn();

require '../db_connection.php';

// Fetch user ID from session
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Query the database for the user's address only
    $stmt = $conn->prepare("SELECT address FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $address = $user['address'] ?? '';
    } else {
        // If no user is found, set default empty value
        $address = '';
    }
} else {
    // If no user ID is found in the session, redirect to login
    header("Location: ../login");
    exit();
}

            // Fetch completed orders for the logged-in user with pagination
            $user_id = $_SESSION['user_id'] ?? null;
            $has_orders = false;
            $orders = [];
            
// Use address directly
$full_address = trim($address);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Enterprises | Completed Orders</title>
    <link rel="icon" href="../assets/images/icons/shirt.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/quote.css">
    <link rel="stylesheet" href="../assets/css/order-process-modal.css">
    <link rel="stylesheet" href="../assets/css/profile-modal.css">

</head>
<body>
    <!-- Loader -->
    <div class="loader-container" id="loader" style="margin-right:10% !important;">
        <div class="loader" style="margin-right:10% !important;">
            <i class="fas fa-tshirt t-shirt"></i>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="#" class="logo">
                 <img src="../csh-logo.png" alt="" style="height: 55px; width: 100%;">
            </a>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
             <?php include "includes/navbar.php";?>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
           <div class="orders-tabs">
    <a href="quote" class="tab-button " >Pending</a>
    <a href="approved-order" class="tab-button  " >Approved</a>
    <a href="to-pickup-order" class="tab-button" >To Pick Up</a>
    <a href="processing-order" class="tab-button ">Processing</a>
    <a href="to-ship-order" class="tab-button ">To Ship</a>
    <a href="completed-order" class="tab-button active">Completed</a>
    <a href="cancelled-orders" class="tab-button">Cancelled</a>
</div>

<!-- Desktop Sidebar (hidden on mobile) -->
<div class="orders-sidebar">
    <a href="quote" class="sidebar-item">
        <i class="fas fa-clock"></i> Pending
    </a>
    <a href="approved-order" class="sidebar-item">
        <i class="fas fa-check-circle"></i> Approved
    </a>
    <a href="to-pickup-order" class="sidebar-item">
        <i class="fas fa-box"></i> To Pick Up
    </a>
    <a href="processing-order" class="sidebar-item">
        <i class="fas fa-cog"></i> Processing
    </a>
    <a href="to-ship-order" class="sidebar-item">
        <i class="fas fa-shipping-fast"></i> To Ship
    </a>
    <a href="completed-order" class="sidebar-item active">
        <i class="fas fa-flag-checkered"></i> Completed
    </a>
    <a href="cancelled-orders" class="sidebar-item">
        <i class="fas fa-times-circle"></i> Cancelled
    </a>
</div>

<div class="search-wrapper">
    <div class="search-container">
        <!-- Completed Orders Search -->
        <div class="completed-search" style="display: block;">
            <input type="text"
                   id="CompletedSearchInput"
                   class="search-input"
                   placeholder="Search by Ticket #">
            <span class="search-icon">&#128269;</span>
            <button type="button" id="clearCompletedSearch" class="clear-search-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<div class="quotes-container completed-orders-container" id="completed-orders-container" style="display:block;">
<?php
if ($user_id) {
    // Pagination setup
    $limit = 8; // orders per page
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // Count total completed orders
    $count_sql = "SELECT COUNT(*) AS total FROM orders WHERE user_id = ? AND status = 'completed'";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $total_orders = $count_stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_orders / $limit);
    $count_stmt->close();

    // Fetch paginated orders
    $sql = "SELECT orders.*, users.name, users.phone_number 
            FROM orders 
            INNER JOIN users ON orders.user_id = users.id 
            WHERE orders.user_id = ? AND orders.status = 'completed' 
            ORDER BY orders.created_at DESC 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo '<div class="no-orders">No orders found</div>';
    } else {
        while ($order = $result->fetch_assoc()) {
            // fetch items
            $items_sql = "SELECT shirt_color, quantity FROM items WHERE order_id = ?";
            $items_stmt = $conn->prepare($items_sql);
            $items_stmt->bind_param("i", $order['id']);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();

            $shirtItems = [];
            while ($item = $items_result->fetch_assoc()) {
                $shirtItems[] = $item;
            }
            $items_stmt->close();

            $createdAt = date('M d, Y', strtotime($order['created_at']));
            $designFile = $order['design_file'];
            $ext = strtolower(pathinfo($designFile, PATHINFO_EXTENSION));
            $thumbnail = ($ext === 'psd') ? "../photoshop.png" : (($ext === 'pdf') ? "../pdf.png" : (($ext === 'ai') ? "../illustrator.png" : htmlspecialchars($designFile)));
            ?>
            <div class="quote-card animate__animated animate__fadeInUp">
                <img src="<?= $thumbnail ?>" alt="Design" class="card-image">
                <span class="card-status status-approved">Completed</span>
                <div class="card-content">
                    <h3 class="card-title"><?= htmlspecialchars($order['print_type']) ?></h3>
                    <div class="card-details">
                        <div class="card-detail"><span class="detail-label">Quantity</span><span class="detail-value"><?= htmlspecialchars($order['quantity']) ?></span></div>
                        <div class="card-detail"><span class="detail-label">Ticket #</span><span class="detail-value"><?= htmlspecialchars($order['ticket']) ?></span></div>
                    </div>
                    <span class="quote-date"><?= $createdAt ?></span>
                    <i class="fa fa-info-circle bottom-right-details-icon"
                      title="Click to see full order details"
                      onclick="openDetailsModalFromCard(this)"
                      data-id="<?= $order['id'] ?>"
                      data-user-id="<?= $order['user_id'] ?>"
                      data-ticket="<?= htmlspecialchars($order['ticket'], ENT_QUOTES) ?>"
                      data-design="<?= htmlspecialchars($order['design_file'], ENT_QUOTES) ?>"
                      data-mobile="<?= htmlspecialchars($order['phone_number'], ENT_QUOTES) ?>"
                      data-name="<?= htmlspecialchars($order['name'], ENT_QUOTES) ?>"
                      data-print-type="<?= htmlspecialchars($order['print_type'], ENT_QUOTES) ?>"
                      data-quantity="<?= htmlspecialchars($order['quantity'], ENT_QUOTES) ?>"
                      data-date="<?= htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES) ?>"
                      data-status="<?= htmlspecialchars($order['status'], ENT_QUOTES) ?>"
                      data-note="<?= htmlspecialchars($order['note'], ENT_QUOTES) ?>"
                      data-address="<?= htmlspecialchars($order['address'], ENT_QUOTES) ?>"
                      data-pricing="<?= htmlspecialchars($order['pricing'], ENT_QUOTES) ?>"
                      data-subtotal="<?= htmlspecialchars($order['subtotal'], ENT_QUOTES) ?>"
                      data-items='<?= json_encode($shirtItems, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                    </i>
                    <div class="card-actions">
                        <div class="button-group">
                            <button class="view-details-btn completed-order-btn" 
                                data-order-id="<?= htmlspecialchars($order['id']) ?>" 
                                data-order-ticket="<?= htmlspecialchars($order['ticket']) ?>" 
                                data-order-created-at="<?= htmlspecialchars($order['created_at']) ?>"
                                data-order-pricing="<?= htmlspecialchars($order['pricing']) ?>"
                                data-order-quantity="<?= htmlspecialchars($order['quantity']) ?>"
                                data-order-subtotal="<?= htmlspecialchars($order['pricing'] * $order['quantity']) ?>"
                                data-admin-approved-date="<?= htmlspecialchars($order['admin_approved_date']) ?>"
                                data-user-approved-date="<?= htmlspecialchars($order['user_approved_date']) ?>"
                                data-processing-date="<?= htmlspecialchars($order['processing_date']) ?>"
                                data-to-ship-date="<?= htmlspecialchars($order['shipping_date']) ?>"
                                data-completed-date="<?= htmlspecialchars($order['completion_date']) ?>"
                                data-is-for-pickup="<?= htmlspecialchars($order['is_for_pickup']) ?>"
                                data-pickup-date="<?= htmlspecialchars($order['pickup_date']) ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="<?= htmlspecialchars($order['design_file']) ?>" class="download-btn" download>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php }
    }
    $stmt->close();

    // ✅ Pagination Controls
    if ($total_pages > 0) {
        echo '<div class="pagination">';
        
        // Previous button
        if ($page > 1) {
            echo '<a href="?page='.($page-1).'" class="page-btn prev-next">‹ Prev</a>';
        } else {
            echo '<span class="page-btn prev-next disabled">‹ Prev</span>';
        }
        
        // First page
        if ($page > 3) {
            echo '<a href="?page=1" class="page-btn">1</a>';
            if ($page > 4) {
                echo '<span class="page-dots">...</span>';
            }
        }
        
        // Page numbers around current page
        $startPage = max(2, $page - 1);
        $endPage = min($total_pages - 1, $page + 1);
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            $active = ($i == $page) ? 'active' : '';
            echo '<a href="?page='.$i.'" class="page-btn '.$active.'">'.$i.'</a>';
        }
        
        // Last page
        if ($page < $total_pages - 2) {
            if ($page < $total_pages - 3) {
                echo '<span class="page-dots">...</span>';
            }
            echo '<a href="?page='.$total_pages.'" class="page-btn">'.$total_pages.'</a>';
        }
        
        // Next button
        if ($page < $total_pages) {
            echo '<a href="?page='.($page+1).'" class="page-btn prev-next">Next ›</a>';
        } else {
            echo '<span class="page-btn prev-next disabled">Next ›</span>';
        }
        
        echo '</div>';
    }
} else {
    echo '<div class="no-orders">No user ID found. Please log in.</div>';
}
?>
</div>
    </main>

    <div id="completedProcessModal" class="order-process-modal">
        <div class="order-process-modal-content">
            <span class="order-process-close-btn" onclick="closeCompletedProcessModal()">&times;</span>
            <h2 id="completedProcessTitle" class="order-process-title">Ticket #12345 Process Details</h2>
            
            <div id="completedProcessSteps" class="order-process-steps-container">
                <!-- Quote Placed Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">1</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="completedQuotePlacedTitle" class="order-step-title">Quote Placed</div>
                        <div id="completedQuotePlacedDesc" class="order-step-description">Your order request has been received</div>
                        <div id="completedQuotePlacedDate" class="order-step-date">Jan 15, 2023</div>
                    </div>
                </div>
                
                <!-- Agreed Price Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">2</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="completedAdminApprovedTitle" class="order-step-title">Agreed Price</div>
                        <div class="order-step-description">
                            <div id="completedOrderSummary" class="order-summary-details">
                                <p id="completedUnitPrice">Unit Price: $10.00</p>
                                <p id="completedQuantity">Quantity: 5</p>
                                <p id="completedSubtotal" class="order-subtotal">Subtotal: $50.00</p>
                            </div>
                        </div>
                        <div id="completedAdminApprovedDate" class="order-step-date">Jan 16, 2023</div>
                    </div>
                </div>
                
                <!-- Processing Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">3</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="completedProcessingTitle" class="order-step-title">Processing</div>
                        <div id="completedProcessingDesc" class="order-step-description">Your items were being prepared</div>
                        <div id="completedProcessingDate" class="order-step-date">Jan 17, 2023</div>
                    </div>
                </div>
                
                <!-- To Ship Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">4</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="completedToShipTitle" class="order-step-title">To Ship</div>
                        <div id="completedToShipDesc" class="order-step-description">Your items were ready to be shipped</div>
                        <div id="completedToShipDate" class="order-step-date">Jan 18, 2023</div>
                    </div>
                </div>
                
                <!-- Completed Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">5</div>
                    <div class="order-step-connector-current"></div>
                    <div class="order-step-content">
                        <div id="completedCompletedTitle" class="order-step-title">Completed</div>
                        <div id="completedCompletedDesc" class="order-step-description">Order has been successfully delivered</div>
                        <div id="completedCompletedDate" class="order-step-date">Jan 19, 2023</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- ✅ Details Modal -->
<div id="detailsModal" class="details-modal">
  <div class="detail-modal-content">
    <span class="detail-modal-close">&times;</span>

    <!-- Header: Ticket & Date -->
    <div class="detail-modal-header">
      <h2>Ticket #<span id="detail-modal-ticket"></span></h2>
      <span id="detail-modal-date" class="detail-modal-value"></span>
      <span class="status-label">Status: <span id="detail-modal-status"></span></span>
    </div>

    <hr>

    <!-- Design & Print Info -->
    <div class="detail-modal-section design-info">
      <div class="design-image-container">
        <img id="detail-modal-design" src="" alt="Design" class="design-image">
        <div class="design-buttons">
          <button class="view-design-btn">View</button>
          <button class="download-design-btn">Download</button>
        </div>
      </div>
      <div class="design-details">
        <div><strong>Print Type:</strong> <span id="detail-modal-print-type"></span></div>
        <div><strong>Total Quantity:</strong> <span id="detail-modal-quantity"></span></div>
      </div>
    </div>

    <hr>

    <!-- Items Table -->
    <div class="detail-modal-section items-section">
      <table class="items-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Qty</th>
          </tr>
        </thead>
        <tbody id="detail-modal-shirt-items">
          <!-- Shirt items will populate here -->
        </tbody>
      </table>
    </div>

    <hr>

    <!-- Price & Subtotal Section -->
    <div class="detail-modal-section price-section">
      <div class="price-detail">
        <span class="detail-modal-label">Price per pcs:</span>
        <span id="detail-modal-price" class="detail-modal-price-value"></span>
      </div>
      <div class="price-detail">
        <span class="detail-modal-label">Subtotal:</span>
        <span id="detail-modal-subtotal" class="detail-modal-subtotal-value"></span>
      </div>
    </div>

    <!-- Hidden fields for JS (kept as is) -->
    <input type="hidden" id="subtotal-value" name="subtotal">
    <input type="hidden" id="pricing-value" name="pricing">
    <input type="hidden" id="user_id" name="user_id">
    <input type="hidden" id="ticket-value-input" name="ticket-value-input">
  </div>
</div>

<!-- Image Viewer Modal -->
<div id="userImageViewerModal" class="image-viewer-modal" style="display:none;">
  <span class="close-viewer">&times;</span>
  <img class="image-viewer-content" id="userExpandedDesignImage" alt="Design Preview">
  <div id="viewerLoading" class="viewer-loading" style="display:none;">Loading...</div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/quote.js"></script>

    <script>
    function openDetailsModalFromCard(element) {
    // If the element itself is the icon, use it
    const button = element.tagName === "I" ? element : element.querySelector('i.details-icon');
    
    if (button) {
        openDetailsModal({ currentTarget: button });
    }
}
      // Grab the modal
const detailsModal = document.getElementById('detailsModal');

// Open Details Modal
function openDetailsModal(event) {
    const button = event.currentTarget;
    const id = button.getAttribute("data-id");
    const userId = button.getAttribute("data-user-id");
    const ticket = button.getAttribute("data-ticket");
    const design = button.getAttribute("data-design");
    const printType = button.getAttribute("data-print-type");
    const quantity = button.getAttribute("data-quantity");
    const date = button.getAttribute("data-date");
    const status = button.getAttribute("data-status");
    const pricing = button.getAttribute("data-pricing");
    const subtotal = button.getAttribute("data-subtotal");
    const items = JSON.parse(button.getAttribute("data-items") || "[]");

    // Determine correct design image
    const fileExtension = design.split('.').pop().toLowerCase();
    let imageSrc = ['psd','pdf','ai'].includes(fileExtension)
                    ? fileExtension === 'psd' ? '../photoshop.png'
                      : fileExtension === 'pdf' ? '../pdf.png'
                      : '../illustrator.png'
                    : '../user/' + design;

    // Populate modal fields
    document.getElementById("detail-modal-ticket").textContent = ticket;
    document.getElementById("detail-modal-design").src = imageSrc;
    document.getElementById("detail-modal-print-type").textContent = printType;
    document.getElementById("detail-modal-quantity").textContent = quantity;
    document.getElementById("detail-modal-date").textContent = date;
    document.getElementById("detail-modal-status").textContent = status;
    document.getElementById("user_id").value = userId;
    document.getElementById("ticket-value-input").value = ticket;

    document.getElementById("detail-modal-price").textContent = `₱${parseFloat(pricing).toFixed(2)}`;
    document.getElementById("detail-modal-subtotal").textContent = `₱${parseFloat(subtotal).toFixed(2)}`;

    // Populate shirt items (for table tbody)
    const itemsContainer = document.getElementById("detail-modal-shirt-items");
    itemsContainer.innerHTML = "";
    if (items.length > 0) {
        items.forEach(item => {
            const tr = document.createElement("tr");
            tr.innerHTML = `<td>${item.shirt_color}</td><td>${item.quantity}</td>`;
            itemsContainer.appendChild(tr);
        });
    } else {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td colspan="2"><em>No shirt colors added</em></td>`;
        itemsContainer.appendChild(tr);
    }

    // Show the modal
    detailsModal.style.display = "flex";
    detailsModal.setAttribute('data-design-file', design);
    detailsModal.setAttribute(
        'data-is-viewable',
        ['jpg','jpeg','png','gif','webp'].includes(fileExtension)
    );
}


// Close Details Modal
function closeDetailsModal() {
    detailsModal.style.display = "none";
}

// Attach open/close events
document.querySelectorAll(".details-btn").forEach(btn => {
    btn.addEventListener("click", openDetailsModal);
});
detailsModal.querySelector(".detail-modal-close").addEventListener("click", closeDetailsModal);
detailsModal.addEventListener("click", e => {
    if (e.target === detailsModal) closeDetailsModal();
});


function openCompletedOrderModal(event) {
    // Handle both event objects and direct element calls
    const button = event.currentTarget || event;
    
    if (!button || !button.getAttribute) {
        console.error('Invalid button element:', button);
        return;
    }
    
    const ticket = button.getAttribute('data-order-ticket');
    const createdAt = button.getAttribute('data-order-created-at');
    const pricing = button.getAttribute('data-order-pricing');
    const quantity = button.getAttribute('data-order-quantity');
    const subtotal = button.getAttribute('data-order-subtotal');
    const adminApprovedDate = button.getAttribute('data-admin-approved-date');
    const userApprovedDate = button.getAttribute('data-user-approved-date');
    const processingDate = button.getAttribute('data-processing-date');
    const toShipDate = button.getAttribute('data-to-ship-date');
    const completedDate = button.getAttribute('data-completed-date');
    const isForPickup = button.getAttribute('data-is-for-pickup');
    const pickupDate = button.getAttribute('data-pickup-date');

    // Format the dates
    const formatDate = (dateString) => {
        if (!dateString || dateString === 'null' || dateString === '') return 'Pending';
        const date = new Date(dateString);
        return isNaN(date) ? 'Pending' : date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    };

    document.getElementById('completedProcessTitle').textContent = `Ticket #${ticket} Process Details`;
    document.getElementById('completedQuotePlacedDate').textContent = formatDate(createdAt);
    document.getElementById('completedUnitPrice').textContent = `Unit Price: ₱${parseFloat(pricing).toFixed(2)}`;
    document.getElementById('completedQuantity').textContent = `Quantity: ${quantity}`;
    document.getElementById('completedSubtotal').textContent = `Subtotal: ₱${parseFloat(subtotal).toFixed(2)}`;
    document.getElementById('completedAdminApprovedDate').textContent = formatDate(userApprovedDate);
    document.getElementById('completedProcessingDate').textContent = formatDate(processingDate);
    document.getElementById('completedToShipDate').textContent = formatDate(toShipDate);
    document.getElementById('completedCompletedDate').textContent = formatDate(completedDate);

    document.getElementById('completedProcessModal').setAttribute('data-ticket', ticket);
    document.getElementById('completedProcessModal').style.display = 'flex';
}

function closeCompletedProcessModal() {
    document.getElementById('completedProcessModal').style.display = 'none';
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to view buttons
    const viewButtons = document.querySelectorAll('.completed-order-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', openCompletedOrderModal);
    });

    // Close modal when clicking outside
    document.getElementById('completedProcessModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeCompletedProcessModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCompletedProcessModal();
        }
    });
});

// Completed Orders Search Functionality (AJAX-based)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('CompletedSearchInput');
    const clearSearchBtn = document.getElementById('clearCompletedSearch');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            if (clearSearchBtn) {
                clearSearchBtn.style.display = searchTerm ? 'block' : 'none';
            }
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Set new timeout to avoid too many requests
            searchTimeout = setTimeout(() => {
                searchCompletedOrders(searchTerm, 1);
            }, 500); // 500ms delay
        });
    }
    
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            searchCompletedOrders('', 1);
        });
    }
});

function searchCompletedOrders(searchTerm = '', page = 1) {
    const container = document.getElementById('completed-orders-container');
    
    if (!container) {
        console.error('Completed orders container not found');
        return;
    }
    
    // Show loading state
    container.innerHTML = '<div class="no-orders">Searching...</div>';
    
    // Build URL with parameters
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    params.append('page', page);
    
    const searchUrl = 'functions/search_completed_orders.php?' + params.toString();
    
    console.log('Searching completed orders with URL:', searchUrl);
    
    fetch(searchUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Completed orders search response:', data);
            
            if (data.error) {
                container.innerHTML = `<div class="no-orders">Error: ${data.error}</div>`;
                return;
            }
            
            if (data.success === false) {
                container.innerHTML = `<div class="no-orders">${data.error || 'Search failed'}</div>`;
                return;
            }
            
            displayCompletedSearchResults(data, searchTerm, page);
        })
        .catch(error => {
            console.error('Completed orders search error:', error);
            container.innerHTML = '<div class="no-orders">Search failed: ' + error.message + '</div>';
        });
}

function displayCompletedSearchResults(data, searchTerm, currentPage) {
    const container = document.getElementById('completed-orders-container');
    const orders = data.orders || [];
    const totalPages = data.total_pages || 1;
    
    if (orders.length === 0) {
        const message = searchTerm 
            ? `No completed orders found for ticket "${searchTerm}"`
            : 'No completed orders found';
        container.innerHTML = `<div class="no-orders">${message}</div>`;
        return;
    }
    
    let html = '';
    
    orders.forEach(order => {
        // Use the thumbnail path from the server response
        const thumbnail = order.thumbnail || order.design_file;
        const statusClass = order.status === 'completed' ? 'status-completed' : 
                           order.status === 'approved' ? 'status-approved' : 'status-pending';
        const statusText = order.status === 'completed' ? 'Completed' : 
                          order.status === 'approved' ? 'Approved' : 'Pending';
        
        html += `
            <div class="quote-card animate__animated animate__fadeInUp">
                <img src="${thumbnail}" alt="Design" class="card-image" onerror="this.src='../image-placeholder.png'">
                <span class="card-status ${statusClass}">${statusText}</span>
                <div class="card-content">
                    <h3 class="card-title">${escapeHtml(order.print_type)}</h3>
                    <div class="card-details">
                        <div class="card-detail"><span class="detail-label">Quantity</span><span class="detail-value">${escapeHtml(order.quantity)}</span></div>
                        <div class="card-detail"><span class="detail-label">Ticket #</span><span class="detail-value">${escapeHtml(order.ticket)}</span></div>
                    </div>
                    <span class="quote-date">${order.completed_at_formatted || order.created_at_formatted}</span>
                    <i class="fa fa-info-circle bottom-right-details-icon"
                        title="Click to see full order details"
                        onclick="openDetailsModalFromCard(this)"
                        data-id="${order.id}"
                        data-user-id="${order.user_id}"
                        data-ticket="${escapeHtml(order.ticket)}"
                        data-design="${escapeHtml(order.design_file)}"
                        data-mobile="${escapeHtml(order.phone_number)}"
                        data-name="${escapeHtml(order.name)}"
                        data-print-type="${escapeHtml(order.print_type)}"
                        data-quantity="${escapeHtml(order.quantity)}"
                        data-date="${escapeHtml(order.created_at_formatted)}"
                        data-completed-date="${escapeHtml(order.completed_at_formatted || '')}"
                        data-status="${escapeHtml(order.status)}"
                        data-note="${escapeHtml(order.note || '')}"
                        data-address="${escapeHtml(order.address)}"
                        data-pricing="${escapeHtml(order.pricing)}"
                        data-subtotal="${escapeHtml(order.subtotal)}"
                        data-items='${JSON.stringify(order.items).replace(/'/g, "&#39;")}'>
                    </i>
                    <div class="card-actions">
                        <div class="button-group">
                            <button class="view-details-btn completed-order-btn" 
                                data-order-id="${order.id}" 
                                data-order-ticket="${escapeHtml(order.ticket)}" 
                                data-order-created-at="${escapeHtml(order.created_at)}"
                                data-order-pricing="${escapeHtml(order.pricing)}"
                                data-order-quantity="${escapeHtml(order.quantity)}"
                                data-order-subtotal="${escapeHtml(order.subtotal)}"
                                data-admin-approved-date="${escapeHtml(order.admin_approved_date || '')}"
                                data-user-approved-date="${escapeHtml(order.user_approved_date || '')}"
                                data-processing-date="${escapeHtml(order.processing_date || '')}"
                                data-to-ship-date="${escapeHtml(order.shipping_date || '')}"
                                data-completed-date="${escapeHtml(order.completion_date || '')}"
                                data-is-for-pickup="${escapeHtml(order.is_for_pickup || '')}"
                                data-pickup-date="${escapeHtml(order.pickup_date || '')}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="${escapeHtml(order.design_file)}" class="download-btn" download>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    if (totalPages > 0) {
        html += '<div class="pagination" id="completed-search-pagination">';
        
        // Previous button
        if (currentPage > 1) {
            html += `<a href="javascript:void(0)" class="page-btn prev-next" onclick="searchCompletedOrders('${escapeHtml(searchTerm)}', ${currentPage - 1})">‹ Prev</a>`;
        } else {
            html += `<span class="page-btn prev-next disabled">‹ Prev</span>`;
        }
        
        // First page
        if (currentPage > 3) {
            html += `<a href="javascript:void(0)" class="page-btn" onclick="searchCompletedOrders('${escapeHtml(searchTerm)}', 1)">1</a>`;
            if (currentPage > 4) {
                html += `<span class="page-dots">...</span>`;
            }
        }
        
        // Page numbers around current page
        const startPage = Math.max(2, currentPage - 1);
        const endPage = Math.min(totalPages - 1, currentPage + 1);
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                html += `<span class="page-btn active">${i}</span>`;
            } else {
                html += `<a href="javascript:void(0)" class="page-btn" onclick="searchCompletedOrders('${escapeHtml(searchTerm)}', ${i})">${i}</a>`;
            }
        }
        
        // Last page
        if (currentPage < totalPages - 2) {
            if (currentPage < totalPages - 3) {
                html += `<span class="page-dots">...</span>`;
            }
            html += `<a href="javascript:void(0)" class="page-btn" onclick="searchCompletedOrders('${escapeHtml(searchTerm)}', ${totalPages})">${totalPages}</a>`;
        }
        
        // Next button
        if (currentPage < totalPages) {
            html += `<a href="javascript:void(0)" class="page-btn prev-next" onclick="searchCompletedOrders('${escapeHtml(searchTerm)}', ${currentPage + 1})">Next ›</a>`;
        } else {
            html += `<span class="page-btn prev-next disabled">Next ›</span>`;
        }
        
        html += '</div>';
    }
    
    container.innerHTML = html;
    
    // ✅ FIX: Re-attach event listeners to the new buttons PROPERLY
    document.querySelectorAll('.view-details-btn.completed-order-btn').forEach(button => {
        button.addEventListener('click', openCompletedOrderModal);
    });
    
    // ✅ FIX: Also re-attach the details modal icon click events
    document.querySelectorAll('.bottom-right-details-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            openDetailsModalFromCard(this);
        });
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Clear completed search functionality
function clearCompletedSearch() {
    const searchInput = document.getElementById('CompletedSearchInput');
    const clearSearchBtn = document.getElementById('clearCompletedSearch');
    
    if (searchInput) {
        searchInput.value = '';
        if (clearSearchBtn) {
            clearSearchBtn.style.display = 'none';
        }
        // Reload the original page content
        window.location.href = 'completed-order?page=1';
    }
}
        const userImageViewerModal = document.getElementById('userImageViewerModal');
const userExpandedImage = document.getElementById('userExpandedDesignImage');
const userDetailsodal = document.getElementById('detailsModal'); // Your details modal

// View button
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('view-design-btn')) {
    const designFile = userDetailsodal.getAttribute('data-design-file');
    const isViewable = userDetailsodal.getAttribute('data-is-viewable') === 'true';

    if (isViewable && designFile) {
      userExpandedImage.src = '../user/' + designFile;
      userImageViewerModal.style.display = 'flex';
    } else {
      alert('This file cannot be previewed. Please download the file.');
    }
  }
});

// Download button
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('download-design-btn')) {
    const designFile = userDetailsodal.getAttribute('data-design-file');
    if (!designFile) return;

    const ticket = document.getElementById('detail-modal-ticket').textContent;
    const printType = document.getElementById('detail-modal-print-type').textContent;
    const filePath = '../user/' + designFile;
    const filename = designFile.split('/').pop();
    const extension = filename.split('.').pop();

    const link = document.createElement('a');
    link.href = filePath;
    link.download = `${ticket}-${printType.toLowerCase().replace(/ /g,'-')}.${extension}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
});

// Close viewer
document.querySelector('#userImageViewerModal .close-viewer').onclick = function() {
  userImageViewerModal.style.display = 'none';
}

// Close when clicking outside image
userImageViewerModal.onclick = function(e) {
  if (e.target === userImageViewerModal) {
    userImageViewerModal.style.display = 'none';
  }
}
    </script>
</body>
</html>