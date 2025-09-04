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
   

    <?php include "includes/link-css.php";?>

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
/* On Pickup Modal Specific Styles */
.btn-warning {
    background-color: #ffc107;
    color: #212529;
    border: none;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
    border: none;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
    border: none;
}

.quote-modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.address-value {
    display: inline-block;
    max-width: 100%;
    word-break: break-word;
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
                <div class="theme-toggle" id="themeToggle" style="display:none;">
                <span style="margin-right:8px;" style="display:none;">Dark Mode</span>
                <i class="fas fa-moon"></i>
            </div>
                    
          <?php include "includes/notification.php";?>

    </div>
                    
                   <?php include "includes/profile.php";?>
                </div>
            </header>
            
            <!-- Table -->
            <section class="table-card fade-in">
               <div class="table-header">
    <div class="table-tabs">
        <button class="tab-btn active" data-tab="to-pickup">To Pickup</button>
        <button class="tab-btn" data-tab="on-pickup">On Pickup</button>
        <button class="tab-btn" data-tab="to-ship">To Ship</button>
         <button class="tab-btn" data-tab="completed">Completed</button>
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
        <?php while ($order = $result->fetch_assoc()): 
            // Determine the appropriate thumbnail based on file extension
            $designFile = $order['design_file'];
            $fileExtension = strtolower(pathinfo($designFile, PATHINFO_EXTENSION));
            
            // Define the base path for design files
            $designFilesBasePath = '../user/';
            
            if ($fileExtension === 'psd') {
                $thumbnail = "../photoshop.png";
            } elseif ($fileExtension === 'pdf') {
                $thumbnail = "../pdf.png";
            } elseif ($fileExtension === 'ai') {
                $thumbnail = "../illustrator.png";
            } else {
                // For image files, use the actual file with proper path checking
                $thumbnailPath = $designFilesBasePath . $designFile;
                
                // Check if file exists, otherwise use a placeholder
                if (file_exists($thumbnailPath)) {
                    $thumbnail = $thumbnailPath;
                } else {
                    $thumbnail = "../placeholder-image.png"; // Create this placeholder image
                }
            }
        ?>
            <tr>
                <td><?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <div class="user-cell">
                        <img src="<?php echo $thumbnail; ?>" alt="file design" width="50" height="50" onerror="this.onerror=null; this.src='../placeholder-image.png';">
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
<?php include "includes/tables/onpickup-table.php"; ?>
<?php include "includes/tables/to-ship-table.php"; ?>
<?php include "includes/tables/completed-table.php"; ?>
            </section>
        </main>
    </div>
    <!-- On Pickup Modal -->
<div id="onPickupModal" class="quote-modal">
    <div class="quote-modal-content">
        <span class="quote-modal-close">&times;</span>
        <h2>Order On Pickup</h2>
        <div class="quote-modal-body">
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Ticket #:</span>
                    <span id="onpickup-modal-ticket" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Attempt:</span>
                    <span id="onpickup-modal-attempt" class="quote-modal-value"></span>
                </div>
            </div>
            
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Customer:</span>
                    <span id="onpickup-modal-name" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Mobile #:</span>
                    <span id="onpickup-modal-mobile" class="quote-modal-value"></span>
                </div>
            </div>
            
            <div class="quote-modal-row">
                <span class="quote-modal-label">Address:</span>
                <span id="onpickup-modal-address" class="quote-modal-value address-value"></span>
            </div>
            
            <div class="quote-modal-row">
                <span class="quote-modal-label">Last Pickup Attempt:</span>
                <span id="onpickup-modal-last-attempt" class="quote-modal-value"></span>
            </div>
        </div>
        <div class="quote-modal-footer">
            <input type="hidden" id="onpickup-modal-id">
            <input type="hidden" id="onpickup-modal-user-id">
            <input type="hidden" id="onpickup-modal-email">
            <input type="hidden" id="onpickup-modal-ticket">
            <input type="hidden" id="onpickup-modal-attempt">
            
        
<div class="quote-modal-footer">
    <button id="onpickup-reattempt" class="quote-modal-btn btn-warning">Re-attempt</button>
    <button id="onpickup-failed" class="quote-modal-btn btn-danger">Failed</button>
    <button id="onpickup-reject" class="quote-modal-btn btn-outline">Reject</button>
    <button id="onpickup-close" class="quote-modal-btn btn-secondary">Close</button>
</div>
  <button id="onpickup-pickedup" class="quote-modal-btn btn-success">Picked Up</button>
        </div>
    </div>
</div>
  
    <!-- Pickup Modal -->
    <div id="pickupModal" class="quote-modal">
        <div class="quote-modal-content">
          
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


    <script src="assets/js/to-pick-up-table-switching.js"></script>
    <script src="assets/js/to-pick-up-image-viewer.js"></script>
    <script src="assets/js/to-pick-up-confirm.js"></script>

    <script>
        // DOM Elements
const onPickupModal = document.getElementById('onPickupModal');
const onPickupModalClose = document.querySelector('#onPickupModal .quote-modal-close');
const reattemptBtn = document.getElementById('onpickup-reattempt');
const failedBtn = document.getElementById('onpickup-failed');
const rejectBtn = document.getElementById('onpickup-reject');
const closeOnPickupBtn = document.getElementById('onpickup-close');
const pickedUpBtn = document.getElementById('onpickup-pickedup');

// View button click handler for on-pickup orders
function handleOnPickupViewButtonClick() {
    const id = this.getAttribute('data-id');
    const userId = this.getAttribute('data-user-id');
    const ticket = this.getAttribute('data-ticket');
    const name = this.getAttribute('data-name');
    const mobile = this.getAttribute('data-mobile');
    const address = this.getAttribute('data-address');
    const email = this.getAttribute('data-email');
    const attempt = this.closest('tr').querySelector('td:nth-child(6)').textContent.trim();
    
    // Store data in modal
    onPickupModal.setAttribute('data-current-id', id);
    document.getElementById('onpickup-modal-id').value = id;
    document.getElementById('onpickup-modal-user-id').value = userId;
    document.getElementById('onpickup-modal-email').value = email;
    document.getElementById('onpickup-modal-ticket').value = ticket;
    document.getElementById('onpickup-modal-attempt').value = attempt;
    
    // Populate modal fields
    document.getElementById('onpickup-modal-ticket').textContent = ticket;
    document.getElementById('onpickup-modal-attempt').textContent = attempt;
    document.getElementById('onpickup-modal-name').textContent = name;
    document.getElementById('onpickup-modal-mobile').textContent = mobile || 'N/A';
    document.getElementById('onpickup-modal-address').textContent = address || 'N/A';
    document.getElementById('onpickup-modal-last-attempt').textContent = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Show modal
    onPickupModal.style.display = 'block';
}

// Handle reattempt
function handleReattempt() {
    const id = onPickupModal.getAttribute('data-current-id');
    const userId = document.getElementById('onpickup-modal-user-id').value;
    const email = document.getElementById('onpickup-modal-email').value;
    const ticket = document.getElementById('onpickup-modal-ticket').value;
    const attempt = document.getElementById('onpickup-modal-attempt').value;

    // Show loading state
    const originalText = reattemptBtn.textContent;
    reattemptBtn.disabled = true;
    reattemptBtn.textContent = 'Processing...';

    fetch('functions/onpickup_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'reattempt',
            id: id,
            user_id: userId,
            email: email,
            ticket: ticket,
            attempt: attempt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            onPickupModal.style.display = 'none';
            refreshPickupTable();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        reattemptBtn.disabled = false;
        reattemptBtn.textContent = originalText;
    });
}

// Handle failed
function handleFailed() {
    const id = onPickupModal.getAttribute('data-current-id');
    const userId = document.getElementById('onpickup-modal-user-id').value;
    const email = document.getElementById('onpickup-modal-email').value;
    const ticket = document.getElementById('onpickup-modal-ticket').value;
    const attempt = document.getElementById('onpickup-modal-attempt').value;

    // Show loading state
    const originalText = failedBtn.textContent;
    failedBtn.disabled = true;
    failedBtn.textContent = 'Processing...';

    fetch('functions/onpickup_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'failed',
            id: id,
            user_id: userId,
            email: email,
            ticket: ticket,
            attempt: attempt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            onPickupModal.style.display = 'none';
            refreshPickupTable();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        failedBtn.disabled = false;
        failedBtn.textContent = originalText;
    });
}

// Handle reject
function handleReject() {
    if (!confirm('Are you sure you want to reject this order? This action cannot be undone.')) {
        return;
    }

    const id = onPickupModal.getAttribute('data-current-id');
    const userId = document.getElementById('onpickup-modal-user-id').value;
    const email = document.getElementById('onpickup-modal-email').value;
    const ticket = document.getElementById('onpickup-modal-ticket').value;
    const attempt = document.getElementById('onpickup-modal-attempt').value;

    // Show loading state
    const originalText = rejectBtn.textContent;
    rejectBtn.disabled = true;
    rejectBtn.textContent = 'Processing...';

    fetch('functions/onpickup_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'reject',
            id: id,
            user_id: userId,
            email: email,
            ticket: ticket,
            attempt: attempt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            onPickupModal.style.display = 'none';
            refreshPickupTable();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        rejectBtn.disabled = false;
        rejectBtn.textContent = originalText;
    });
}
function handlePickedUp() {
    const id = onPickupModal.getAttribute('data-current-id');
    const userId = document.getElementById('onpickup-modal-user-id').value;
    const email = document.getElementById('onpickup-modal-email').value;
    const ticket = document.getElementById('onpickup-modal-ticket').value;
    const attempt = document.getElementById('onpickup-modal-attempt').value;

    // Show loading state
    const originalText = pickedUpBtn.textContent;
    pickedUpBtn.disabled = true;
    pickedUpBtn.textContent = 'Processing...';

    fetch('functions/onpickup_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'pickedup',
            id: id,
            user_id: userId,
            email: email,
            ticket: ticket,
            attempt: attempt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            onPickupModal.style.display = 'none';
            refreshPickupTable();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        pickedUpBtn.disabled = false;
        pickedUpBtn.textContent = originalText;
    });
}

// Modal close handlers
function closeOnPickupModal() {
    onPickupModal.style.display = 'none';
}

function handleWindowClick(event) {
    if (event.target === onPickupModal) {
        closeOnPickupModal();
    }
}

// Attach all event listeners
function attachOnPickupEventListeners() {
    // View buttons
    document.querySelectorAll('.view-on-pickup-modal').forEach(button => {
        button.addEventListener('click', handleOnPickupViewButtonClick);
    });
    
    // Modal close
    onPickupModalClose.addEventListener('click', closeOnPickupModal);
    closeOnPickupBtn.addEventListener('click', closeOnPickupModal);
    window.addEventListener('click', handleWindowClick);
    
    // Action buttons
    reattemptBtn.addEventListener('click', handleReattempt);
    failedBtn.addEventListener('click', handleFailed);
    rejectBtn.addEventListener('click', handleReject);
    pickedUpBtn.addEventListener('click', handlePickedUp);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    attachOnPickupEventListeners();
    
    // Use existing refresh function from to-pick-up-confirm.js
    if (typeof refreshPickupTable !== 'function') {
        function refreshPickupTable() {
            location.reload(); // Fallback if not defined
        }
    }
});
    </script>


<?php include "includes/script-src.php";?>
</body>
</html>