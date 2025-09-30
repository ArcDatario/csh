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

// Use address directly
$full_address = trim($address);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Enterprises | To Ship Orders</title>
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
    <a href="processing-order" class="tab-button">Processing</a>
    <a href="to-ship-order" class="tab-button active">To Ship</a>
    <a href="completed-order" class="tab-button">Completed</a>
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
    <a href="to-ship-order" class="sidebar-item active">
        <i class="fas fa-shipping-fast"></i> To Ship
    </a>
    <a href="completed-order" class="sidebar-item">
        <i class="fas fa-flag-checkered"></i> Completed
    </a>
    <a href="cancelled-orders" class="sidebar-item">
        <i class="fas fa-times-circle"></i> Cancelled
    </a>
</div>

        <div class="search-wrapper">
            <div class="search-container">
                <!-- Print Type Filter -->
                <div class="print-type-filter">
                    <select id="toShipPrintTypeFilter" class="form-control">
                        <option value="">All Print Types</option>
                        <option value="Direct to Film Print">Direct to Film Print</option>
                        <option value="Screen Printing">Screen Printing</option>
                        <option value="Emboss Print">Emboss Print</option>
                        <option value="Hi-Density Print">Hi-Density Print</option>
                        <option value="Glitters Print">Glitters Print</option>
                        <option value="Silk Screen Print">Silk Screen Print</option>
                    </select>
                </div>
                <!-- Search Input -->
                <div class="ship-search" style="display: block;">
                    <input type="text"
                           id="ToShipSearchInput"
                           class="search-input"
                           placeholder="Search by Ticket #">
                    
                </div>
            </div>
        </div>

        <div class="quotes-container ship-orders-container" id="ship-orders-container" style="display:block;">
            <?php
            include '../db_connection.php';

            $user_id = $_SESSION['user_id'] ?? null;

            if ($user_id) {
                // Pagination setup
                $limit = 8; // orders per page
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $offset = ($page - 1) * $limit;

                // Count total to_ship orders
                $count_sql = "SELECT COUNT(*) AS total FROM orders WHERE user_id = ? AND status = 'to_ship'";
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
                        WHERE orders.user_id = ? AND orders.status = 'to_ship' 
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
                        <div class="quote-card animate__animated animate__fadeInUp" data-ticket="<?= htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') ?>">
                            <img src="<?= $thumbnail ?>" alt="Design" class="card-image">
                            <span class="card-status status-approved"><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></span>
                            <div class="card-content">
                                <h3 class="card-title"><?= htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <div class="card-details">
                                    <div class="card-detail"><span class="detail-label">Quantity</span><span class="detail-value"><?= htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div class="card-detail"><span class="detail-label">Ticket #</span><span class="detail-value"><?= htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') ?></span></div>
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
                                        <button class="view-details-btn to-ship-order-btn" 
                                            data-order-id="<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                            data-order-ticket="<?= htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') ?>" 
                                            data-order-created-at="<?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-order-pricing="<?= htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-order-quantity="<?= htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-order-subtotal="<?= htmlspecialchars($order['pricing'] * $order['quantity'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-admin-approved-date="<?= htmlspecialchars($order['admin_approved_date'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-user-approved-date="<?= htmlspecialchars($order['user_approved_date'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-processing-date="<?= htmlspecialchars($order['processing_date'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-to-ship-date="<?= htmlspecialchars($order['shipping_date'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-is-for-pickup="<?= htmlspecialchars($order['is_for_pickup'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-pickup-date="<?= htmlspecialchars($order['pickup_date'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="<?= htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8') ?>" class="download-btn" download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php }
                }
                $stmt->close();

                // Pagination Controls
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
        <!-- Footer inside main content -->
<?php include 'footer.php'; ?>
    </main>

    <div id="toShipProcessModal" class="order-process-modal">
        <div class="order-process-modal-content">
            <span class="order-process-close-btn" onclick="closeToShipProcessModal()">&times;</span>
            <h2 id="toShipProcessTitle" class="order-process-title">Ticket #12345 Process Details</h2>
            
            <div id="toShipProcessSteps" class="order-process-steps-container">
                <!-- Quote Placed Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">1</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="toShipQuotePlacedTitle" class="order-step-title">Quote Placed</div>
                        <div id="toShipQuotePlacedDesc" class="order-step-description">Your order request has been received</div>
                        <div id="toShipQuotePlacedDate" class="order-step-date">Jan 15, 2023</div>
                    </div>
                </div>
                
                <!-- Agreed Price Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">2</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="toShipAdminApprovedTitle" class="order-step-title">Agreed Price</div>
                        <div class="order-step-description">
                            <div id="toShipOrderSummary" class="order-summary-details">
                                <p id="toShipUnitPrice">Unit Price: $10.00</p>
                                <p id="toShipQuantity">Quantity: 5</p>
                                <p id="toShipSubtotal" class="order-subtotal">Subtotal: $50.00</p>
                            </div>
                        </div>
                        <div id="toShipAdminApprovedDate" class="order-step-date">Jan 16, 2023</div>
                    </div>
                </div>
                
                <!-- Processing Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">3</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="toShipProcessingTitle" class="order-step-title">Processing</div>
                        <div id="toShipProcessingDesc" class="order-step-description">Your items were being prepared</div>
                        <div id="toShipProcessingDate" class="order-step-date">Jan 17, 2023</div>
                    </div>
                </div>
                
                <!-- To Ship Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">4</div>
                    <div class="order-step-connector-current"></div>
                    <div class="order-step-content">
                        <div id="toShipToShipTitle" class="order-step-title">To Ship</div>
                        <div id="toShipToShipDesc" class="order-step-description">Your items are ready to be shipped</div>
                        <div id="toShipToShipDate" class="order-step-date">Jan 18, 2023</div>
                    </div>
                </div>
                
                <!-- Completed Step -->
                <div class="order-step">
                    <div class="order-step-number">6</div>
                    <div class="order-step-connector-pending"></div>
                    <div class="order-step-content">
                        <div id="toShipCompletedTitle" class="order-step-title">Completed</div>
                        <div id="toShipCompletedDesc" class="order-step-description">Order will be marked completed after delivery</div>
                        <div id="toShipCompletedDate" class="order-step-date">Pending</div>
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

function openToShipOrderModal(event) {
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

    document.getElementById('toShipProcessTitle').textContent = `Ticket #${ticket} Process Details`;
    document.getElementById('toShipQuotePlacedDate').textContent = formatDate(createdAt);
    document.getElementById('toShipUnitPrice').textContent = `Unit Price: ₱${parseFloat(pricing).toFixed(2)}`;
    document.getElementById('toShipQuantity').textContent = `Quantity: ${quantity}`;
    document.getElementById('toShipSubtotal').textContent = `Subtotal: ₱${parseFloat(subtotal).toFixed(2)}`;
    document.getElementById('toShipAdminApprovedDate').textContent = formatDate(userApprovedDate);
    document.getElementById('toShipProcessingDate').textContent = formatDate(processingDate);
    document.getElementById('toShipToShipDate').textContent = formatDate(toShipDate);

    // Future steps
    document.getElementById('toShipCompletedDate').textContent = 'Pending';

    document.getElementById('toShipProcessModal').setAttribute('data-ticket', ticket);
    document.getElementById('toShipProcessModal').style.display = 'flex';
}

    function closeToShipProcessModal() {
        document.getElementById('toShipProcessModal').style.display = 'none';
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to view buttons
        const viewButtons = document.querySelectorAll('.to-ship-order-btn');
        viewButtons.forEach(button => {
            button.addEventListener('click', openToShipOrderModal);
        });

        // Close modal when clicking outside
        document.getElementById('toShipProcessModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeToShipProcessModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeToShipProcessModal();
            }
        });
    });

    // To Ship Orders Search Functionality (AJAX-based)

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ToShipSearchInput');
    const clearSearchBtn = document.getElementById('clearToShipSearch');
    const printTypeFilter = document.getElementById('toShipPrintTypeFilter');
    let searchTimeout;

    function triggerSearch() {
        const searchTerm = searchInput ? searchInput.value.trim() : '';
        const printType = printTypeFilter ? printTypeFilter.value : '';
        searchToShipOrders(searchTerm, 1, printType);
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (clearSearchBtn) {
                clearSearchBtn.style.display = this.value.trim() ? 'block' : 'none';
            }
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(triggerSearch, 400);
        });
    }

    if (printTypeFilter) {
        printTypeFilter.addEventListener('change', triggerSearch);
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (printTypeFilter) printTypeFilter.value = '';
            clearSearchBtn.style.display = 'none';
            triggerSearch();
        });
    }

    // Initial search
    triggerSearch();
});

function searchToShipOrders(searchTerm = '', page = 1, printType = '') {
    const container = document.getElementById('ship-orders-container');
    if (!container) {
        console.error('To ship orders container not found');
        return;
    }
    container.innerHTML = '<div class="no-orders">Searching...</div>';
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (printType) params.append('print_type', printType);
    params.append('page', page);
    const searchUrl = 'functions/search_to_ship_orders.php?' + params.toString();
    fetch(searchUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                container.innerHTML = `<div class="no-orders">Error: ${data.error}</div>`;
                return;
            }
            if (data.success === false) {
                container.innerHTML = `<div class="no-orders">${data.error || 'Search failed'}</div>`;
                return;
            }
            displayToShipSearchResults(data, searchTerm, page);
        })
        .catch(error => {
            container.innerHTML = '<div class="no-orders">Search failed: ' + error.message + '</div>';
        });
}

function displayToShipSearchResults(data, searchTerm, currentPage) {
    const container = document.getElementById('ship-orders-container');
    const orders = data.orders || [];
    const totalPages = data.total_pages || 1;
    
    if (orders.length === 0) {
        const message = searchTerm 
            ? `No orders to ship found for ticket "${searchTerm}"`
            : 'No orders to ship found';
        container.innerHTML = `<div class="no-orders">${message}</div>`;
        return;
    }
    
    let html = '';
    
    orders.forEach(order => {
        // Use the thumbnail path from the server response
        const thumbnail = order.thumbnail || order.design_file;
        const statusClass = order.status === 'to_ship' ? 'status-approved' : 
                           order.status === 'approved' ? 'status-approved' : 'status-pending';
        const statusText = order.status === 'to_ship' ? 'To Ship' : 
                          order.status === 'approved' ? 'Approved' : 'Pending';
        
        html += `
            <div class="quote-card animate__animated animate__fadeInUp" data-ticket="${escapeHtml(order.ticket)}">
                <img src="${thumbnail}" alt="Design" class="card-image" onerror="this.src='../image-placeholder.png'">
                <span class="card-status ${statusClass}">${statusText}</span>
                <div class="card-content">
                    <h3 class="card-title">${escapeHtml(order.print_type)}</h3>
                    <div class="card-details">
                        <div class="card-detail"><span class="detail-label">Quantity</span><span class="detail-value">${escapeHtml(order.quantity)}</span></div>
                        <div class="card-detail"><span class="detail-label">Ticket #</span><span class="detail-value">${escapeHtml(order.ticket)}</span></div>
                    </div>
                    <span class="quote-date">${order.created_at_formatted}</span>
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
                        data-status="${escapeHtml(order.status)}"
                        data-note="${escapeHtml(order.note || '')}"
                        data-address="${escapeHtml(order.address)}"
                        data-pricing="${escapeHtml(order.pricing)}"
                        data-subtotal="${escapeHtml(order.subtotal)}"
                        data-items='${JSON.stringify(order.items).replace(/'/g, "&#39;")}'>
                    </i>
                    <div class="card-actions">
                        <div class="button-group">
                            <button class="view-details-btn to-ship-order-btn" 
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
        html += '<div class="pagination" id="to-ship-search-pagination">';
        
        // Previous button
        if (currentPage > 1) {
            html += `<a href="javascript:void(0)" class="page-btn prev-next" onclick="searchToShipOrders('${escapeHtml(searchTerm)}', ${currentPage - 1})">‹ Prev</a>`;
        } else {
            html += `<span class="page-btn prev-next disabled">‹ Prev</span>`;
        }
        
        // First page
        if (currentPage > 3) {
            html += `<a href="javascript:void(0)" class="page-btn" onclick="searchToShipOrders('${escapeHtml(searchTerm)}', 1)">1</a>`;
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
                html += `<a href="javascript:void(0)" class="page-btn" onclick="searchToShipOrders('${escapeHtml(searchTerm)}', ${i})">${i}</a>`;
            }
        }
        
        // Last page
        if (currentPage < totalPages - 2) {
            if (currentPage < totalPages - 3) {
                html += `<span class="page-dots">...</span>`;
            }
            html += `<a href="javascript:void(0)" class="page-btn" onclick="searchToShipOrders('${escapeHtml(searchTerm)}', ${totalPages})">${totalPages}</a>`;
        }
        
        // Next button
        if (currentPage < totalPages) {
            html += `<a href="javascript:void(0)" class="page-btn prev-next" onclick="searchToShipOrders('${escapeHtml(searchTerm)}', ${currentPage + 1})">Next ›</a>`;
        } else {
            html += `<span class="page-btn prev-next disabled">Next ›</span>`;
        }
        
        html += '</div>';
    }
    
    container.innerHTML = html;
    
    // Re-attach event listeners to the new buttons
    document.querySelectorAll('.view-details-btn.to-ship-order-btn').forEach(button => {
        button.addEventListener('click', openToShipOrderModal);
    });
}

// Clear to_ship search functionality
function clearToShipSearch() {
    const searchInput = document.getElementById('ToShipSearchInput');
    const clearSearchBtn = document.getElementById('clearToShipSearch');
    
    if (searchInput) {
        searchInput.value = '';
        if (clearSearchBtn) {
            clearSearchBtn.style.display = 'none';
        }
        // Reload the original page content
        window.location.href = 'to-ship-orders?page=1';
    }
}

// Helper function to escape HTML (reuse from your existing code)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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