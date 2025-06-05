<div id="to-ship-table" class="table-responsive tab-content">
    <table id="toship-table">
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
        <tbody id="toship-table-body">
            <!-- Content will be loaded via JavaScript -->
        </tbody>
    </table>
</div>

<!-- To Ship Modal -->
<div id="toShipModal" class="quote-modal">
  <div class="quote-modal-content">
    <span class="toship-modal-close">&times;</span>
    <h2>Order Details</h2>
    <div class="quote-modal-body">
      <!-- Ticket -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Ticket #:</span>
        <span id="toship-modal-ticket" class="quote-modal-value"></span>
      </div>
      
      <!-- Customer Info -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Customer:</span>
        <span id="toship-modal-name" class="quote-modal-value"></span>
      </div>
      
      <div class="quote-modal-row">
        <span class="quote-modal-label">Email:</span>
        <span id="toship-modal-email" class="quote-modal-value"></span>
      </div>
      
      <div class="quote-modal-row">
        <span class="quote-modal-label">Mobile:</span>
        <span id="toship-modal-mobile" class="quote-modal-value"></span>
      </div>
      
      <div class="quote-modal-row">
        <span class="quote-modal-label">Address:</span>
        <span id="toship-modal-address" class="quote-modal-value address-value"></span>
      </div>
      
      <!-- Design with buttons -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Design:</span>
        <div class="design-image-container">
          <img id="toship-modal-design" src="" alt="Design" class="design-image">
          <div class="design-buttons">
            <button class="view-design-btn">View</button>
            <button class="download-design-btn">Download</button>
          </div>
        </div>
      </div>
      
      <!-- Print Type -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Print Type:</span>
        <span id="toship-modal-print-type" class="quote-modal-value"></span>
      </div>
      
      <!-- Quantity -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Quantity:</span>
        <span id="toship-modal-quantity" class="quote-modal-value"></span>
      </div>
      
      <!-- Pricing -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Unit Price:</span>
        <span id="toship-modal-pricing" class="quote-modal-value"></span>
      </div>
      
      <!-- Subtotal -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Subtotal:</span>
        <span id="toship-modal-subtotal" class="quote-modal-value"></span>
      </div>
      
      <!-- Notes -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Notes:</span>
        <span id="toship-modal-note" class="quote-modal-value"></span>
      </div>
      
      <!-- Shipping Date -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Shipping Date:</span>
        <span id="toship-modal-ship-date" class="quote-modal-value"></span>
      </div>
      
      <!-- Hidden fields -->
      <input type="hidden" id="toship-modal-id" name="id">
      <input type="hidden" id="toship-modal-user-id" name="user_id">
      <input type="hidden" id="toship-modal-ticket-input" name="ticket">
    </div>
    <div class="quote-modal-footer">
      <button id="toship-modal-complete" class="quote-modal-btn btn-process">Mark as Shipped</button>
      <button id="toship-modal-close-btn" class="quote-modal-btn btn-close">Close</button>
    </div>
  </div>
</div>

<!-- Confirmation Modal for Shipping -->
<div id="toship-confirm-modal" class="quote-modal">
  <div class="quote-modal-content" style="max-width: 500px;">
    <h2>Confirm Delivery</h2>
    <div class="quote-modal-body">
      <p>Are you sure this order has been delivered?</p>
      <p>This will mark the order as completed and notify the customer.</p>
    </div>
    <div class="quote-modal-footer">
      <button id="toship-confirm-yes" class="quote-modal-btn btn-process">Yes, Delivered</button>
      <button id="toship-confirm-no" class="quote-modal-btn btn-close">Cancel</button>
    </div>
  </div>
</div>

<script>
// Function to fetch and update the to-ship table
function updateToShipTable() {
    fetch('api/get_toship_orders.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('toship-table-body').innerHTML = data;
            attachToShipViewButtonListeners();
        })
        .catch(error => {
            console.error('Error fetching to-ship orders:', error);
        });
}

// Function to attach event listeners to view buttons
function attachToShipViewButtonListeners() {
    document.querySelectorAll('.view-to-ship-modal').forEach(button => {
        button.addEventListener('click', handleToShipViewButtonClick);
    });
}

// View button click handler for to-ship orders
function handleToShipViewButtonClick() {
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
        subtotal: this.getAttribute('data-subtotal')
    };

    // Store data in modal
    document.getElementById('toShipModal').setAttribute('data-current-id', orderData.id);
    document.getElementById('toship-modal-id').value = orderData.id;
    document.getElementById('toship-modal-user-id').value = orderData.userId;
    document.getElementById('toship-modal-email').value = orderData.email;
    document.getElementById('toship-modal-ticket-input').value = orderData.ticket;
    
    // Populate modal fields
    document.getElementById('toship-modal-ticket').textContent = orderData.ticket;
    document.getElementById('toship-modal-name').textContent = orderData.name;
    document.getElementById('toship-modal-mobile').textContent = orderData.mobile || 'N/A';
    document.getElementById('toship-modal-address').textContent = orderData.address || 'N/A';
    document.getElementById('toship-modal-email').textContent = orderData.email;
    document.getElementById('toship-modal-print-type').textContent = orderData.printType;
    document.getElementById('toship-modal-quantity').textContent = orderData.quantity;
    document.getElementById('toship-modal-pricing').textContent = orderData.pricing ? '₱' + parseFloat(orderData.pricing).toFixed(2) : 'N/A';
    document.getElementById('toship-modal-subtotal').textContent = orderData.subtotal ? '₱' + parseFloat(orderData.subtotal).toFixed(2) : 'N/A';
    document.getElementById('toship-modal-note').textContent = orderData.note || 'No notes';
    document.getElementById('toship-modal-design').src = '../user/' + orderData.design;
    document.getElementById('toship-modal-ship-date').textContent = orderData.date || new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Show modal
    document.getElementById('toShipModal').style.display = 'block';
}

// Initialize modals and event listeners
function initializeToShipModals() {
    const toShipModal = document.getElementById('toShipModal');
    const confirmModal = document.getElementById('toship-confirm-modal');
    
    // Close buttons
    document.querySelector('.toship-modal-close').addEventListener('click', function() {
        toShipModal.style.display = 'none';
    });
    
    document.getElementById('toship-modal-close-btn').addEventListener('click', function() {
        toShipModal.style.display = 'none';
    });
    
    document.getElementById('toship-confirm-no').addEventListener('click', function() {
        confirmModal.style.display = 'none';
    });
    
    // Close when clicking outside modal
    window.addEventListener('click', function(event) {
        if (event.target === toShipModal) {
            toShipModal.style.display = 'none';
        }
        if (event.target === confirmModal) {
            confirmModal.style.display = 'none';
        }
    });
    
    // Complete button handler
    document.getElementById('toship-modal-complete').addEventListener('click', function() {
        confirmModal.style.display = 'block';
    });
    
    // Confirm delivery handler
    document.getElementById('toship-confirm-yes').addEventListener('click', function() {
        const id = document.getElementById('toship-modal-id').value;
        const userId = document.getElementById('toship-modal-user-id').value;
        const ticket = document.getElementById('toship-modal-ticket-input').value;
        const email = document.getElementById('toship-modal-email').value;
        const quantity = document.getElementById('toship-modal-quantity').textContent;
        const pricing = document.getElementById('toship-modal-pricing').textContent.replace('₱', '');
        const subtotal = document.getElementById('toship-modal-subtotal').textContent.replace('₱', '');
        const address = document.getElementById('toship-modal-address').textContent;
        
        // Show loading state
        const originalText = this.textContent;
        this.disabled = true;
        this.textContent = 'Processing...';
        
        // Send data to server
        fetch('api/confirm_delivery.php', {
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
                updateToShipTable();
                toShipModal.style.display = 'none';
                confirmModal.style.display = 'none';
            } else {
                showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'An error occurred while updating order', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            this.disabled = false;
            this.textContent = originalText;
        });
    });
}

// Initialize the table when tab is active
function initializeToShipTable() {
    if (document.getElementById('to-ship-table').style.display !== 'none') {
        updateToShipTable();
        setInterval(updateToShipTable, 3000);
    }
}

// Toast notification function
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

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeToShipModals();
    initializeToShipTable();
});

// Make functions available globally
window.updateToShipTable = updateToShipTable;
window.initializeToShipTable = initializeToShipTable;
</script>