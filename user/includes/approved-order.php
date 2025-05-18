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
        data-approved-created-at="<?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?>">
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
            
            <!-- Processing Step -->
            <div class="order-step order-step-current">
                <div class="order-step-number">3</div>
                <div class="order-step-connector-pending"></div>
                <div class="order-step-content">
                    <div id="approvedProcessingTitle" class="order-step-title">Processing</div>
                    <div id="approvedProcessingDesc" class="order-step-description">Items are in the printing process</div>
                    <div id="approvedProcessingDate" class="order-step-date">Jan 17, 2023</div>
                </div>
            </div>
            
            <!-- Ready for Pickup/Delivery Step -->
            <!-- <div class="order-step">
                <div class="order-step-number">4</div>
                <div class="order-step-connector"></div>
                <div class="order-step-content">
                    <div id="approvedReadyTitle" class="order-step-title">Ready</div>
                    <div id="approvedReadyDesc" class="order-step-description">Your order is ready for pickup/delivery</div>
                    <div id="approvedReadyDate" class="order-step-date">Pending</div>
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
   // Function to open the approved order modal with order details
function openApprovedOrderModal(event) {
    const button = event.currentTarget;
    const ticket = button.getAttribute('data-approved-ticket');
    const createdAt = button.getAttribute('data-approved-created-at');
    const id = button.getAttribute('data-approved-id');
    
    // Format the dates
    const date = new Date(createdAt);
    const formattedDate = date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });

    // Update modal content
    document.getElementById('approvedOrderProcessTitle').textContent = `Ticket #${ticket} Process Details`;
    document.getElementById('approvedQuotePlacedDate').textContent = formattedDate;
    
    // Set example data (replace with actual data from your backend)
    document.getElementById('approvedUnitPrice').textContent = `Unit Price: $10.00`;
    document.getElementById('approvedQuantity').textContent = `Quantity: 5`;
    document.getElementById('approvedSubtotal').textContent = `Subtotal: $50.00`;
    document.getElementById('approvedAdminApprovedDate').textContent = formattedDate;
    document.getElementById('approvedProcessingDate').textContent = new Date(date.getTime() + 86400000).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });

    // Show the modal
    document.getElementById('approvedOrderProcessModal').style.display = 'flex';
}

// Function to close the modal (works with onclick handler)
function closeApprovedOrderProcessModal() {
    document.getElementById('approvedOrderProcessModal').style.display = 'none';
}

// Set up event listeners when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Open modal when approved order buttons are clicked
    document.querySelectorAll('.approved-order-btn').forEach(button => {
        button.addEventListener('click', openApprovedOrderModal);
    });
    
    // Close modal when clicking outside content
    document.getElementById('approvedOrderProcessModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeOrderProcessModal();
        }
    });
});
</script>


