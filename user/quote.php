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
    <title>CSH Enterprises | Modern Cloth Printing</title>
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
    <a href="quote" class="tab-button active" >Pending</a>
    <a href="approved-order" class="tab-button  " >Approved</a>
    <a href="to-pickup-order" class="tab-button" >To Pick Up</a>
    <a href="processing-order" class="tab-button">Processing</a>
    <a href="to-ship-order" class="tab-button">To Ship</a>
    <a href="completed-order" class="tab-button">Completed</a>
    <a href="cancelled-orders" class="tab-button">Cancelled</a>
</div>

<!-- Desktop Sidebar (hidden on mobile) -->
<div class="orders-sidebar">
    <a href="quote" class="sidebar-item active">
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
    <a href="completed-order" class="sidebar-item">
        <i class="fas fa-flag-checkered"></i> Completed
    </a>
    <a href="cancelled-orders" class="sidebar-item">
        <i class="fas fa-times-circle"></i> Cancelled
    </a>
</div>

    <div class="search-wrapper">
        <div class="search-container">
            <!-- Pending -->
            <div class="pending-search" style="display: block;">
                <input type="text"
                       id="PendingSearchInput"
                       class="search-input"
                       placeholder="Search by Ticket #">
                <span class="search-icon">&#128269;</span>
                <button type="button" id="clearSearch" class="clear-search-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>




 <div class="quotes-container" id="pending-orders-container" style="display:block;">
<?php
if ($user_id) {
    // Pagination setup
    $limit = 8; // orders per page
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // Count total pending orders
    $count_sql = "SELECT COUNT(*) AS total FROM orders WHERE user_id = ? AND status = 'pending'";
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
            WHERE orders.user_id = ? AND orders.status = 'pending' 
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
              <span class="card-status status-pending">Pending</span>
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
                  data-items='<?= json_encode($shirtItems, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                </i>
                <div class="card-actions">
                  <div class="button-group">
                    <button class="view-details-btn view-pending-orders" 
                      data-id="<?= $order['id'] ?>" 
                      data-ticket="<?= htmlspecialchars($order['ticket']) ?>" 
                      data-created-at="<?= htmlspecialchars($order['created_at']) ?>">
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



    <div id="pendingOrderProcessModal" class="order-process-modal">
    <div class="order-process-modal-content">
        <span class="order-process-close-btn" onclick="closePendingOrderProcessModal()">&times;</span>
        <h2 id="pendingOrderProcessTitle" class="order-process-title">Ticket #12345 Process Details</h2>
        
        <div id="pendingOrderProcessSteps" class="order-process-steps-container">
            <!-- Quote Placed Step -->
            <div class="order-step order-step-completed">
                <div class="order-step-number">1</div>
                <div class="order-step-connector-completed"></div>
                <div class="order-step-content">
                    <div id="pendingQuotePlacedTitle" class="order-step-title">Quote Placed</div>
                    <div id="pendingQuotePlacedDesc" class="order-step-description">Your order request has been received</div>
                    <div id="pendingQuotePlacedDate" class="order-step-date">Jan 15, 2023</div>
                </div>
            </div>
            
            <!-- Admin Approved Step -->
            <div class="order-step order-step-current">
                <div class="order-step-number">2</div>
                <div class="order-step-connector-current"></div>
                <div class="order-step-content">
                    <div id="pendingAdminApprovedTitle" class="order-step-title">Admin Approved</div>
                    <div class="order-step-description">
                        <div id="pendingOrderSummary" class="order-summary-details">
                            <p id="pendingUnitPrice">Unit Price:Pending</p>
                            <p id="pendingQuantity">Quantity: Pending</p>
                            <p id="pendingSubtotal" class="order-subtotal">Subtotal: Pending</p>
                        </div>
                    </div>
                    <div id="pendingAdminApprovedDate" class="order-step-date">Pending</div>
                </div>
                <!-- User Approval Buttons (shown when needed) -->
                <div class="order-approval-actions">
                    <div class="order-approval-buttons">
                        <button class="order-agree-btn" >
                            <i class="fas fa-check-circle" disabled></i> Agree
                        </button>
                        <button class="order-cancel-btn">
                            <i class="fas fa-times-circle" disabled></i> Reject
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Pick Up Step (conditional) -->
            <!-- <div class="order-step">
                <div class="order-step-number">3</div>
                <div class="order-step-connector"></div>
                <div class="order-step-content">
                    <div id="pendingPickupTitle" class="order-step-title">Pick up</div>
                    <div id="pendingPickupDesc" class="order-step-description">Your items will be picked up at your location</div>
                    <div id="pendingPickupDate" class="order-step-date">Pending</div>
                </div>
            </div> -->
            
            <!-- Processing Step (conditional) -->
            <!-- <div class="order-step">
                <div class="order-step-number">4</div>
                <div class="order-step-connector"></div>
                <div class="order-step-content">
                    <div id="pendingProcessingTitle" class="order-step-title">Processing</div>
                    <div id="pendingProcessingDesc" class="order-step-description">Items are in the printing process</div>
                    <div id="pendingProcessingDate" class="order-step-date">Pending</div>
                </div>
            </div> -->
            
            <!-- Delivered Step (conditional) -->
            <!-- <div class="order-step">
                <div class="order-step-number">5</div>
                <div class="order-step-connector"></div>
                <div class="order-step-content">
                    <div id="pendingDeliveredTitle" class="order-step-title">Delivered</div>
                    <div id="pendingDeliveredDesc" class="order-step-description">Items have been delivered</div>
                    <div id="pendingDeliveredDate" class="order-step-date">Pending</div>
                </div>
            </div> -->
        </div>
    </div>
</div>

<!-- Confirmation Modal (dynamically shown when user confirms/rejects) -->
<div id="agreeConfirmationModal" class="agree-confirmation-modal" style="display: none;">
    <div class="agree-confirmation-modal-content">
        <h3>Quote Confirmed</h3>
        <p>Items will be picked up at your location</p>
        <div class="modal-buttons">
            <button id="closeAgreeConfirmationModal" class="modal-close-btn">Agree & Continue</button>
            <button id="cancelAgreeConfirmationModal" class="modal-cancel-btn">Cancel</button>
        </div>
    </div>
</div>
<!-- Confirmation Modal (dynamically shown when user confirms/rejects) ends -->

    <!-- Add Quote Button -->
    <button class="add-quote-btn pulse" id="addQuoteBtn">
        <i class="fas fa-plus-circle"></i> Add Quote
    </button>

    <!-- Quote Modal -->
    <div class="quote-modal" id="quoteModal" style="z-index: 500 !important;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create New Quote</h2>
            <button class="close-modal" id="closeModal">&times;</button>
        </div>
        <form id="quoteForm" enctype="multipart/form-data" method="post">
            <div class="form-group">
                <label for="designFile">Upload Design</label>
                <div class="file-input-container">
                    <div class="file-input-btn">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span class="upload-text">Click to upload design file</span>
                        <input type="file" id="designFile" name="designFile" class="file-input" 
                               accept=".psd,.ai,.pdf,image/*" required>
                    </div>
                    <div id="file-name" class="file-name-display"></div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col form-col-print-type">
                    <label for="printType">Print Type</label>
                    <select id="printType" name="printType" class="form-control" required>
                        <option value="">Select print type</option>
                        <option value="Direct to Film Print">Direct to Film Print</option>
                        <option value="Screen Printing">Screen Printing</option>
                        <option value="Emboss Print">Emboss Print</option>
                        <option value="Hi-Density Print">Hi-Density Print</option>
                        <option value="Glitters Print">Glitters Print</option>
                        <option value="Silk Screen Print">Silk Screen Print</option>
                    </select>
                </div>
              
            </div>
            <!-- Replace the quantity input section with this code -->
<div class="form-group">
    <label>Item & Colors (Total Minimum: 500)</label>
    <div id="shirtItemsContainer">
        <div class="shirt-item-row">
            <div class="form-row">
               <div class="form-col">
    <select name="item_color[]" class="form-control" required>
    <option value="">Select Item & Color</option>

    <!-- Shirt Colors -->
    <option value="Shirt (White)">Shirt (White)</option>
    <option value="Shirt (Black)">Shirt (Black)</option>
    <option value="Shirt (Red)">Shirt (Red)</option>
    <option value="Shirt (Blue)">Shirt (Blue)</option>
    <option value="Shirt (Green)">Shirt (Green)</option>
    <option value="Shirt (Yellow)">Shirt (Yellow)</option>
    <option value="Shirt (Orange)">Shirt (Orange)</option>
    <option value="Shirt (Purple)">Shirt (Purple)</option>
    <option value="Shirt (Pink)">Shirt (Pink)</option>
    <option value="Shirt (Gray)">Shirt (Gray)</option>
    <option value="Shirt (Brown)">Shirt (Brown)</option>
    <option value="Shirt (Navy)">Shirt (Navy)</option>
    <option value="Shirt (Maroon)">Shirt (Maroon)</option>
    <option value="Shirt (Teal)">Shirt (Teal)</option>
    <option value="Shirt (Olive)">Shirt (Olive)</option>

    <!-- Jacket Colors -->
    <option value="Jacket (White)">Jacket (White)</option>
    <option value="Jacket (Black)">Jacket (Black)</option>
    <option value="Jacket (Red)">Jacket (Red)</option>
    <option value="Jacket (Blue)">Jacket (Blue)</option>
    <option value="Jacket (Green)">Jacket (Green)</option>
    <option value="Jacket (Yellow)">Jacket (Yellow)</option>
    <option value="Jacket (Orange)">Jacket (Orange)</option>
    <option value="Jacket (Purple)">Jacket (Purple)</option>
    <option value="Jacket (Pink)">Jacket (Pink)</option>
    <option value="Jacket (Gray)">Jacket (Gray)</option>
    <option value="Jacket (Brown)">Jacket (Brown)</option>
    <option value="Jacket (Navy)">Jacket (Navy)</option>
    <option value="Jacket (Maroon)">Jacket (Maroon)</option>
    <option value="Jacket (Teal)">Jacket (Teal)</option>
    <option value="Jacket (Olive)">Jacket (Olive)</option>

    <!-- Shorts Colors -->
    <option value="Shorts (White)">Shorts (White)</option>
    <option value="Shorts (Black)">Shorts (Black)</option>
    <option value="Shorts (Red)">Shorts (Red)</option>
    <option value="Shorts (Blue)">Shorts (Blue)</option>
    <option value="Shorts (Green)">Shorts (Green)</option>
    <option value="Shorts (Yellow)">Shorts (Yellow)</option>
    <option value="Shorts (Orange)">Shorts (Orange)</option>
    <option value="Shorts (Purple)">Shorts (Purple)</option>
    <option value="Shorts (Pink)">Shorts (Pink)</option>
    <option value="Shorts (Gray)">Shorts (Gray)</option>
    <option value="Shorts (Brown)">Shorts (Brown)</option>
    <option value="Shorts (Navy)">Shorts (Navy)</option>
    <option value="Shorts (Maroon)">Shorts (Maroon)</option>
    <option value="Shorts (Teal)">Shorts (Teal)</option>
    <option value="Shorts (Olive)">Shorts (Olive)</option>

    <!-- Bag Colors -->
    <option value="Bag (White)">Bag (White)</option>
    <option value="Bag (Black)">Bag (Black)</option>
    <option value="Bag (Red)">Bag (Red)</option>
    <option value="Bag (Blue)">Bag (Blue)</option>
    <option value="Bag (Green)">Bag (Green)</option>
    <option value="Bag (Yellow)">Bag (Yellow)</option>
    <option value="Bag (Orange)">Bag (Orange)</option>
    <option value="Bag (Purple)">Bag (Purple)</option>
    <option value="Bag (Pink)">Bag (Pink)</option>
    <option value="Bag (Gray)">Bag (Gray)</option>
    <option value="Bag (Brown)">Bag (Brown)</option>
    <option value="Bag (Navy)">Bag (Navy)</option>
    <option value="Bag (Maroon)">Bag (Maroon)</option>
    <option value="Bag (Teal)">Bag (Teal)</option>
    <option value="Bag (Olive)">Bag (Olive)</option>

    <!-- Jersey Colors -->
    <option value="Jersey (White)">Jersey (White)</option>
    <option value="Jersey (Black)">Jersey (Black)</option>
    <option value="Jersey (Red)">Jersey (Red)</option>
    <option value="Jersey (Blue)">Jersey (Blue)</option>
    <option value="Jersey (Green)">Jersey (Green)</option>
    <option value="Jersey (Yellow)">Jersey (Yellow)</option>
    <option value="Jersey (Orange)">Jersey (Orange)</option>
    <option value="Jersey (Purple)">Jersey (Purple)</option>
    <option value="Jersey (Pink)">Jersey (Pink)</option>
    <option value="Jersey (Gray)">Jersey (Gray)</option>
    <option value="Jersey (Brown)">Jersey (Brown)</option>
    <option value="Jersey (Navy)">Jersey (Navy)</option>
    <option value="Jersey (Maroon)">Jersey (Maroon)</option>
    <option value="Jersey (Teal)">Jersey (Teal)</option>
    <option value="Jersey (Olive)">Jersey (Olive)</option>
</select>


</div>
                <div class="form-col">
                    <input type="number" name="shirt_quantity[]" class="form-control shirt-quantity" min="1" placeholder="Qty" required>
                </div>
                <div class="form-col" style="flex: 0 0 auto;">
                    <button type="button" class="remove-item-btn" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <button type="button" id="addShirtItem" class="add-item-btn">
        <i class="fas fa-plus"></i> Add Another
    </button>
    <div class="total-quantity-display">
        Total Quantity: <span id="totalQuantity">0</span>
    </div>
</div>
            <input type="text" name="address" id="address" class="address" 
                   value="<?php echo htmlspecialchars($full_address, ENT_QUOTES, 'UTF-8'); ?>" readonly style="display:none;">

            <div class="form-group">
                <label for="note">Note</label>
                <textarea id="note" name="note" class="form-control note-input" rows="2" placeholder="Enter any additional notes or instructions"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Submit Quote
                </button>
            </div>
        </form>
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

<style>

.add-item-btn {
    margin-top: 8px;
    padding: 6px 10px;
    background: none;
    border: 1px dashed #ccc;
    border-radius: 4px;
    color: #666;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.15s;
}

.add-item-btn:hover {
    border-color: #4a90e2;
    color: #4a90e2;
}

.remove-item-btn {
    padding: 8px 10px;
    background: none;
    color: #ff6b6b;
    border: 1px solid #ff6b6b;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
}

.remove-item-btn:hover {
    background-color: #ff6b6b;
    color: white;
}

.total-quantity-display {
    margin-top: 8px;
    padding: 8px 0;
    font-weight: 500;
    border-top: 1px solid #eee;
}

.total-quantity-display span {
    font-weight: bold;
    color: #4a90e2;
}

/* Make form rows more compact */
.form-row {
    gap: 8px;
    margin-bottom: 8px;
}

/* Adjust form columns for better spacing */
.form-col {
    margin-bottom: 0;
}
/* Modal Styles - Fixed Positioning */
.quote-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 2000;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    padding: 20px;
    box-sizing: border-box;
}

.quote-modal.active {
    display: flex;
    opacity: 1;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    transform: translateY(-20px);
    transition: transform 0.3s ease;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.quote-modal.active .modal-content {
    transform: translateY(0);
}

.modal-header {
    background: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eaeaea;
}

.modal-header h2 {
    margin: 0;
    color: #333;
    font-size: 1.5rem;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.8rem;
    cursor: pointer;
    color: #999;
    transition: color 0.2s;
}

.close-modal:hover {
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.form-col {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.form-col-print-type {
    flex: 7;
}

.form-col-quantity {
    flex: 3;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #444;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
    background-color: #fff;
}

.form-control:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.file-input-container {
    margin-top: 5px;
}

.file-input-btn {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 25px;
    border: 2px dashed #ddd;
    border-radius: 6px;
    background-color: #f9f9f9;
    cursor: pointer;
    transition: border-color 0.2s, background-color 0.2s;
    text-align: center;
}

.file-input-btn:hover {
    border-color: #4a90e2;
    background-color: #f0f7ff;
}

.file-input-btn i {
    font-size: 2rem;
    color: #4a90e2;
    margin-bottom: 10px;
}

.upload-text {
    color: #666;
    font-size: 0.9rem;
}

.file-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-name-display {
    margin-top: 8px;
    font-size: 0.85rem;
    color: #666;
    word-break: break-all;
}

.note-input {
    resize: vertical;
    min-height: 80px;
}

.form-actions {
    margin-top: 25px;
}

.submit-btn {
    width: 100%;
    padding: 14px;
    background-color: #4a90e2;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.submit-btn:hover {
    background-color: #3a7bc8;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .modal-content {
        padding: 20px;
    }
    
    .form-row {
        flex-direction: column;
        gap: 20px;
    }
    
    .form-col {
        width: 100%;
    }
}



</style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/quote.js"></script>



    <script>
        // Details Modal Logic
// Details Modal Logic


   // Function to open the modal with order details
function openPendingOrderModal(event) {
    const button = event.currentTarget;
    const ticket = button.getAttribute('data-ticket');
    const createdAt = button.getAttribute('data-created-at');
    
    // Format the date (assuming createdAt is in ISO format like 'YYYY-MM-DD HH:MM:SS')
    const date = new Date(createdAt);
    const formattedDate = date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });

    // Update modal content with the order details
    document.getElementById('pendingOrderProcessTitle').textContent = `Ticket #${ticket} Process Details`;
    document.getElementById('pendingQuotePlacedDate').textContent = formattedDate;

    // Show the modal
    document.getElementById('pendingOrderProcessModal').style.display = 'flex';
}

// Function to close the modal
function closePendingOrderProcessModal() {
    document.getElementById('pendingOrderProcessModal').style.display = 'none';
}

// Set up event listeners when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Open modal when view buttons are clicked and pass data
    document.querySelectorAll('.view-details-btn.view-pending-orders').forEach(button => {
        button.addEventListener('click', openPendingOrderModal);
    });
    
    // Close modal when clicking outside content
    document.getElementById('pendingOrderProcessModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePendingOrderProcessModal();
        }
    });
});

    function openDetailsModalFromCard(element) {
    // If the element itself is the icon, use it
    const button = element.tagName === "I" ? element : element.querySelector('i.details-icon');
    
    if (button) {
        openDetailsModal({ currentTarget: button });
    }
}
      // Grab the modal
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


// Close modal when clicking the close button or outside content
detailsModal.querySelector(".detail-modal-close").addEventListener("click", () => {
    detailsModal.style.display = "none";
});
detailsModal.addEventListener("click", e => {
    if (e.target === detailsModal) detailsModal.style.display = "none";
});

// Add event listeners to all detail buttons
document.querySelectorAll(".details-btn").forEach(btn => {
    btn.addEventListener("click", openDetailsModal);
});
// Set up event listeners when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Open modal when any .details-btn is clicked
    document.querySelectorAll('.details-btn').forEach(button => {
        button.addEventListener('click', openDetailsModal);
    });

    // Close modal when clicking the close button
    document.querySelector('#detailsModal .detail-modal-close')
            .addEventListener('click', closeDetailsModal);

    // Close modal when clicking outside the modal content
    document.getElementById('detailsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDetailsModal();
        }
    });
});

// Pending Orders Search Functionality (AJAX-based)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('PendingSearchInput');
    const clearSearchBtn = document.getElementById('clearSearch');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            clearSearchBtn.style.display = searchTerm ? 'block' : 'none';
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Set new timeout to avoid too many requests
            searchTimeout = setTimeout(() => {
                searchPendingOrders(searchTerm, 1);
            }, 500); // 500ms delay
        });
    }
    
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            searchPendingOrders('', 1);
        });
    }
});

function searchPendingOrders(searchTerm = '', page = 1) {
    const container = document.getElementById('pending-orders-container');
    
    // Show loading state
    container.innerHTML = '<div class="no-orders">Searching...</div>';
    
    // Build URL with parameters - fix the path
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    params.append('page', page);
    
    // Fix the URL path - adjust based on your folder structure
    const searchUrl = 'functions/search_pending_orders.php?' + params.toString();
    
    console.log('Searching with URL:', searchUrl); // Debug log
    
    fetch(searchUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Search response:', data); // Debug log
            
            if (data.error) {
                container.innerHTML = `<div class="no-orders">Error: ${data.error}</div>`;
                return;
            }
            
            if (data.success === false) {
                container.innerHTML = `<div class="no-orders">${data.error || 'Search failed'}</div>`;
                return;
            }
            
            displaySearchResults(data, searchTerm, page);
        })
        .catch(error => {
            console.error('Search error:', error);
            container.innerHTML = '<div class="no-orders">Search failed: ' + error.message + '</div>';
        });
}

function displaySearchResults(data, searchTerm, currentPage) {
    const container = document.getElementById('pending-orders-container');
    const orders = data.orders || [];
    const totalPages = data.total_pages || 1;
    
    if (orders.length === 0) {
        const message = searchTerm 
            ? `No orders found for ticket "${searchTerm}"`
            : 'No orders found';
        container.innerHTML = `<div class="no-orders">${message}</div>`;
        return;
    }
    
    let html = '';
    
    orders.forEach(order => {
        // Use the thumbnail path from the server response
        const thumbnail = order.thumbnail || order.design_file;
        
        html += `
            <div class="quote-card animate__animated animate__fadeInUp">
                <img src="${thumbnail}" alt="Design" class="card-image" onerror="this.src='../image-placeholder.png'">
                <span class="card-status status-pending">Pending</span>
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
                        data-items='${JSON.stringify(order.items).replace(/'/g, "&#39;")}'>
                    </i>
                    <div class="card-actions">
                        <div class="button-group">
                            <button class="view-details-btn view-pending-orders" 
                                data-id="${order.id}" 
                                data-ticket="${escapeHtml(order.ticket)}" 
                                data-created-at="${escapeHtml(order.created_at)}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="../user/${escapeHtml(order.design_file)}" class="download-btn" download>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    if (totalPages > 0) {
        html += '<div class="pagination" id="search-pagination">';
        
        // Previous button
        if (currentPage > 1) {
            html += `<a href="javascript:void(0)" class="page-btn prev-next" onclick="searchPendingOrders('${escapeHtml(searchTerm)}', ${currentPage - 1})">‹ Prev</a>`;
        } else {
            html += `<span class="page-btn prev-next disabled">‹ Prev</span>`;
        }
        
        // First page
        if (currentPage > 3) {
            html += `<a href="javascript:void(0)" class="page-btn" onclick="searchPendingOrders('${escapeHtml(searchTerm)}', 1)">1</a>`;
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
                html += `<a href="javascript:void(0)" class="page-btn" onclick="searchPendingOrders('${escapeHtml(searchTerm)}', ${i})">${i}</a>`;
            }
        }
        
        // Last page
        if (currentPage < totalPages - 2) {
            if (currentPage < totalPages - 3) {
                html += `<span class="page-dots">...</span>`;
            }
            html += `<a href="javascript:void(0)" class="page-btn" onclick="searchPendingOrders('${escapeHtml(searchTerm)}', ${totalPages})">${totalPages}</a>`;
        }
        
        // Next button
        if (currentPage < totalPages) {
            html += `<a href="javascript:void(0)" class="page-btn prev-next" onclick="searchPendingOrders('${escapeHtml(searchTerm)}', ${currentPage + 1})">Next ›</a>`;
        } else {
            html += `<span class="page-btn prev-next disabled">Next ›</span>`;
        }
        
        html += '</div>';
    }
    
    container.innerHTML = html;
    
    // Re-attach event listeners to the new buttons
    document.querySelectorAll('.view-details-btn.view-pending-orders').forEach(button => {
        button.addEventListener('click', openPendingOrderModal);
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Clear search functionality
function clearSearch() {
    const searchInput = document.getElementById('PendingSearchInput');
    const clearSearchBtn = document.getElementById('clearSearch');
    
    if (searchInput) {
        searchInput.value = '';
        clearSearchBtn.style.display = 'none';
        // Reload the original page content
        window.location.href = 'quote?page=1';
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