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