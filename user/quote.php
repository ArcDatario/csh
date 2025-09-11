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
    <link rel="icon" href="../assets/images/tshirt.png" type="image/x-icon">
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
                 <img src="../assets/images/icons/tshirt.png" alt="" style="height: 45px; width: 35px;">
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
    $sql = "SELECT * FROM orders WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $has_orders = true;
        while ($order = $result->fetch_assoc()) {
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
    
    <div class="form-group" style="display: flex; gap: 10px;">
        <div style="flex: 7;">
            <label for="printType">Print Type</label>
            <select id="printType" name="printType" class="form-control" style="width: 100%;" required>
                <option value="">Select print type</option>
                <option value="Direct to Film Print">Direct to Film Print</option>
                <option value="Screen Printing">Screen Printing</option>
                <option value="Emboss Print">Emboss Print</option>
                <option value="Hi-Density Print">Hi-Density Print</option>
                <option value="Glitters Print">Glitters Print</option>
                <option value="Silk Screen Print">Silk Screen Print</option>
            </select>
        </div>
        <div style="flex: 3;">
            <label for="quantity">Qty (Min. 500)</label>
            <input type="number" id="quantity" name="quantity" class="form-control" min="1" placeholder="Quantity" style="width: 100%; background-color:transparent;" required>
        </div>
    </div>
    
   <input type="text" name="address" id="address" class="address" 
       value="<?php echo htmlspecialchars($full_address, ENT_QUOTES, 'UTF-8'); ?>" readonly style="display:none;">

    <div class="form-group">
        <label for="note">Note</label>
        <textarea id="note" name="note" class="form-control note-input" rows="2" placeholder="Enter any additional notes or instructions" style="background-color:transparent;"></textarea>
    </div>
    
    <button type="submit" class="submit-btn">
        <i class="fas fa-paper-plane"></i> Submit Quote
    </button>
</form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/quote.js"></script>



    <script>
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
            closeOrderProcessModal();
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
    </script>


</body>
</html>