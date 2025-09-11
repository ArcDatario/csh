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
    <title>CSH Enterprises | Approved Orders</title>
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
        
        /* Toast notification styles */
       
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
    <a href="quote" class="tab-button" >Pending</a>
    <a href="approved-order" class="tab-button  active" >Approved</a>
    <a href="to-pickup-order" class="tab-button" >To Pick Up</a>
    <a href="processing-order" class="tab-button">Processing</a>
    <a href="to-ship-order" class="tab-button">To Ship</a>
    <a href="completed-order" class="tab-button">Completed</a>
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
        <!-- Approved -->
        <div class="approved-search" style="display: block;">
          <input type="text"
                 id="ApproveSearchInput"
                 class="search-input"
                 placeholder="Search by Ticket #">
          <span class="search-icon">&#128269;</span>
        </div>
      </div>
    </div>

        <div class="quotes-container approved-orders-container" id="approved-orders-container" style="display:block;">
            <?php

            // Fetch approved orders for the logged-in user
            $user_id = $_SESSION['user_id'] ?? null;
            $has_orders = false;
            $orders = [];

            if ($user_id) {
                $sql = "SELECT * FROM orders WHERE user_id = ? AND status = 'approved' ORDER BY created_at DESC";
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

            <!-- HTML Structure -->
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
                                    <button class="view-details-btn approved-order-btn" 
                                        data-approved-id="<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                        data-approved-ticket="<?= htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') ?>" 
                                        data-approved-created-at="<?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-approved-admin="<?= htmlspecialchars($order['is_approved_admin'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-pricing="<?= htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-quantity="<?= htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-subtotal="<?= htmlspecialchars($order['pricing'] * $order['quantity'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-admin-approved-date="<?= htmlspecialchars($order['admin_approved_date'], ENT_QUOTES, 'UTF-8') ?>"
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

    <div id="approvedOrderProcessModal" class="order-process-modal">
        <div class="order-process-modal-content">
            <span class="order-process-close-btn" onclick="closeApprovedOrderProcessModal()">&times;</span>
            <h2 id="approvedOrderProcessTitle" class="order-process-title">Ticket #12345 Process Details</h2>
            
            <div id="approvedOrderProcessSteps" class="order-process-steps-container">
                <!-- Quote Placed Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">1</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="approvedQuotePlacedTitle" class="order-step-title">Quote Placed</div>
                        <div id="approvedQuotePlacedDesc" class="order-step-description">Your order request has been received</div>
                        <div id="approvedQuotePlacedDate" class="order-step-date">Jan 15, 2023</div>
                    </div>
                </div>
                
                <!-- Admin Approved Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">2</div>
                    <div class="order-step-connector-current"></div>
                    <div class="order-step-content">
                        <div id="approvedAdminApprovedTitle" class="order-step-title">Admin Approved</div>
                        <div class="order-step-description">
                            <div id="approvedOrderSummary" class="order-summary-details">
                                <p id="approvedUnitPrice">Unit Price: $10.00</p>
                                <p id="approvedQuantity">Quantity: 5</p>
                                <p id="approvedSubtotal" class="order-subtotal">Subtotal: $50.00</p>
                            </div>
                        </div>
                        <div id="approvedAdminApprovedDate" class="order-step-date">Jan 16, 2023</div>
                    </div>
                    <div class="order-approval-actions">
                        <div class="order-approval-buttons">
                            <button class="order-agree-btn" id="orderAgreeBtn">
                                <i class="fas fa-check-circle"></i> Agree
                            </button>
                            <button class="order-cancel-btn" id="orderCancelBtn">
                                <i class="fas fa-times-circle"></i> Reject
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Ready for Pickup/Delivery Step -->
                <div class="order-step order-step-current">
                    <div class="order-step-number">4</div>
                    <div class="order-step-connector-pending"></div>
                    <div class="order-step-content">
                        <div id="approvedReadyTitle" class="order-step-title">Ready</div>
                        <div id="approvedReadyDesc" class="order-step-description">Your order is ready for pickup</div>
                        <div id="approvedReadyDate" class="order-step-date">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast" style="display: none;">
        <span class="toast-icon" id="toastIcon"></span>
        <div class="toast-message" id="toastMessage"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/quote.js"></script>

   <script>
    // Toast notification function
    function showToast(title, message, type = 'info', duration = 3000) {
        const toast = document.getElementById('toast');
        const toastIcon = document.getElementById('toastIcon');
        const toastMessage = document.getElementById('toastMessage');
        
        // Set content
        toastMessage.innerHTML = `<strong>${title}</strong><br>${message}`;
        
        // Set style based on type
        toast.className = 'toast';
        toast.classList.add(type);
        
        // Set icon based on type
        switch(type) {
            case 'success':
                toastIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
                break;
            case 'error':
                toastIcon.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
                break;
            default:
                toastIcon.innerHTML = '<i class="fas fa-info-circle"></i>';
        }
        
        // Show toast
        toast.style.display = 'flex';
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Hide after duration
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.style.display = 'none', 300);
        }, duration);
    }

    function openApprovedOrderModal(event) {
        const button = event.currentTarget;
        const ticket = button.getAttribute('data-approved-ticket');
        const createdAt = button.getAttribute('data-approved-created-at');
        const pricing = button.getAttribute('data-pricing');
        const quantity = button.getAttribute('data-quantity');
        const subtotal = button.getAttribute('data-subtotal');
        const adminApprovedDate = button.getAttribute('data-admin-approved-date');

        // Format the dates
        const date = new Date(createdAt);
        const formattedDate = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });

        // Format admin approved date if available
        let formattedAdminApprovedDate = 'N/A';
        if (adminApprovedDate && adminApprovedDate !== 'null' && adminApprovedDate !== '') {
            const adminDate = new Date(adminApprovedDate);
            if (!isNaN(adminDate)) {
                formattedAdminApprovedDate = adminDate.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
            }
        }

        document.getElementById('approvedOrderProcessTitle').textContent = `Ticket #${ticket} Process Details`;
        document.getElementById('approvedQuotePlacedDate').textContent = formattedDate;
        document.getElementById('approvedUnitPrice').textContent = `Unit Price: ₱${parseFloat(pricing).toFixed(2)}`;
        document.getElementById('approvedQuantity').textContent = `Quantity: ${quantity}`;
        document.getElementById('approvedSubtotal').textContent = `Subtotal: ₱${parseFloat(subtotal).toFixed(2)}`;
        document.getElementById('approvedAdminApprovedDate').textContent = formattedAdminApprovedDate;

        document.getElementById('approvedOrderProcessModal').setAttribute('data-ticket', ticket);
        document.getElementById('approvedOrderProcessModal').style.display = 'flex';
    }

    function closeApprovedOrderProcessModal() {
        document.getElementById('approvedOrderProcessModal').style.display = 'none';
    }

    // Confirmation modal logic
    function showConfirmationModal(message, onConfirm) {
        let modal = document.getElementById('confirmationModal');
        
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'confirmationModal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0,0,0,0.5);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.2s ease;
                backdrop-filter: blur(2px);
            `;
            
            modal.innerHTML = `
                <div style="
                    background: #fff;
                    padding: 32px;
                    border-radius: 12px;
                    max-width: 90vw;
                    width: 380px;
                    text-align: center;
                    box-shadow: 0 4px 24px rgba(0,0,0,0.1);
                    transform: translateY(10px);
                    transition: transform 0.2s ease;
                ">
                    <h3 style="
                        margin: 0 0 16px 0;
                        font-size: 1.25rem;
                        color: #333;
                        font-weight: 600;
                    ">Confirm Action</h3>
                    <p style="
                        margin: 0 0 32px 0;
                        color: #666;
                        line-height: 1.5;
                    ">${message}</p>
                    <div style="
                        display: flex;
                        gap: 12px;
                        justify-content: center;
                    ">
                        <button id="confirmCancelBtn" style="
                            padding: 12px 24px;
                            border: 1px solid #ddd;
                            background: #f8f9fa;
                            color: #333;
                            border-radius: 8px;
                            cursor: pointer;
                            font-weight: 500;
                            transition: all 0.2s ease;
                        ">Cancel</button>
                        <button id="confirmProceedBtn" style="
                            padding: 12px 24px;
                            border: none;
                            background: #dc3545;
                            color: white;
                            border-radius: 8px;
                            cursor: pointer;
                            font-weight: 500;
                            transition: all 0.2s ease;
                        ">Proceed</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            document.getElementById('confirmCancelBtn').addEventListener('click', () => {
                hideConfirmationModal();
            });
        }
        
        document.getElementById('confirmProceedBtn').onclick = onConfirm;
        
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            modal.querySelector('div').style.transform = 'translateY(0)';
        }, 10);
    }

    function hideConfirmationModal() {
        const modal = document.getElementById('confirmationModal');
        if (modal) {
            modal.style.opacity = '0';
            modal.querySelector('div').style.transform = 'translateY(10px)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 200);
        }
    }

        // Function to handle order approval/rejection
    function userApproveOrder(ticketNumber, action) {
        // Hide confirmation modal first
        hideConfirmationModal();
        
        fetch('functions/user_approve_order.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ticketNumber, action })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success', 3500);
                closeApprovedOrderProcessModal();
                
                // Save the tab to localStorage before reloading
                localStorage.setItem('activeTab', 'pickup-orders-container');
                
                // Reload after the toast disappears
                setTimeout(() => location.reload(), 3500);
            } else {
                showToast('Error', data.message, 'error', 3500);
            }
        })
        .catch(() => showToast('Error', 'An error occurred. Please try again.', 'error', 3500));
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to view buttons
        const viewButtons = document.querySelectorAll('.approved-order-btn');
        viewButtons.forEach(button => {
            button.addEventListener('click', openApprovedOrderModal);
        });

        // Add event listeners to agree and reject buttons
        document.getElementById('orderAgreeBtn').addEventListener('click', function() {
            const ticket = document.getElementById('approvedOrderProcessModal').getAttribute('data-ticket');
            if (!ticket) {
                showToast('Error', 'Ticket number not found.', 'error');
                return;
            }
            showConfirmationModal('Are you sure you want to agree to this quote?', function() {
                userApproveOrder(ticket, 'agree');
            });
        });

        document.getElementById('orderCancelBtn').addEventListener('click', function() {
            const ticket = document.getElementById('approvedOrderProcessModal').getAttribute('data-ticket');
            if (!ticket) {
                showToast('Error', 'Ticket number not found.', 'error');
                return;
            }
            showConfirmationModal('Are you sure you want to reject this quote?', function() {
                userApproveOrder(ticket, 'reject');
            });
        });

        // Close modal when clicking outside
        document.getElementById('approvedOrderProcessModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeApprovedOrderProcessModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeApprovedOrderProcessModal();
            }
        });

        // Search functionality
        const searchInput = document.getElementById('ApproveSearchInput');
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