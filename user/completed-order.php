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
                $sql = "SELECT * FROM orders WHERE user_id = ? AND status = 'completed' ORDER BY created_at DESC";
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/quote.js"></script>

    <script>
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
    </script>
</body>
</html>