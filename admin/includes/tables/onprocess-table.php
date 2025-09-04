<div id="on-process-table" class="table-responsive tab-content">
    <table id="onprocess-table">
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
        <tbody id="onprocess-table-body">
            <!-- Content will be loaded via JavaScript -->
        </tbody>
    </table>
</div>
<script>
// Function to fetch and update the on-process table
function updateOnProcessTable() {
    fetch('api/get_onprocess_orders.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('onprocess-table-body').innerHTML = data;
            attachViewButtonListeners();
        })
        .catch(error => {
            console.error('Error fetching on-process orders:', error);
        });
}

// Function to attach event listeners to view buttons
function attachViewButtonListeners() {
    document.querySelectorAll('.view-on-process-modal').forEach(button => {
        button.addEventListener('click', handleOnProcessViewButtonClick);
    });
}
// Reset view button state when modal is closed
function resetViewButtonState() {
    const viewButtons = document.querySelectorAll('#onProcessModal .view-design-btn');
    viewButtons.forEach(button => {
        button.style.display = 'inline-block'; // Reset to default
    });
}
// View button click handler for on-process orders
function handleOnProcessViewButtonClick() {
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
        viewable: this.getAttribute('data-viewable') === 'yes'
    };

    // Store data in modal
    document.getElementById('onProcessModal').setAttribute('data-current-id', orderData.id);
    document.getElementById('onprocess-modal-id').value = orderData.id;
    document.getElementById('onprocess-modal-user-id').value = orderData.userId;
    document.getElementById('onprocess-modal-email').value = orderData.email;
    document.getElementById('onprocess-modal-ticket-input').value = orderData.ticket;
    
    // Populate modal fields
    document.getElementById('onprocess-modal-ticket').textContent = orderData.ticket;
    document.getElementById('onprocess-modal-name').textContent = orderData.name;
    document.getElementById('onprocess-modal-mobile').textContent = orderData.mobile || 'N/A';
    document.getElementById('onprocess-modal-address').textContent = orderData.address || 'N/A';
    document.getElementById('onprocess-modal-email').textContent = orderData.email;
    document.getElementById('onprocess-modal-print-type').textContent = orderData.printType;
    document.getElementById('onprocess-modal-quantity').textContent = orderData.quantity;
    document.getElementById('onprocess-modal-pricing').textContent = orderData.pricing ? '₱' + parseFloat(orderData.pricing).toFixed(2) : 'N/A';
    document.getElementById('onprocess-modal-subtotal').textContent = orderData.subtotal ? '₱' + parseFloat(orderData.subtotal).toFixed(2) : 'N/A';
    document.getElementById('onprocess-modal-note').textContent = orderData.note || 'No notes';
    
    // Determine if we should show the actual file or a placeholder
    const fileExtension = orderData.design.split('.').pop().toLowerCase();
    const imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    
    if (imageFormats.includes(fileExtension)) {
        // Show the actual image file
        document.getElementById('onprocess-modal-design').src = '../user/' + orderData.design;
    } else {
        // Show appropriate placeholder based on file type
        let placeholderSrc = '../file.png'; // default placeholder
        if (fileExtension === 'psd') placeholderSrc = '../photoshop.png';
        if (fileExtension === 'pdf') placeholderSrc = '../pdf.png';
        if (fileExtension === 'ai') placeholderSrc = '../illustrator.png';
        
        document.getElementById('onprocess-modal-design').src = placeholderSrc;
    }
    
    document.getElementById('onprocess-modal-process-date').textContent = orderData.date || new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Show/hide view button based on file type
    const viewButtons = document.querySelectorAll('#onProcessModal .view-design-btn');
    if (viewButtons.length > 0) {
        viewButtons.forEach(button => {
            if (orderData.viewable) {
                button.style.display = 'inline-block';
            } else {
                button.style.display = 'none';
            }
        });
    }

    // Show modal
    document.getElementById('onProcessModal').style.display = 'block';
}
// Setup image viewer and download buttons (attach only once)
function setupImageAndDownloadButtons() {
    // This function is now empty or can be removed
}

// Attach the event listener ONCE when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeModals();
    initializeOnProcessTable();
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-design-btn')) {
            const container = e.target.closest('.design-image-container');
            const imgElement = container.querySelector('img');
            const ticket = container.closest('.quote-modal-content').querySelector('#onprocess-modal-ticket').textContent;
            
            // Get the actual design file from the button's data attribute
            const viewButton = document.querySelector('.view-on-process-modal[data-ticket="' + ticket + '"]');
            const designFile = viewButton.getAttribute('data-design');
            
            // Check if it's an image format that can be displayed in browser
            const fileExtension = designFile.split('.').pop().toLowerCase();
            const displayableFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            
            if (displayableFormats.includes(fileExtension)) {
                // Show the actual image
                document.getElementById('expandedDesignImage').src = '../user/' + designFile;
                document.getElementById('imageViewerModal').style.display = 'block';
            } else {
                // This shouldn't happen since we hide the button, but just in case
                showToast('Cannot Preview', 'This file format cannot be previewed in the browser. Please download the file to view it.', 'warning');
            }
        }

        if (e.target.classList.contains('download-design-btn')) {
            const container = e.target.closest('.design-image-container');
            const ticket = container.closest('.quote-modal-content').querySelector('#onprocess-modal-ticket').textContent;
            const printType = container.closest('.quote-modal-content').querySelector('#onprocess-modal-print-type').textContent;
            
            // Get the actual design file from the button's data attribute
            const viewButton = document.querySelector('.view-on-process-modal[data-ticket="' + ticket + '"]');
            const designFile = viewButton.getAttribute('data-design');
            
            // Create download link for the actual file, not the thumbnail
            const link = document.createElement('a');
            link.href = '../user/' + designFile;
            
            // Extract filename and extension
            const filename = designFile.split('/').pop();
            const extension = filename.split('.').pop();
            
            link.download = `${ticket}-${printType.toLowerCase().replace(/ /g, '-')}.${extension}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });
});

// Initialize modals and event listeners
function initializeModals() {
    const onProcessModal = document.getElementById('onProcessModal');
    const confirmShipModal = document.getElementById('confirmShipModal');
    
    document.querySelectorAll('.quote-modal-close, #onprocess-modal-close').forEach(btn => {
    btn.addEventListener('click', function() {
        onProcessModal.style.display = 'none';
        resetViewButtonState(); // Reset view button state
    });
});
    document.getElementById('confirm-ship-no').addEventListener('click', function() {
        confirmShipModal.style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
    if (event.target === onProcessModal) {
        onProcessModal.style.display = 'none';
        resetViewButtonState(); // Reset view button state
    }
    if (event.target === confirmShipModal) {
        confirmShipModal.style.display = 'none';
    }
});
    // Ship button handler
    document.getElementById('onprocess-modal-ship').addEventListener('click', function() {
        confirmShipModal.style.display = 'block';
    });
    
    // Confirm shipment handler
    document.getElementById('confirm-ship-yes').addEventListener('click', function() {
        const id = document.getElementById('onprocess-modal-id').value;
        const userId = document.getElementById('onprocess-modal-user-id').value;
        const ticket = document.getElementById('onprocess-modal-ticket-input').value;
        const email = document.getElementById('onprocess-modal-email').value;
        const quantity = document.getElementById('onprocess-modal-quantity').textContent;
        const pricing = document.getElementById('onprocess-modal-pricing').textContent.replace('₱', '');
        const subtotal = document.getElementById('onprocess-modal-subtotal').textContent.replace('₱', '');
        const address = document.getElementById('onprocess-modal-address').textContent;
        
        // Show loading state
        const originalText = this.textContent;
        this.disabled = true;
        this.textContent = 'Processing...';
        
        // Send data to server
        fetch('api/confirm_shipment.php', {
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
                updateOnProcessTable(); // Refresh the table
                onProcessModal.style.display = 'none';
                confirmShipModal.style.display = 'none';
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

// Initialize the table when tab is active
function initializeOnProcessTable() {
    if (document.getElementById('on-process-table').style.display !== 'none') {
        updateOnProcessTable();
        setInterval(updateOnProcessTable, 3000);
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeModals();
    initializeOnProcessTable();
});

// Make functions available globally
window.updateOnProcessTable = updateOnProcessTable;
window.initializeOnProcessTable = initializeOnProcessTable;
</script>