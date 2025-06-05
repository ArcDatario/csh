<div id="completed-table" class="table-responsive tab-content">
    <table id="completed-orders-table">
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>Design</th>
                <th>Print Type</th>
                <th>Quantity</th>
                <th>Date Completed</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="completed-table-body">
            <!-- Content will be loaded via JavaScript -->
        </tbody>
    </table>
</div>

<!-- Completed Order Modal -->
<div id="completedModal" class="quote-modal">
    <div class="quote-modal-content">
        <span class="completed-modal-close">&times;</span>
        <h2>Completed Order Details</h2>
        <div class="quote-modal-body">
            <!-- Ticket and Customer -->
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Ticket #:</span>
                    <span id="completed-modal-ticket" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Customer:</span>
                    <span id="completed-modal-name" class="quote-modal-value"></span>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Email:</span>
                    <span id="completed-modal-email" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Mobile #:</span>
                    <span id="completed-modal-mobile" class="quote-modal-value"></span>
                </div>
            </div>
            
            <!-- Address -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Address:</span>
                <span id="completed-modal-address" class="quote-modal-value address-value"></span>
            </div>
            
            <!-- Design -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Design:</span>
                <div class="design-image-container">
                    <img id="completed-modal-design" src="" alt="Design" class="design-image">
                    <div class="design-buttons">
                        <button class="view-design-btn">View</button>
                        <button class="download-design-btn">Download</button>
                    </div>
                </div>
            </div>
            
            <!-- Order Details -->
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Print Type:</span>
                    <span id="completed-modal-print-type" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Quantity:</span>
                    <span id="completed-modal-quantity" class="quote-modal-value"></span>
                </div>
            </div>
            
            <!-- Pricing -->
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Unit Price:</span>
                    <span id="completed-modal-pricing" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Subtotal:</span>
                    <span id="completed-modal-subtotal" class="quote-modal-value"></span>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="quote-modal-timeline">
                <h3>Order Timeline</h3>
                <div class="timeline-item">
                    <span class="timeline-label">Order Placed:</span>
                    <span id="completed-modal-created" class="timeline-value"></span>
                </div>
                <div class="timeline-item">
                    <span class="timeline-label">Design Approved:</span>
                    <span id="completed-modal-designer-approved" class="timeline-value"></span>
                </div>
                <div class="timeline-item">
                    <span class="timeline-label">Admin Approved:</span>
                    <span id="completed-modal-admin-approved" class="timeline-value"></span>
                </div>
                <div class="timeline-item">
                    <span class="timeline-label">Processing Started:</span>
                    <span id="completed-modal-processing" class="timeline-value"></span>
                </div>
                <div class="timeline-item">
                    <span class="timeline-label">Pickup Date:</span>
                    <span id="completed-modal-pickup" class="timeline-value"></span>
                </div>
                <div class="timeline-item">
                    <span class="timeline-label">Shipping Date:</span>
                    <span id="completed-modal-shipping" class="timeline-value"></span>
                </div>
                <div class="timeline-item">
                    <span class="timeline-label">Completed Date:</span>
                    <span id="completed-modal-completed" class="timeline-value"></span>
                </div>
            </div>
            
            <!-- Notes -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Notes:</span>
                <span id="completed-modal-note" class="quote-modal-value note-value"></span>
            </div>
        </div>
        <div class="quote-modal-footer">
            <button id="completed-modal-close" class="quote-modal-btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<style>
    .quote-modal-timeline {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.quote-modal-timeline h3 {
    margin-bottom: 15px;
    font-size: 1.1rem;
    color: #333;
}

.timeline-item {
    display: flex;
    margin-bottom: 8px;
}

.timeline-label {
    font-weight: 500;
    color: #666;
    min-width: 150px;
}

.timeline-value {
    color: #333;
}
</style>
<script>
// Function to fetch and update the completed orders table
function updateCompletedTable() {
    fetch('api/get_completed_orders.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('completed-table-body').innerHTML = data;
            attachCompletedViewButtonListeners();
        })
        .catch(error => {
            console.error('Error fetching completed orders:', error);
        });
}

// Function to attach event listeners to view buttons
function attachCompletedViewButtonListeners() {
    document.querySelectorAll('.view-completed-modal').forEach(button => {
        button.addEventListener('click', handleCompletedViewButtonClick);
    });
}

// View button click handler for completed orders
function handleCompletedViewButtonClick() {
    const orderData = {
        id: this.getAttribute('data-id'),
        userId: this.getAttribute('data-user-id'),
        ticket: this.getAttribute('data-ticket'),
        design: this.getAttribute('data-design'),
        mobile: this.getAttribute('data-mobile'),
        name: this.getAttribute('data-name'),
        printType: this.getAttribute('data-print-type'),
        quantity: this.getAttribute('data-quantity'),
        date: this.getAttribute('data-date'),
        status: this.getAttribute('data-status'),
        note: this.getAttribute('data-note'),
        address: this.getAttribute('data-address'),
        email: this.getAttribute('data-email'),
        pricing: this.getAttribute('data-pricing'),
        subtotal: this.getAttribute('data-subtotal'),
        created: this.getAttribute('data-created'),
        designerApproved: this.getAttribute('data-designer-approved'),
        adminApproved: this.getAttribute('data-admin-approved'),
        processing: this.getAttribute('data-processing'),
        pickup: this.getAttribute('data-pickup'),
        shipping: this.getAttribute('data-shipping'),
        delivered: this.getAttribute('data-delivered'),
        completed: this.getAttribute('data-completed') // This now matches the database field
    };

    // Store data in modal
    document.getElementById('completedModal').setAttribute('data-current-id', orderData.id);
    
    // Populate modal fields
    document.getElementById('completed-modal-ticket').textContent = orderData.ticket;
    document.getElementById('completed-modal-name').textContent = orderData.name;
    document.getElementById('completed-modal-mobile').textContent = orderData.mobile || 'N/A';
    document.getElementById('completed-modal-email').textContent = orderData.email;
    document.getElementById('completed-modal-address').textContent = orderData.address || 'N/A';
    document.getElementById('completed-modal-print-type').textContent = orderData.printType;
    document.getElementById('completed-modal-quantity').textContent = orderData.quantity;
    document.getElementById('completed-modal-pricing').textContent = orderData.pricing ? '₱' + parseFloat(orderData.pricing).toFixed(2) : 'N/A';
    document.getElementById('completed-modal-subtotal').textContent = orderData.subtotal ? '₱' + parseFloat(orderData.subtotal).toFixed(2) : 'N/A';
    document.getElementById('completed-modal-note').textContent = orderData.note || 'No notes';
    document.getElementById('completed-modal-design').src = '../user/' + orderData.design;
    
    // Populate timeline
    document.getElementById('completed-modal-created').textContent = formatDate(orderData.created);
    document.getElementById('completed-modal-designer-approved').textContent = formatDate(orderData.designerApproved);
    document.getElementById('completed-modal-admin-approved').textContent = formatDate(orderData.adminApproved);
    document.getElementById('completed-modal-processing').textContent = formatDate(orderData.processing);
    document.getElementById('completed-modal-pickup').textContent = formatDate(orderData.pickup);
    document.getElementById('completed-modal-shipping').textContent = formatDate(orderData.shipping);
    document.getElementById('completed-modal-completed').textContent = formatDate(orderData.completed);
    
    // Show modal
    document.getElementById('completedModal').style.display = 'block';
}

// Format date for display
function formatDate(dateString) {
    if (!dateString || dateString === '0000-00-00 00:00:00') return 'N/A';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Initialize modals and event listeners
function initializeCompletedModals() {
    const completedModal = document.getElementById('completedModal');
    
    // Close buttons
    document.querySelector('.completed-modal-close').addEventListener('click', function() {
        completedModal.style.display = 'none';
    });
    
    document.getElementById('completed-modal-close').addEventListener('click', function() {
        completedModal.style.display = 'none';
    });
    
    // Close when clicking outside modal
    window.addEventListener('click', function(event) {
        if (event.target === completedModal) {
            completedModal.style.display = 'none';
        }
    });
}

// Initialize the table when tab is active
function initializeCompletedTable() {
    if (document.getElementById('completed-table').style.display !== 'none') {
        updateCompletedTable();
        setInterval(updateCompletedTable, 3000);
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeCompletedModals();
});

// Make functions available globally
window.updateCompletedTable = updateCompletedTable;
window.initializeCompletedTable = initializeCompletedTable;
</script>