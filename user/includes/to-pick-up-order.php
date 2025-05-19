<div class="quotes-container pickup-orders-container" id="pickup-orders-container" style="display:none;">
        <?php
include '../db_connection.php';

// Fetch initial orders for the logged-in user
$user_id = $_SESSION['user_id'] ?? null;
$has_orders = false;
$orders = [];

if ($user_id) {
    $sql = "SELECT * FROM orders WHERE user_id = ? AND status = 'to-pick-up' ORDER BY created_at DESC";
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
                            
                <button class="view-details-btn to-pick-up-order-btn" 
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


        
 <div id="toPickUpProcessModal" class="order-process-modal">
    <div class="order-process-modal-content">
    <span class="order-process-close-btn" onclick="closeToPickUpProcessModal()">&times;</span>
        <h2 id="toPickUpProcessTitle" class="order-process-title">Ticket #12345 Process Details</h2>
        
        <div id="toPickUpProcessSteps" class="order-process-steps-container">
            <!-- Quote Placed Step -->
            <div class="order-step order-step-completed">
                <div class="order-step-number">1</div>
                <div class="order-step-connector-completed"></div>
                <div class="order-step-content">
                    <div id="toPickUpQuotePlacedTitle" class="order-step-title">Quote Placed</div>
                    <div id="toPickUpQuotePlacedDesc" class="order-step-description">Your order request has been received</div>
                    <div id="toPickUpQuotePlacedDate" class="order-step-date">Jan 15, 2023</div>
                </div>
            </div>
            
            <!-- Admin Approved Step -->
            <div class="order-step order-step-completed">
                <div class="order-step-number">2</div>
                <div class="order-step-connector-current"></div>
                <div class="order-step-content">
                    <div id="toPickUpAdminApprovedTitle" class="order-step-title">Admin Approved</div>
                    <div class="order-step-description">
                        <div id="toPickUpOrderSummary" class="order-summary-details">
                            <p id="toPickUpUnitPrice">Unit Price: $10.00</p>
                            <p id="toPickUpQuantity">Quantity: 5</p>
                            <p id="toPickUpSubtotal" class="order-subtotal">Subtotal: $50.00</p>
                        </div>
                    </div>
                    <div id="toPickUpAdminApprovedDate" class="order-step-date">Jan 16, 2023</div>
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
                    <div id="toPickUpProcessingTitle" class="order-step-title">Processing</div>
                    <div id="toPickUpProcessingDesc" class="order-step-description">Items are in the printing process</div>
                    <div id="toPickUpProcessingDate" class="order-step-date">Jan 17, 2023</div>
                </div>
            </div>
            
            <!-- Ready for Pickup/Delivery Step -->
            <div class="order-step">
                <div class="order-step-number">4</div>
                <div class="order-step-connector"></div>
                <div class="order-step-content">
                    <div id="toPickUpReadyTitle" class="order-step-title">Ready</div>
                    <div id="toPickUpReadyDesc" class="order-step-description">Your order is ready for pickup/delivery</div>
                    <div id="toPickUpReadyDate" class="order-step-date">Pending</div>
                </div>
            </div>
            
            <!-- Completed Step -->
            <div class="order-step">
                <div class="order-step-number">5</div>
                <div class="order-step-connector"></div>
                <div class="order-step-content">
                    <div id="toPickUpCompletedTitle" class="order-step-title">Completed</div>
                    <div id="toPickUpCompletedDesc" class="order-step-description">Order has been delivered/picked up</div>
                    <div id="toPickUpCompletedDate" class="order-step-date">Pending</div>
                </div>
            </div>
        </div>
    </div>
</div>


