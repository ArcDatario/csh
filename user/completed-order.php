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
    <title>CSH Enterprises | Completed Orders</title>
    <link rel="icon" href="../assets/images/icons/shirt.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/quote.css">
    <link rel="stylesheet" href="../assets/css/order-process-modal.css">
    <link rel="stylesheet" href="../assets/css/profile-modal.css">

    <style>
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
        
        /* Add style for hidden cards */
        .quote-card.hidden {
            display: none !important;
        }

        
                
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
    <a href="quote" class="tab-button " >Pending</a>
    <a href="approved-order" class="tab-button  " >Approved</a>
    <a href="to-pickup-order" class="tab-button" >To Pick Up</a>
    <a href="processing-order" class="tab-button ">Processing</a>
    <a href="to-ship-order" class="tab-button ">To Ship</a>
    <a href="completed-order" class="tab-button active">Completed</a>
</div>
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
        </style>

        <div class="search-wrapper">
            <div class="search-container">
                <!-- Completed -->
                <div class="completed-search" style="display: block;">
                    <input type="text"
                           id="CompletedSearchInput"
                           class="search-input"
                           placeholder="Search by Ticket #">
                    <span class="search-icon">&#128269;</span>
                </div>
            </div>
        </div>

        <div class="quotes-container completed-orders-container" id="completed-orders-container" style="display:block;">
            <?php
            include '../db_connection.php';

            // Fetch completed orders for the logged-in user
            $user_id = $_SESSION['user_id'] ?? null;
            $has_orders = false;
            $orders = [];

            if ($user_id) {
                 $sql = "SELECT o.*, u.name, u.phone_number
                    FROM orders o
                    JOIN users u ON o.user_id = u.id
                    WHERE o.user_id = ? 
                    AND o.status = 'completed'
                    ORDER BY o.created_at DESC;";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $has_orders = true;

                while ($order = $result->fetch_assoc()) {
                    // Fetch shirt items for this order
                    $items_sql = "SELECT shirt_color, quantity FROM items WHERE order_id = ?";
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

                    // Attach shirt items to the order array
                    $order['shirtItems'] = $shirtItems;
                    $orders[] = $order;

                    $items_stmt->close();
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
                    <div class="quote-card animate__animated animate__fadeInUp" data-ticket="<?= htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') ?>">
                        <img src="<?= $thumbnail ?>" alt="Design" class="card-image">
                        <span class="card-status status-approved"><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></span>
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
                                    <button class="view-details-btn completed-order-btn" 
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
                                        data-completed-date="<?= htmlspecialchars($order['completion_date'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-is-for-pickup="<?= htmlspecialchars($order['is_for_pickup'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-pickup-date="<?= htmlspecialchars($order['pickup_date'], ENT_QUOTES, 'UTF-8') ?>"
                                        >
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
                                        data-items='<?= json_encode($shirtItems, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
>
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
        <span class="detail-modal-label">Items:</span>
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
      <div class="detail-modal-row grouped-row">
        <div class="grouped-item">
          <span class="detail-modal-label">Price per pcs:</span>
          <span id="detail-modal-price" class="detail-modal-price-value"></span>
        </div>
        <div class="grouped-item">
          <span class="detail-modal-label">Subtotal:</span>
          <span id="detail-modal-subtotal" class="detail-modal-subtotal-value"></span>
        </div>
      </div> 

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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/quote.js"></script>

    <script>
// Grab the modal
const detailsModal = document.getElementById('detailsModal');

// Open Details Modal
function openDetailsModal(event) {
    const button = event.currentTarget;
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
    document.getElementById("detail-modal-name").textContent = name;
    document.getElementById("detail-modal-design").src = imageSrc;
    document.getElementById("detail-modal-print-type").textContent = printType;
    document.getElementById("detail-modal-quantity").textContent = quantity;
    document.getElementById("detail-modal-date").textContent = date;
    document.getElementById("detail-modal-status").textContent = status;
    document.getElementById("detail-modal-note").textContent = note || "N/A";
    document.getElementById("detail-modal-address").textContent = address || "N/A";
    document.getElementById("detail-modal-mobile").textContent = mobile || "N/A";

    // Price & subtotal
    document.getElementById("detail-modal-price").textContent = `₱${parseFloat(pricing).toFixed(2)}`;
    document.getElementById("detail-modal-subtotal").textContent = `₱${parseFloat(subtotal).toFixed(2)}`;

    // Hidden values for JS if needed
    document.getElementById("user_id").value = userId;
    document.getElementById("ticket-value-input").value = ticket;
    document.getElementById("pricing-value").value = pricing;
    document.getElementById("subtotal-value").value = subtotal;

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
        const button = event.currentTarget;
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

        // Search functionality
        const searchInput = document.getElementById('CompletedSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim().toLowerCase();
                const cards = document.querySelectorAll('.quote-card[data-ticket]');
                
                cards.forEach(card => {
                    const ticketNumber = card.getAttribute('data-ticket').toLowerCase();
                    
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