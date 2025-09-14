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

    <style>
        .orders-tabs {
    display: flex;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 20px;
}

.tab-button {
    padding: 12px 24px;
    text-decoration: none;
    color: #555;
    font-weight: 500;
    border: none;
    background: transparent;
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
}

.tab-button:hover {
    color: #2196F3;
    background-color: #f8f9fa;
}

.tab-button.active {
    color: #2196F3;
    border-bottom: 3px solid #2196F3;
    font-weight: 600;
}
        .quote-date{
            font-size:12px;
            margin-left:7px;
        }

/* Align to right and make it responsive */
.search-wrapper {
  display: flex;
  justify-content: flex-end;
  padding: 0.6rem;
}

/* Container with icon */
.search-container {
  position: relative;
  width: 100%;
  max-width: 250px;
}

/* Modern input field */
.search-input {
  width: 100%;
  padding: 0.6rem 0.5rem 0.6rem 2.5rem; /* space for icon */
  border: 1px solid #ccc;
  border-radius: 999px;
  background-color: #f1f3f5;
  font-size: 0.8rem;
  transition: 0.2s all ease-in-out;
  outline: none;
}

.search-input:focus {
  background-color: #fff;
  border-color: #339af0;
  box-shadow: 0 0 0 3px rgba(51, 154, 240, 0.2);
}

/* Search icon inside input */
.search-icon {
  position: absolute;
  top: 50%;
  left: 0.9rem;
  transform: translateY(-50%);
  color: #888;
  font-size: 0.85rem;
  pointer-events: none;
}
@media (max-width: 576px) {
  .search-wrapper {
    justify-content: center;
  }
}

 .quote-card.hidden {
        display: none !important;
    }
    </style>
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
                 <img src="../assets/images/icons/shirt1.png" alt="" style="height: 45px; width: 35px;">
                CSH
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
        </div>
    </div>
</div>




        <div class="quotes-container" id="pending-orders-container" style="display:block;">
           
        <?php
include '../db_connection.php';

// Fetch initial orders for the logged-in user
$user_id = $_SESSION['user_id'] ?? null;
$has_orders = false;
$orders = [];

if ($user_id) {
    $sql = "SELECT orders.*, users.name, users.phone_number 
            FROM orders 
            INNER JOIN users ON orders.user_id = users.id 
            WHERE orders.user_id = ? AND orders.status = 'pending' 
            ORDER BY orders.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $has_orders = true;
        while ($order = $result->fetch_assoc()) {

            // Fetch shirt items for this order
            $items_sql = "SELECT shirt_color, quantity 
                          FROM items 
                          WHERE order_id = ?";
            $items_stmt = $conn->prepare($items_sql);
            $items_stmt->bind_param("i", $order['id']);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();

            $shirtItems = [];
            if ($items_result && $items_result->num_rows > 0) {
                while ($item = $items_result->fetch_assoc()) {
                    $shirtItems[] = $item;
                }
            }
            $items_stmt->close();

            // Store the items in the order array for use in the button
            $order['items'] = $shirtItems;

            $orders[] = $order;
        }
    }
    $stmt->close();
}

?>

<?php if (!$user_id): ?>
    <div class="no-orders">No user ID found. Please log in.</div>
<?php elseif (!$has_orders): ?>
    <div class="no-orders">No orders found</div>
<?php else: ?>
    <?php foreach ($orders as $order): 
        $status = $order['is_approved_admin'] === 'yes' ? 'Approved' : ($order['admin_approved_date'] ? 'Pending' : 'Processing');
        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $status));
        $createdAt = date('M d, Y', strtotime($order['created_at']));
        $subtotal = $order['pricing'] * $order['quantity'];
        
        // Determine the appropriate thumbnail based on file extension
        $designFile = $order['design_file'];
        $fileExtension = strtolower(pathinfo($designFile, PATHINFO_EXTENSION));
        
        if ($fileExtension === 'psd') {
            $thumbnail = "../photoshop.png";
        } elseif ($fileExtension === 'pdf') {
            $thumbnail = "../pdf.png";
        } elseif ($fileExtension === 'ai') {
            $thumbnail = "../illustrator.png";
        } else {
            // For image files, use the actual file
            $thumbnail = htmlspecialchars($designFile, ENT_QUOTES, 'UTF-8');
        }
    ?>
        <div class="quote-card animate__animated animate__fadeInUp">
            <img src="<?= $thumbnail ?>" alt="Design" class="card-image">
            <span class="card-status <?= $statusClass ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
            <div class="card-content">
                <h3 class="card-title"><?= htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8') ?></h3>
                <div class="card-details">
                    <div class="card-detail">
                        <span class="detail-label">Quantity</span>
                        <span class="detail-value"><?= htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="card-detail">
                        <span class="detail-label">Ticket #</span>
                        <span class="detail-value"><?= htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
                 <span class="quote-date"><?= $createdAt ?></span>
               <div class="card-actions">
    <div class="button-group">
          <button class="view-details-btn view-pending-orders" 
        data-id="<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8') ?>" 
        data-ticket="<?= htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') ?>" 
        data-created-at="<?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?>">
    <i class="fas fa-eye"></i> View
</button>

<button class="details-btn"
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
    data-items='<?= json_encode($order['items'], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
   <i class="fa fa-info-circle"></i>Details
</button>

        <a href="<?= htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8') ?>" 
           class="download-btn" 
           download 
           title="Download design file">
            <i class="fas fa-download"></i>
        </a>


    </div>
   
</div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
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
    <label>Shirt Colors & Quantities (Total Minimum: 500)</label>
    <div id="shirtItemsContainer">
        <div class="shirt-item-row">
            <div class="form-row">
               <div class="form-col">
    <select name="shirt_color[]" class="form-control" required>
        <option value="">Select Color</option>
        <option value="White">White</option>
        <option value="Black">Black</option>
        <option value="Red">Red</option>
        <option value="Blue">Blue</option>
        <option value="Green">Green</option>
        <option value="Yellow">Yellow</option>
        <option value="Orange">Orange</option>
        <option value="Purple">Purple</option>
        <option value="Pink">Pink</option>
        <option value="Gray">Gray</option>
        <option value="Brown">Brown</option>
        <option value="Navy">Navy</option>
        <option value="Maroon">Maroon</option>
        <option value="Teal">Teal</option>
        <option value="Olive">Olive</option>
        <option value="Other">Other (Specify in notes)</option>
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
        <i class="fas fa-plus"></i> Add Another Shirt
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
<!-- ✅ Full Order Details Modal -->
<div id="detailsModal" class="details-modal">
  <div class="detail-modal-content">
    <span class="detail-modal-close">&times;</span>
    <h2>Order Details</h2>
    <div class="detail-modal-body">
      <!-- Group 1: Ticket and Customer in one row -->
      <div class="detail-modal-row grouped-row">
        <div class="grouped-item">
          <span class="detail-modal-label">Ticket #:</span>
          <span id="detail-modal-ticket" class="detail-modal-value"></span>
        </div>
        <div class="grouped-item">
          <span class="detail-modal-label">Customer:</span>
          <span id="detail-modal-name" class="detail-modal-value"></span>
        </div>
      </div>

      <!-- Group 2: Image with buttons and details -->
      <div class="detail-modal-row grouped-row-2">
        <div class="grouped-item">
          <span class="detail-modal-label">Design:</span>
          <div class="design-image-container">
            <img id="detail-modal-design" src="" alt="Design" class="design-image">
            <div class="design-buttons">
              <button class="view-design-btn">View</button>
              <button class="download-design-btn">Download</button>
            </div>
          </div>
        </div>
        <div class="grouped-item details-column">
          <div class="detail-row">
            <span class="detail-modal-label">Print Type:</span>
            <span id="detail-modal-print-type" class="detail-modal-value"></span>
          </div>
          <div class="detail-row">
            <span class="detail-modal-label">Quantity:</span>
            <span id="detail-modal-quantity" class="detail-modal-value"></span>
          </div>
          <div class="detail-row">
            <span class="detail-modal-label">Mobile #:</span>
            <span id="detail-modal-mobile" class="detail-modal-value"></span>
          </div>
        </div>
      </div>

      <!-- Shirt Colors & Quantities Section -->
      <div class="detail-modal-row">
        <span class="detail-modal-label">Shirt Colors & Quantities:</span>
        <div id="detail-modal-shirt-items" class="shirt-items-container"></div>
      </div>

      <!-- Note -->
      <div class="detail-modal-row">
        <span class="detail-modal-label">Note:</span>
        <span id="detail-modal-note" class="detail-modal-value note-value"></span>
      </div>

      <!-- Group 3: Date and Status -->
      <div class="detail-modal-row grouped-row">
        <div class="grouped-item">
          <span class="detail-modal-label">Date:</span>
          <span id="detail-modal-date" class="detail-modal-value"></span>
        </div>
        <div class="grouped-item">
          <span class="detail-modal-label">Status:</span>
          <span id="detail-modal-status" class="detail-modal-value"></span>
        </div>
      </div>

      <!-- Address -->
      <div class="detail-modal-row">
        <span class="detail-modal-label">Address:</span>
        <span id="detail-modal-address" class="detail-modal-value address-value"></span>
      </div>

      <!-- Price & Subtotal -->
      <!-- <div class="detail-modal-row grouped-row">
        <div class="grouped-item">
          <span class="detail-modal-label">Price per pcs:</span>
          <span id="detail-modal-price" class="detail-modal-price-value"></span>
        </div>
        <div class="grouped-item">
          <span class="detail-modal-label">Subtotal:</span>
          <span id="detail-modal-subtotal" class="detail-modal-subtotal-value"></span>
        </div>
      </div> -->

      <!-- Hidden values if needed for JS -->
      <input type="hidden" id="subtotal-value" name="subtotal">
      <input type="hidden" id="pricing-value" name="pricing">
      <input type="hidden" id="user_id" name="user_id">
      <input type="hidden" id="ticket-value-input" name="ticket-value-input">
    </div>
  </div>
</div>

<!-- Image Viewer Modal -->
<div id="userImageViewerModal" class="image-viewer-modal" style="display:none;">
  <span class="close-viewer">&times;</span>
  <img class="image-viewer-content" id="userExpandedDesignImage" alt="Design Preview">
  <div id="viewerLoading" class="viewer-loading" style="display:none;">Loading...</div>
</div>

<style>

/* Image Viewer Modal */
.image-viewer-modal {
  display: none;
  position: fixed;
  z-index: 1001;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.9);
  text-align: center;
}

.image-viewer-content {
  margin: auto;
  display: block;
  max-width: 90%;
  max-height: 80vh;
  margin-top: 10vh;
}

.close-viewer {
  position: absolute;
  top: 20px;
  right: 30px;
  color: #f1f1f1;
  font-size: 35px;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}

.close-viewer:hover {
  color: #bbb;
}

.viewer-loading {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 1rem;
}
/* Reuse Quote Modal Styles for Details Modal */
.details-modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  overflow-y: auto;
}

.detail-modal-content {
  background-color: white;
  margin: 5% auto;
  padding: 20px;
  border-radius: 6px;
  width: 90%;
  max-width: 450px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  position: relative;
}

.detail-modal-close {
  color: #999;
  position: absolute;
  right: 20px;
  top: 15px;
  font-size: 24px;
  font-weight: bold;
  cursor: pointer;
  line-height: 1;
}

.detail-modal-close:hover {
  color: #333;
}

.detail-modal h2 {
  font-size: 1.2rem;
  margin: 0 0 15px 0;
  color: #333;
  font-weight: 600;
}

.detail-modal-body {
  margin: 15px 0;
  font-size: 0.9rem;
}

.detail-modal-row {
  margin-bottom: 12px;
}

.grouped-row {
  display: flex;
  gap: 15px;
}

.grouped-row-2 {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 15px;
  margin-bottom: 12px;
}

.grouped-item {
  flex: 1;
}

.details-column {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-modal-label {
  font-weight: 500;
  color: #666;
  display: block;
  margin-bottom: 2px;
  font-size: 0.85rem;
}

.detail-modal-value {
  color: #333;
  word-break: break-word;
  font-size: 0.9rem;
  line-height: 1.4;
}

/* Design section with buttons */
.design-image-container {
  position: relative;
  margin-bottom: 8px;
}

.design-image {
  border-radius: 4px;
  border: 1px solid #eee;
  max-width: 120px;
  height: auto;
  display: block;
}

.design-buttons {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.view-design-btn,
.download-design-btn{
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
  transition: all 0.2s;
  flex: 1;
}

.view-design-btn {
  background-color: #2196F3;
  color: white;
}

.view-design-btn:hover {
  background-color: #0b7dda;
}

.download-design-btn {
  background-color: #4CAF50;
  color: white;
}

.download-design-btn:hover {
  background-color: #45a049;
}

/* Special value styles */
.note-value {
  display: inline-block;
  padding: 6px 8px;
  background-color: #f8f8f8;
  border-radius: 3px;
  width: 100%;
  font-style: italic;
}

.address-value {
  display: inline-block;
  padding: 6px 8px;
  background-color: #f5f9ff;
  border-radius: 3px;
  width: 100%;
  white-space: pre-wrap;
}

/* Subtotal */
.subtotal-text {
  display: inline-block;
  padding: 8px;
  background-color: #f0f8f0;
  border-radius: 4px;
  font-weight: 500;
  width: 100%;
  margin-top: 5px;
}

/* Responsive Adjustments */
@media (max-width: 480px) {
  .detail-modal-content {
    padding: 15px;
    margin: 10% auto;
    width: 95%;
  }

  .grouped-row {
    flex-direction: column;
    gap: 8px;
  }

  .grouped-row-2 {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .design-image {
    max-width: 100px;
  }
}


.details-btn {
  background-color: #f59e0b; /* amber-500 */
  color: white;
  border: none;
  padding: 5px 8px;
  border-radius: 6px;
  font-size: 11.2px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: background-color 0.2s ease;
}

.details-btn:hover {
  background-color: #d97706; /* amber-600 */
}

.details-btn i {
  font-size: 14px;
}

.details-modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
}

.details-modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  width: 600px;
  max-height: 80vh;
  overflow-y: auto;
}

.details-close {
  float: right;
  font-size: 22px;
  cursor: pointer;
}

.details-table {
  width: 100%;
  border-collapse: collapse;
  margin: 10px 0;
}
.details-table th, .details-table td {
  border: 1px solid #ddd;
  padding: 8px;
}
.details-table th {
  background: #f5f5f5;
  text-align: left;
  width: 30%;
}
.design-preview {
  margin-top: 10px;
}
.design-preview img {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 4px;
}

  .shirt-item-row {
    margin-bottom: 8px;
    padding: 8px 0;
}

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

/* Shirt items layout */
.shirt-items-container {
  margin-top: 10px;
  border: 1px solid #eee;
  border-radius: 4px;
  overflow: hidden;
}

.shirt-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 10px;
  font-size: 0.9rem;
  background-color: #fafafa;
}

.shirt-item:nth-child(even) {
  background-color: #fdfdfd;
}

.shirt-color {
  font-weight: 500;
  color: #333;
}

.shirt-qty {
  font-weight: 600;
  color: #444;
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

// Grab the modal
const detailsModal = document.getElementById("detailsModal");

// Function to open the details modal
function openDetailsModal(event) {
    const button = event.currentTarget;
    // Grab data attributes
    const id = button.getAttribute("data-id");
    const userId = button.getAttribute("data-user-id");
    const ticket = button.getAttribute("data-ticket");
    const design = button.getAttribute("data-design");
    const mobile = button.getAttribute("data-mobile");
    const name = button.getAttribute("data-name");
    const printType = button.getAttribute("data-print-type");
    const quantity = button.getAttribute("data-quantity");
    const date = button.getAttribute("data-date");
    const status = button.getAttribute("data-status");
    const note = button.getAttribute("data-note");
    const address = button.getAttribute("data-address");
    const items = JSON.parse(button.getAttribute("data-items") || "[]");



    // 👇 Debug all fetched details
    console.group("Details Modal Data");
    console.log("id:", id);
    console.log("userId:", userId);
    console.log("ticket:", ticket);
    console.log("design:", design);
    console.log("mobile:", mobile);
    console.log("name:", name);
    console.log("printType:", printType);
    console.log("quantity:", quantity);
    console.log("date:", date);
    console.log("status:", status);
    console.log("note:", note);
    console.log("address:", address);
    console.log("items (raw):", items); // ✅ use 'items', not 'itemsData'
    console.groupEnd();

    // Determine correct design image
    let imageSrc;
    const fileExtension = design.split('.').pop().toLowerCase();
    if (["psd","pdf","ai"].includes(fileExtension)) {
        imageSrc = fileExtension === "psd" ? "../photoshop.png" :
                   fileExtension === "pdf" ? "../pdf.png" : "../illustrator.png";
    } else {
        imageSrc = '../user/' + design;
    }

    // Populate fields in the details modal
    document.getElementById("detail-modal-ticket").textContent = ticket;
    document.getElementById("detail-modal-name").textContent = name;
    document.getElementById("detail-modal-design").src = imageSrc;
    document.getElementById("detail-modal-print-type").textContent = printType;
    document.getElementById("detail-modal-quantity").textContent = quantity;
    document.getElementById("detail-modal-date").textContent = date;
    document.getElementById("detail-modal-status").textContent = status;
    document.getElementById("detail-modal-note").textContent = note || "N/A";
    document.getElementById("detail-modal-address").textContent = address || "N/A";
    document.getElementById("detail-modal-mobile").textContent = mobile || "N/A";
    document.getElementById("user_id").value = userId;
    document.getElementById("ticket-value-input").value = ticket;


    // Populate shirt items
    const itemsContainer = document.getElementById("detail-modal-shirt-items");
    itemsContainer.innerHTML = "";
    if (items.length > 0) {
        items.forEach(item => {
            const div = document.createElement("div");
            div.classList.add("shirt-item");
            div.innerHTML = `<span class="shirt-color">${item.shirt_color}</span> 
                             <span class="shirt-qty">${item.quantity}</span>`;
            itemsContainer.appendChild(div);
        });
    } else {
        itemsContainer.innerHTML = "<em>No shirt colors added</em>";
    }

    // Show modal
    detailsModal.style.display = "flex";
    // At the end of openDetailsModal()
    detailsModal.setAttribute('data-design-file', design);

    // Only allow preview for images
    const imageExtensions = ['jpg','jpeg','png','gif','webp'];
    detailsModal.setAttribute(
        'data-is-viewable',
        imageExtensions.includes(design.split('.').pop().toLowerCase())
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

// Pending Orders Search Functionality
 document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('PendingSearchInput');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim().toLowerCase();
                const cards = document.querySelectorAll('.quote-card');
                
                cards.forEach(card => {
                    // Find the Ticket # value inside the card
                    let ticketNumber = '';
                    card.querySelectorAll('.card-detail').forEach(detail => {
                        const label = detail.querySelector('.detail-label');
                        const value = detail.querySelector('.detail-value');
                        if (label && value && label.textContent.trim().toLowerCase() === 'ticket #') {
                            ticketNumber = value.textContent.trim().toLowerCase();
                        }
                    });
                    
                    if (searchTerm === '' || ticketNumber.includes(searchTerm)) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });
            });
        }
    });

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