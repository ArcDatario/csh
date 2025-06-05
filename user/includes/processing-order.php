<div class="quotes-container processing-orders-container" id="processing-orders-container" style="display:none;">
    <?php
    include '../db_connection.php';

    // Fetch processing orders for the logged-in user
    $user_id = $_SESSION['user_id'] ?? null;
    $has_orders = false;
    $orders = [];

    if ($user_id) {
        $sql = "SELECT * FROM orders WHERE user_id = ? AND status = 'processing' ORDER BY created_at DESC";
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
        <div class="no-orders">No processing orders found</div>
    <?php else: ?>
        <?php foreach ($orders as $order): 
            $createdAt = date('M d, Y', strtotime($order['created_at']));
            $subtotal = $order['pricing'] * $order['quantity'];
        ?>
            <div class="quote-card animate__animated animate__fadeInUp">
                <img src="<?= htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8') ?>" alt="Design" class="card-image">
                <span class="card-status status-processing"><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></span>
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
                        <span class="quote-date"><?= $createdAt ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

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
            
            <!-- Completed Step -->
            <!-- <div class="order-step">
                <div class="order-step-number">6</div>
                <div class="order-step-connector-pending"></div>
                <div class="order-step-content">
                    <div id="processingCompletedTitle" class="order-step-title">Completed</div>
                    <div id="processingCompletedDesc" class="order-step-description">Order will be marked completed after pickup</div>
                    <div id="processingCompletedDate" class="order-step-date">Pending</div>
                </div>
            </div> -->
        </div>
    </div>
</div>

<script>
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

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.processing-order-btn').forEach(button => {
        button.addEventListener('click', openProcessingOrderModal);
    });

    document.getElementById('processingProcessModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeProcessingProcessModal();
        }
    });

    // Search functionality for processing orders
    const processingSearchInput = document.getElementById('ProcessingSearchInput');
    const processingOrdersContainer = document.getElementById('processing-orders-container');
    if (processingSearchInput && processingOrdersContainer) {
        processingSearchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const cards = processingOrdersContainer.querySelectorAll('.quote-card');
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