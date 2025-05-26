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
                        data-user-approved-date="<?= htmlspecialchars($order['user_approved_date'], ENT_QUOTES, 'UTF-8') ?>"
                        data-ready-date="<?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?>"
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
            
            <!-- Agreed Price Step -->
            <div class="order-step order-step-completed">
                <div class="order-step-number">2</div>
                <div class="order-step-connector-completed"></div>
                <div class="order-step-content">
                    <div id="toPickUpAdminApprovedTitle" class="order-step-title">Agreed Price</div>
                    <div class="order-step-description">
                        <div id="toPickUpOrderSummary" class="order-summary-details">
                            <p id="toPickUpUnitPrice">Unit Price: $10.00</p>
                            <p id="toPickUpQuantity">Quantity: 5</p>
                            <p id="toPickUpSubtotal" class="order-subtotal">Subtotal: $50.00</p>
                        </div>
                    </div>
                    <div id="toPickUpAdminApprovedDate" class="order-step-date">Jan 16, 2023</div>
                </div>
            </div>
            
            <!-- To Pick Up Step -->
            <div class="order-step order-step-current">
                <div class="order-step-number">3</div>
                <div class="order-step-connector-pending"></div>
                <div class="order-step-content">
                    <div id="toPickUpProcessingTitle" class="order-step-title">To Pick Up</div>
                    <div id="toPickUpProcessingDesc" class="order-step-description">An email will be sent to you once your items will be picked up by our logistics</div>
                    <div id="toPickUpProcessingDate" class="order-step-date">Pending</div>
                </div>
            </div>
            
            <!-- Processing Step -->
            <div class="order-step">
                <div class="order-step-number">4</div>
                <div class="order-step-connector"></div>
                <div class="order-step-content">
                    <div id="toPickUpReadyTitle" class="order-step-title">Processing</div>
                    <div id="toPickUpReadyDesc" class="order-step-description">Items are being prepared for pickup</div>
                    <div id="toPickUpReadyDate" class="order-step-date">Pending</div>
                </div>
            </div>
            
            <!-- Completed Step -->
            <div class="order-step">
                <div class="order-step-number">5</div>
                <div class="order-step-connector"></div>
                <div class="order-step-content">
                    <div id="toPickUpCompletedTitle" class="order-step-title">Completed</div>
                    <div id="toPickUpCompletedDesc" class="order-step-description">Order will be marked completed after pickup</div>
                    <div id="toPickUpCompletedDate" class="order-step-date">Pending</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openToPickUpOrderModal(event) {
    const button = event.currentTarget;
    const ticket = button.getAttribute('data-approved-ticket');
    const createdAt = button.getAttribute('data-approved-created-at');
    const pricing = button.getAttribute('data-pricing');
    const quantity = button.getAttribute('data-quantity');
    const subtotal = button.getAttribute('data-subtotal');
    const adminApprovedDate = button.getAttribute('data-admin-approved-date');
    const userApprovedDate = button.getAttribute('data-user-approved-date');
    const readyDate = button.getAttribute('data-ready-date');

    // Format the dates
    const date = new Date(createdAt);
    const formattedDate = date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });

    // Format user approved date (Agreed Price date)
    let formattedUserApprovedDate = 'N/A';
    if (userApprovedDate && userApprovedDate !== 'null' && userApprovedDate !== '') {
        const userDate = new Date(userApprovedDate);
        if (!isNaN(userDate)) {
            formattedUserApprovedDate = userDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }
    }

    document.getElementById('toPickUpProcessTitle').textContent = `Ticket #${ticket} Process Details`;
    document.getElementById('toPickUpQuotePlacedDate').textContent = formattedDate;
    document.getElementById('toPickUpUnitPrice').textContent = `Unit Price: ₱${parseFloat(pricing).toFixed(2)}`;
    document.getElementById('toPickUpQuantity').textContent = `Quantity: ${quantity}`;
    document.getElementById('toPickUpSubtotal').textContent = `Subtotal: ₱${parseFloat(subtotal).toFixed(2)}`;
    document.getElementById('toPickUpAdminApprovedDate').textContent = formattedUserApprovedDate; // Changed to user approved date
    
    // All future steps show as Pending
    document.getElementById('toPickUpProcessingDate').textContent = 'Pending';
    document.getElementById('toPickUpReadyDate').textContent = 'Pending';
    document.getElementById('toPickUpCompletedDate').textContent = 'Pending';

    document.getElementById('toPickUpProcessModal').setAttribute('data-ticket', ticket);
    document.getElementById('toPickUpProcessModal').style.display = 'flex';
}

function closeToPickUpProcessModal() {
    document.getElementById('toPickUpProcessModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.to-pick-up-order-btn').forEach(button => {
        button.addEventListener('click', openToPickUpOrderModal);
    });

    document.getElementById('toPickUpProcessModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeToPickUpProcessModal();
        }
    });

    // --- To Pick Up Orders Search Functionality ---
    const toPickupSearchInput = document.getElementById('ToPickupSearchInput');
    const pickupOrdersContainer = document.getElementById('pickup-orders-container');
    if (toPickupSearchInput && pickupOrdersContainer) {
        toPickupSearchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const cards = pickupOrdersContainer.querySelectorAll('.quote-card');
            cards.forEach(card => {
                let ticketNumber = '';
                card.querySelectorAll('.card-detail').forEach(detail => {
                    const label = detail.querySelector('.detail-label');
                    const value = detail.querySelector('.detail-value');
                    if (label && value && label.textContent.trim().toLowerCase() === 'ticket #') {
                        ticketNumber = value.textContent.trim().toLowerCase();
                    }
                });
                if (ticketNumber.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>