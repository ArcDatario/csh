<div class="quotes-container approved-orders-container" id="approved-orders-container" style="display:none;">
        <?php
include '../db_connection.php';

// Fetch initial orders for the logged-in user
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
    ?>
        <div class="quote-card animate__animated animate__fadeInUp">
            <img src="<?= htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8') ?>" alt="Design" class="card-image">
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
                <div class="card-actions">
                            
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
                    <span class="quote-date"><?= $createdAt ?></span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
        </div>

 
        
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
                        <button class="order-agree-btn" >
                            <i class="fas fa-check-circle" disabled></i> Agree
                        </button>
                        <button class="order-cancel-btn">
                            <i class="fas fa-times-circle" disabled></i> Reject
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

            <!-- Processing Step -->
            <!-- <div class="order-step order-step-current">
                <div class="order-step-number">3</div>
                <div class="order-step-connector-pending"></div>
                <div class="order-step-content">
                    <div id="approvedProcessingTitle" class="order-step-title">Processing</div>
                    <div id="approvedProcessingDesc" class="order-step-description">Items are in the printing process</div>
                    <div id="approvedProcessingDate" class="order-step-date">Jan 17, 2023</div>
                </div>
            </div> -->
            
          
            
            <!-- Completed Step -->
            <!-- <div class="order-step">
                <div class="order-step-number">5</div>
                <div class="order-step-connector"></div>
                <div class="order-step-content">
                    <div id="approvedCompletedTitle" class="order-step-title">Completed</div>
                    <div id="approvedCompletedDesc" class="order-step-description">Order has been delivered/picked up</div>
                    <div id="approvedCompletedDate" class="order-step-date">Pending</div>
                </div>
            </div> -->
        </div>
    </div>
</div>
<script>
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

    // Remove or skip updating the Processing step date
    // (No reference to 'approvedProcessingDate' here)

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
                <div id="confirmationModalMessage" style="
                    margin-bottom: 28px;
                    font-size: 16px;
                    line-height: 1.5;
                    color: #333;
                "></div>
                <div style="display: flex; justify-content: center; gap: 12px;">
                    <button id="confirmNoBtn" style="
                        padding: 10px 20px;
                        background: #f5f5f5;
                        color: #333;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-weight: 500;
                        transition: all 0.2s ease;
                    ">Cancel</button>
                    <button id="confirmYesBtn" style="
                        padding: 10px 20px;
                        background: #000;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-weight: 500;
                        transition: all 0.2s ease;
                    ">Confirm</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Add hover effects
        const yesBtn = document.getElementById('confirmYesBtn');
        const noBtn = document.getElementById('confirmNoBtn');
        
        yesBtn.onmouseenter = () => yesBtn.style.transform = 'translateY(-1px)';
        yesBtn.onmouseleave = () => yesBtn.style.transform = 'translateY(0)';
        noBtn.onmouseenter = () => noBtn.style.transform = 'translateY(-1px)';
        noBtn.onmouseleave = () => noBtn.style.transform = 'translateY(0)';
        
        // Add active/click effects
        yesBtn.onmousedown = () => yesBtn.style.transform = 'translateY(1px)';
        yesBtn.onmouseup = () => yesBtn.style.transform = 'translateY(-1px)';
        noBtn.onmousedown = () => noBtn.style.transform = 'translateY(1px)';
        noBtn.onmouseup = () => noBtn.style.transform = 'translateY(-1px)';
    }
    
    document.getElementById('confirmationModalMessage').textContent = message;
    
    // Show with animation
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.children[0].style.transform = 'translateY(0)';
    }, 10);
    
    document.getElementById('confirmYesBtn').onclick = function() {
        modal.style.opacity = '0';
        modal.children[0].style.transform = 'translateY(10px)';
        setTimeout(() => {
            modal.style.display = 'none';
            onConfirm();
        }, 200);
    };
    
    document.getElementById('confirmNoBtn').onclick = function() {
        modal.style.opacity = '0';
        modal.children[0].style.transform = 'translateY(10px)';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 200);
    };
}

function userApproveOrder(ticketNumber, action) {
    fetch('functions/user_approve_order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ ticketNumber, action })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
    showToast(
        'Successful! The items will be picked up on your location',
        '',
        'success',
        3500 // duration in ms
    );
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

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.approved-order-btn').forEach(button => {
        button.addEventListener('click', openApprovedOrderModal);
    });

    document.querySelector('.order-agree-btn').addEventListener('click', function() {
        const ticket = document.getElementById('approvedOrderProcessModal').getAttribute('data-ticket');
        if (!ticket) {
            showToast('Error', 'Ticket number not found.', 'error');
            return;
        }
        showConfirmationModal('Are you sure you want to agree to this quote?', function() {
            userApproveOrder(ticket, 'agree');
        });
    });

    document.querySelector('.order-cancel-btn').addEventListener('click', function() {
        const ticket = document.getElementById('approvedOrderProcessModal').getAttribute('data-ticket');
        if (!ticket) {
            showToast('Error', 'Ticket number not found.', 'error');
            return;
        }
        showConfirmationModal('Are you sure you want to reject this quote?', function() {
            userApproveOrder(ticket, 'reject');
        });
    });

    document.getElementById('approvedOrderProcessModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeApprovedOrderProcessModal();
        }
    });
});
</script>

