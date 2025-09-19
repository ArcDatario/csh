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
    <title>CSH Enterprises | Processing Orders</title>
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
  z-index: 11111;
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
/* ---------- Details Modal Base ---------- */
.details-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.6);
  justify-content: center;
  align-items: center;
  z-index: 9999;
  font-family: 'Arial', sans-serif;
}

.detail-modal-content {
  background: #fff;
  border-radius: 10px;
  width: 400px;
  max-width: 90%;
  padding: 20px 25px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
  position: relative;
  border: 2px dashed #333; /* ticket border style */
}

/* Close Button */
.detail-modal-close {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 24px;
  cursor: pointer;
  font-weight: bold;
  color: #333;
}

/* ---------- Header ---------- */
.detail-modal-header {
  text-align: center;
  margin-bottom: 15px;
}

.detail-modal-header h2 {
  font-size: 20px;
  margin: 0;
}

.detail-modal-header .detail-modal-value {
  display: block;
  font-size: 14px;
  color: #555;
  margin-top: 5px;
}

.status-label {
  display: inline-block;
  margin-top: 5px;
  font-weight: bold;
  color: #fff;
  background-color: #28a745; /* approved green */
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
}

/* Change color based on status if needed */
.status-label[data-status="pending"] {
  background-color: #ffc107;
}
.status-label[data-status="rejected"] {
  background-color: #dc3545;
}

/* ---------- Design Info ---------- */
.design-info {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 15px;
}

.design-image-container {
  flex: 0 0 80px;
  position: relative;
}

.design-image {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.design-buttons {
  display: flex;
  flex-direction: column;
  gap: 5px;
  margin-top: 5px;
}

.design-buttons button {
  font-size: 10px;
  padding: 3px 5px;
  cursor: pointer;
  border: none;
  border-radius: 3px;
  background-color: #007bff;
  color: #fff;
}

.design-details {
  flex: 1;
  font-size: 14px;
  line-height: 1.4;
}

/* ---------- Items Table ---------- */
.items-section {
  margin-top: 10px;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.items-table th,
.items-table td {
  text-align: left;
  padding: 6px 8px;
  border-bottom: 1px solid #ccc;
}

.items-table th {
  background-color: #f5f5f5;
  font-weight: bold;
}

.shirt-item {
  display: flex;
  justify-content: space-between;
}

.card-content {
    position: relative; /* allow absolute positioning inside */
    padding-bottom: 30px; /* space for icon */
}

.bottom-right-details-icon {
    position: absolute;
    bottom: 10px;
    right: 10px;
    font-size: 18px;
    color: #000; /* black icon */
    cursor: pointer;
    transition: transform 0.2s, color 0.2s;
}

.bottom-right-details-icon:hover {
    color: #333; /* subtle darken on hover */
    transform: scale(1.2);
}

/* ---------- Price & Subtotal Section ---------- */
.price-section {
    margin-top: 15px;
    padding: 10px 0;
    display: flex;
    justify-content: space-between;
    font-size: 14px;
}

.price-section .price-detail {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.price-section .detail-modal-label {
    font-weight: bold;
    color: #555;
}

.price-section .detail-modal-price-value,
.price-section .detail-modal-subtotal-value {
    color: #333;
    font-weight: bold;
}

/* ---------- Ticket Lines ---------- */
hr {
  border: none;
  border-top: 1px dashed #999;
  margin: 10px 0;
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
    <a href="processing-order" class="tab-button active">Processing</a>
    <a href="to-ship-order" class="tab-button ">To Ship</a>
    <a href="completed-order" class="tab-button">Completed</a>
    <a href="cancelled-orders" class="tab-button">Cancelled</a>
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
                <!-- Processing -->
                <div class="processing-search" style="display: block;">
                    <input type="text"
                           id="ProcessingSearchInput"
                           class="search-input"
                           placeholder="Search by Ticket #">
                    <span class="search-icon">&#128269;</span>
                </div>
            </div>
        </div>

        <div class="quotes-container processing-orders-container" id="processing-orders-container" style="display:block;">
            <?php
            include '../db_connection.php';

            // Fetch processing orders for the logged-in user
            $user_id = $_SESSION['user_id'] ?? null;
            $has_orders = false;
            $orders = [];

            if ($user_id) {
                $sql = "SELECT o.*, u.name, u.phone_number
                    FROM orders o
                    JOIN users u ON o.user_id = u.id
                    WHERE o.user_id = ? 
                    AND o.status = 'processing'
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

                                                                    <!-- Bottom-right details icon -->
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
                                        data-items='<?= json_encode($shirtItems, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                      >
                                    </i>
                                    
                            <div class="card-actions">
                                <div class="button-group">
                                    <button class="view-details-btn processing-order-btn" 
                                        data-order-id="<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                        data-order-ticket="<?= htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') ?>" 
                                        data-order-created-at="<?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-order-pricing="<?= htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-order-quantity="<?= htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-order-subtotal="<?= htmlspecialchars($order['pricing'] * $order['quantity'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-admin-approved-date="<?= htmlspecialchars($order['admin_approved_date'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-user-approved-date="<?= htmlspecialchars($order['user_approved_date'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-processing-date="<?= htmlspecialchars($order['processing_date'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-is-for-pickup="<?= htmlspecialchars($order['is_for_pickup'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-pickup-date="<?= htmlspecialchars($order['pickup_date'], ENT_QUOTES, 'UTF-8') ?>"
                                        >
                                        <i class="fas fa-eye"></i> View
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

    <div id="processingProcessModal" class="order-process-modal">
        <div class="order-process-modal-content">
            <span class="order-process-close-btn" onclick="closeProcessingProcessModal()">&times;</span>
            <h2 id="processingProcessTitle" class="order-process-title">Ticket #12345 Process Details</h2>
            
            <div id="processingProcessSteps" class="order-process-steps-container">
                <!-- Quote Placed Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">1</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="processingQuotePlacedTitle" class="order-step-title">Quote Placed</div>
                        <div id="processingQuotePlacedDesc" class="order-step-description">Your order request has been received</div>
                        <div id="processingQuotePlacedDate" class="order-step-date">Jan 15, 2023</div>
                    </div>
                </div>
                
                <!-- Agreed Price Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">2</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="processingAdminApprovedTitle" class="order-step-title">Agreed Price</div>
                        <div class="order-step-description">
                            <div id="processingOrderSummary" class="order-summary-details">
                                <p id="processingUnitPrice">Unit Price: $10.00</p>
                                <p id="processingQuantity">Quantity: 5</p>
                                <p id="processingSubtotal" class="order-subtotal">Subtotal: $50.00</p>
                            </div>
                        </div>
                        <div id="processingAdminApprovedDate" class="order-step-date">Jan 16, 2023</div>
                    </div>
                </div>
                <!-- Ready for Pickup Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">3</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="processingReadyTitle" class="order-step-title">Item Has Been Picked Up</div>
                        <div id="processingReadyDesc" class="order-step-description">Items is on process</div>
                        <div id="processingReadyDate" class="order-step-date">Pending</div>
                    </div>
                </div>
                
                <!-- Processing Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">4</div>
                    <div class="order-step-connector-current"></div>
                    <div class="order-step-content">
                        <div id="processingProcessingTitle" class="order-step-title">Processing</div>
                        <div id="processingProcessingDesc" class="order-step-description">Your items are currently being prepared</div>
                        <div id="processingProcessingDate" class="order-step-date">Jan 17, 2023</div>
                    </div>
                </div>
                
                <!-- To Ship Step -->
                    <div class="order-step">
                        <div class="order-step-number">5</div>
                        <div class="order-step-connector-pending"></div>
                        <div class="order-step-content">
                            <div id="processingCompletedTitle" class="order-step-title">To Ship</div>
                            <div id="processingCompletedDesc" class="order-step-description">Items will be ship soon</div>
                            <div id="processingCompletedDate" class="order-step-date">Pending</div>
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


    function openProcessingOrderModal(event) {
        const button = event.currentTarget;
        const ticket = button.getAttribute('data-order-ticket');
        const createdAt = button.getAttribute('data-order-created-at');
        const pricing = button.getAttribute('data-order-pricing');
        const quantity = button.getAttribute('data-order-quantity');
        const subtotal = button.getAttribute('data-order-subtotal');
        const adminApprovedDate = button.getAttribute('data-admin-approved-date');
        const userApprovedDate = button.getAttribute('data-user-approved-date');
        const processingDate = button.getAttribute('data-processing-date');
        const isForPickup = button.getAttribute('data-is-for-pickup');
        const pickupDate = button.getAttribute('data-pickup-date'); // pickup_date is now passed here

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

        document.getElementById('processingProcessTitle').textContent = `Ticket #${ticket} Process Details`;
        document.getElementById('processingQuotePlacedDate').textContent = formatDate(createdAt);
        document.getElementById('processingUnitPrice').textContent = `Unit Price: ₱${parseFloat(pricing).toFixed(2)}`;
        document.getElementById('processingQuantity').textContent = `Quantity: ${quantity}`;
        document.getElementById('processingSubtotal').textContent = `Subtotal: ₱${parseFloat(subtotal).toFixed(2)}`;
        document.getElementById('processingAdminApprovedDate').textContent = formatDate(userApprovedDate);
        document.getElementById('processingProcessingDate').textContent = formatDate(processingDate);
        
        // Set the pickup date value to the step
        document.getElementById('processingReadyDate').textContent = formatDate(pickupDate);

        // Future steps
        document.getElementById('processingCompletedDate').textContent = 'Pending';

        document.getElementById('processingProcessModal').setAttribute('data-ticket', ticket);
        document.getElementById('processingProcessModal').style.display = 'flex';
    }

    function closeProcessingProcessModal() {
        document.getElementById('processingProcessModal').style.display = 'none';
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to view buttons
        const viewButtons = document.querySelectorAll('.processing-order-btn');
        viewButtons.forEach(button => {
            button.addEventListener('click', openProcessingOrderModal);
        });

        // Close modal when clicking outside
        document.getElementById('processingProcessModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeProcessingProcessModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeProcessingProcessModal();
            }
        });

        // Search functionality
        const searchInput = document.getElementById('ProcessingSearchInput');
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