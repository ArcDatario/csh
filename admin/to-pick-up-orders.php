<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Safely check for Field Manager role and redirect
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
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Dashboard</title>
    <link rel="icon" href="assets/images/analysis.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/quote-modal.css">

   
<style>
.table-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.tab-btn {
    padding: 8px 16px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    font-weight: 500;
    color: #666;
}

.tab-btn.active {
    color: #333;
    border-bottom-color: #4CAF50;
    font-weight: 600;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
</style>
</head>

<body>
    <div class="container">
        
       <button class="mobile-menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

        <?php include "includes/sidebar.php";?>
    
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Main Content -->
        <main class="main">
            <header class="header">
                <h1 class="header-dashboard">Dashboard</h1>
                
                <div class="user-menu">
                <div class="theme-toggle" id="themeToggle">
                <span style="margin-right:8px;">Dark Mode</span>
                <i class="fas fa-moon"></i>
            </div>
                    
          <?php include "includes/notification.php";?>

    </div>
                    
                    <div class="user-avatar">Csh</div>
                </div>
            </header>
            
            <!-- Table -->
            <section class="table-card fade-in">
               <div class="table-header">
    <div class="table-tabs">
        <button class="tab-btn active" data-tab="to-pickup">To Pickup</button>
        <button class="tab-btn" data-tab="on-pickup">On Pickup</button>
    </div>
    <div class="table-actions">
        <button class="btn btn-outline">
            <i class="fas fa-filter"></i>
            <span>Filter</span>
        </button>
    </div>
</div>
                
                <div id="to-pickup-table" class="table-responsive tab-content active">
    <table id="pickup-table">
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
        <?php
        require_once '../db_connection.php';

        // Fetch orders ready for pickup (status = 'to-pick-up' AND is_for_pickup = 'no')
        $sql = "SELECT orders.id,orders.user_id,orders.ticket, orders.design_file, orders.print_type,orders.note, orders.address, orders.quantity, orders.created_at, orders.status, orders.pricing, orders.subtotal, users.name, users.phone_number, users.email 
                FROM orders 
                INNER JOIN users ON orders.user_id = users.id 
                WHERE orders.status = 'to-pick-up' AND orders.is_for_pickup = 'no'
                ORDER BY orders.created_at DESC";
        $result = $conn->query($sql);
        ?>
        <tbody id="pickup-table-body">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <div class="user-cell">
                                <img src="../user/<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>" alt="file design" width="50" height="50">
                                <span><?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <span class="status status-success">
                                <?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-outline view-pickup-modal" 
                                    data-id="<?php echo htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-user-id="<?php echo htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-ticket="<?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-design="<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-mobile="<?php echo htmlspecialchars($order['phone_number'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-name="<?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-print-type="<?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-quantity="<?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-date="<?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?>"
                                    data-status="<?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-note="<?php echo htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-address="<?php echo htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-email="<?php echo htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-pricing="<?php echo htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-subtotal="<?php echo htmlspecialchars($order['subtotal'], ENT_QUOTES, 'UTF-8'); ?>">
                                View
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No orders ready for pickup</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- On Pickup Table -->
<div id="on-pickup-table" class="table-responsive tab-content">
    <table id="onpickup-table">
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>Design</th>
                <th>Print Type</th>
                <th>Quantity</th>
                <th>Date</th>
                <th>Attempt</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <?php
        // Fetch orders on pickup (status = 'to-pick-up' AND is_for_pickup = 'yes')
        $sql = "SELECT orders.id,orders.user_id,orders.ticket, orders.design_file, orders.print_type,orders.note, orders.address, orders.quantity, orders.created_at, orders.status, orders.pricing, orders.subtotal,orders.pickup_attempt, users.name, users.phone_number, users.email 
                FROM orders 
                INNER JOIN users ON orders.user_id = users.id 
                WHERE orders.status = 'to-pick-up' AND orders.is_for_pickup = 'yes'
                ORDER BY orders.created_at DESC";
        $result = $conn->query($sql);
        ?>
        <tbody id="onpickup-table-body">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <div class="user-cell">
                                <img src="../user/<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>" alt="file design" width="50" height="50">
                                <span><?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                         <td>
                           <?php echo htmlspecialchars($order['pickup_attempt'], ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td>
                            <span class="status status-warning">
                                On Pickup
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-outline view-pickup-modal" 
                                    data-id="<?php echo htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-user-id="<?php echo htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-ticket="<?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-design="<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-mobile="<?php echo htmlspecialchars($order['phone_number'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-name="<?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-print-type="<?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-quantity="<?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-date="<?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?>"
                                    data-status="<?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-note="<?php echo htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-address="<?php echo htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-email="<?php echo htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-pricing="<?php echo htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-subtotal="<?php echo htmlspecialchars($order['subtotal'], ENT_QUOTES, 'UTF-8'); ?>">
                                View
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No orders on pickup</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and content
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show corresponding content
            const tabId = this.getAttribute('data-tab');
            if (tabId === 'to-pickup') {
                document.getElementById('to-pickup-table').classList.add('active');
            } else if (tabId === 'on-pickup') {
                document.getElementById('on-pickup-table').classList.add('active');
            }
        });
    });
});
</script>

            </section>
        </main>
    </div>
  
    <!-- Pickup Modal -->
    <div id="pickupModal" class="quote-modal">
        <div class="quote-modal-content">
            <span class="quote-modal-close">&times;</span>
            <h2>Order Ready for Pickup</h2>
            <div class="quote-modal-body">
                <!-- Group 1: Ticket and Customer in one row -->
                <div class="quote-modal-row grouped-row">
                    <div class="grouped-item">
                        <span class="quote-modal-label">Ticket #:</span>
                        <span id="pickup-modal-ticket" class="quote-modal-value"></span>
                    </div>
                    <div class="grouped-item">
                        <span class="quote-modal-label">Customer:</span>
                        <span id="pickup-modal-name" class="quote-modal-value"></span>
                    </div>
                </div>
                
                <!-- Group 2: Image with buttons and details -->
                <div class="quote-modal-row grouped-row-2">
                    <div class="grouped-item">
                        <span class="quote-modal-label">Design:</span>
                        <div class="design-image-container">
                            <img id="pickup-modal-design" src="" alt="Design" class="design-image">
                           <div class="design-buttons">
                                <button class="view-design-btn">View</button>
                                <button class="download-design-btn">Download</button>
                            </div>
                        </div>
                    </div>
                    <div class="grouped-item details-column">
                        <div class="detail-row">
                            <span class="quote-modal-label">Print Type:</span>
                            <span id="pickup-modal-print-type" class="quote-modal-value"></span>
                        </div>
                        <div class="detail-row">
                            <span class="quote-modal-label">Quantity:</span>
                            <span id="pickup-modal-quantity" class="quote-modal-value"></span>
                        </div>
                        <div class="detail-row">
                            <span class="quote-modal-label">Mobile #:</span>
                            <span id="pickup-modal-mobile" class="quote-modal-value"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Note -->
                <div class="quote-modal-row">
                    <span class="quote-modal-label">Note:</span>
                    <span id="pickup-modal-note" class="quote-modal-value note-value"></span>
                </div>
                
                <!-- Group 3: Date and Status -->
                <div class="quote-modal-row grouped-row">
                    <div class="grouped-item">
                        <span class="quote-modal-label">Date:</span>
                        <span id="pickup-modal-date" class="quote-modal-value"></span>
                    </div>
                    <div class="grouped-item">
                        <span class="quote-modal-label">Status:</span>
                        <span id="pickup-modal-status" class="quote-modal-value"></span>
                    </div>
                </div>
                
                <!-- Address -->
                <div class="quote-modal-row">
                    <span class="quote-modal-label">Address:</span>
                    <span id="pickup-modal-address" class="quote-modal-value address-value"></span>
                </div>
                
                <!-- Pricing Information -->
                <div class="quote-modal-row">
                    <span class="quote-modal-label">Unit Price:</span>
                    <span id="pickup-modal-pricing" class="quote-modal-value">₱0.00</span>
                </div>
                
                <div class="quote-modal-row">
                    <span class="quote-modal-label">Subtotal:</span>
                    <span id="pickup-modal-subtotal" class="quote-modal-value">₱0.00</span>
                </div>
            </div>
            <div class="quote-modal-footer">
                <input type="hidden" id="pickup-modal-id">
                <input type="hidden" id="pickup-modal-user-id">
                <input type="hidden" id="pickup-modal-email">
                <input type="hidden" id="pickup-modal-ticket">
                <input type="hidden" id="pickup-modal-quantity">
                <input type="hidden" id="pickup-modal-pricing">
                <input type="hidden" id="pickup-modal-subtotal">
                <input type="hidden" id="pickup-modal-address">
                
                <button id="pickup-modal-confirm" class="quote-modal-btn btn-primary">Confirm Pickup</button>
                <button id="pickup-modal-close" class="quote-modal-btn btn-outline">Close</button>
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

    <script src="assets/js/script.js"></script>
    <script>
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
            const imgSrc = e.target.closest('.design-image-container').querySelector('img').src;
            document.getElementById('viewed-image').src = imgSrc;
            imageViewerModal.style.display = 'block';
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
            const container = e.target.closest('.design-image-container');
            const imgSrc = container.querySelector('img').src;
            const ticket = document.getElementById('pickup-modal-ticket').textContent;
            const printType = document.getElementById('pickup-modal-print-type').textContent;
            
            // Extract filename from URL
            const filename = imgSrc.split('/').pop();
            const extension = filename.split('.').pop();
            
            // Create download link
            const link = document.createElement('a');
            link.href = imgSrc;
            link.download = `${ticket}-${printType.toLowerCase().replace(/ /g, '-')}.${extension}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });
    </script>

    <script>
    // Get DOM elements
    const pickupModal = document.getElementById('pickupModal');
    const pickupModalClose = document.querySelector('.quote-modal-close');
    const confirmPickupBtn = document.getElementById('pickup-modal-confirm');
    const closePickupBtn = document.getElementById('pickup-modal-close');

    // View button click handler
    function handlePickupViewButtonClick() {
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
        const email = this.getAttribute('data-email');
        const pricing = this.getAttribute('data-pricing');
        const subtotal = this.getAttribute('data-subtotal');
        
        // Store data in modal
        pickupModal.setAttribute('data-current-id', id);
        document.getElementById('pickup-modal-id').value = id;
        document.getElementById('pickup-modal-user-id').value = userId;
        document.getElementById('pickup-modal-email').value = email;
        document.getElementById('pickup-modal-ticket').value = ticket;
        document.getElementById('pickup-modal-quantity').value = quantity;
        document.getElementById('pickup-modal-pricing').value = pricing;
        document.getElementById('pickup-modal-subtotal').value = subtotal;
        document.getElementById('pickup-modal-address').value = address;
        
        // Populate modal fields
        document.getElementById('pickup-modal-ticket').textContent = ticket;
        document.getElementById('pickup-modal-name').textContent = name;
        document.getElementById('pickup-modal-design').src = '../user/' + design;
        document.getElementById('pickup-modal-print-type').textContent = printType;
        document.getElementById('pickup-modal-quantity').textContent = quantity;
        document.getElementById('pickup-modal-date').textContent = date;
        document.getElementById('pickup-modal-status').textContent = status;
        document.getElementById('pickup-modal-note').textContent = note || 'N/A';
        document.getElementById('pickup-modal-address').textContent = address || 'N/A';
        document.getElementById('pickup-modal-mobile').textContent = mobile || 'N/A';
        document.getElementById('pickup-modal-pricing').textContent = '₱' + parseFloat(pricing).toFixed(2);
        document.getElementById('pickup-modal-subtotal').textContent = '₱' + parseFloat(subtotal).toFixed(2);
        
        // Show modal
        pickupModal.style.display = 'block';
    }

    // Confirm pickup handler
    function handleConfirmPickup() {
        const id = pickupModal.getAttribute('data-current-id');
        const userId = document.getElementById('pickup-modal-user-id').value;
        const email = document.getElementById('pickup-modal-email').value;
        const ticket = document.getElementById('pickup-modal-ticket').value;
        const quantity = document.getElementById('pickup-modal-quantity').value;
        const pricing = document.getElementById('pickup-modal-pricing').value;
        const subtotal = document.getElementById('pickup-modal-subtotal').value;
        const address = document.getElementById('pickup-modal-address').value;

        // Show loading state
        const originalText = confirmPickupBtn.textContent;
        confirmPickupBtn.disabled = true;
        confirmPickupBtn.textContent = 'Processing...';

        fetch('functions/confirm_pickup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: id,
                user_id: userId,
                email: email,
                ticket: ticket,
                quantity: quantity,
                pricing: pricing,
                subtotal: subtotal,
                address: address
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                pickupModal.style.display = 'none';
                refreshPickupTable(); // Refresh table after successful confirmation
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'An error occurred while confirming pickup', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            confirmPickupBtn.disabled = false;
            confirmPickupBtn.textContent = originalText;
        });
    }

    // Modal close handlers
    function closePickupModal() {
        pickupModal.style.display = 'none';
    }

    function handleWindowClick(event) {
        if (event.target === pickupModal) {
            closePickupModal();
        }
    }

    // Table refresh functionality
    function refreshPickupTable() {
        fetch('functions/get_pickup_orders.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('pickup-table-body').innerHTML = data;
                attachEventListeners(); // Reattach event listeners after refresh
            })
            .catch(error => console.error('Error refreshing table:', error));
    }

    // Attach all event listeners
    function attachEventListeners() {
        // View buttons
        document.querySelectorAll('.view-pickup-modal').forEach(button => {
            button.addEventListener('click', handlePickupViewButtonClick);
        });
        
        // Modal close
        pickupModalClose.addEventListener('click', closePickupModal);
        closePickupBtn.addEventListener('click', closePickupModal);
        window.addEventListener('click', handleWindowClick);
        
        // Confirm button
        confirmPickupBtn.addEventListener('click', handleConfirmPickup);
    }

    // Initialize
    function init() {
        attachEventListeners();
        refreshPickupTable();
        
        // Set up periodic refresh (every 5 seconds)
        setInterval(refreshPickupTable, 5000);
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
    </script>
</body>
</html>