<div class="quotes-container completed-orders-container" id="completed-orders-container" style="display:none;">
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

    <!-- HTML Structure -->
    <?php if (!$user_id): ?>
        <div class="no-orders">No user ID found. Please log in.</div>
    <?php elseif (!$has_orders): ?>
        <div class="no-orders">No completed orders found</div>
    <?php else: ?>
        <?php foreach ($orders as $order): 
            $createdAt = date('M d, Y', strtotime($order['created_at']));
            $subtotal = $order['pricing'] * $order['quantity'];
        ?>
            <div class="quote-card animate__animated animate__fadeInUp">
                <img src="<?= htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8') ?>" alt="Design" class="card-image">
                <span class="card-status status-completed"><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></span>
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
                        <span class="quote-date"><?= $createdAt ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

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

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.completed-order-btn').forEach(button => {
        button.addEventListener('click', openCompletedOrderModal);
    });

    document.getElementById('completedProcessModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCompletedProcessModal();
        }
    });

    // Search functionality for completed orders
    const completedSearchInput = document.getElementById('CompletedSearchInput');
    const completedOrdersContainer = document.getElementById('completed-orders-container');
    if (completedSearchInput && completedOrdersContainer) {
        completedSearchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const cards = completedOrdersContainer.querySelectorAll('.quote-card');
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