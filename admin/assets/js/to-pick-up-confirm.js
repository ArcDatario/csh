// Get DOM elements
const pickupModal = document.getElementById('pickupModal');
const pickupModalClose = document.querySelector('.quote-modal-close');
const confirmPickupBtn = document.getElementById('pickup-modal-confirm');
const closePickupBtn = document.getElementById('pickup-modal-close');

// Helper function to get thumbnail path
function getThumbnailPath(designFilePath) {
    const filename = designFilePath.split('/').pop();
    const fileExtension = filename.split('.').pop().toLowerCase();
    
    if (fileExtension === 'psd') {
        return "../photoshop.png";
    } else if (fileExtension === 'pdf') {
        return "../pdf.png";
    } else if (fileExtension === 'ai') {
        return "../illustrator.png";
    } else {
        return "../user/" + designFilePath;
    }
}

// Check if file is an image
function isImageFile(designFilePath) {
    const filename = designFilePath.split('/').pop();
    const fileExtension = filename.split('.').pop().toLowerCase();
    return !['psd', 'pdf', 'ai'].includes(fileExtension);
}

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
    pickupModal.setAttribute('data-design-file', design); // Store the actual design file path
    document.getElementById('pickup-modal-id').value = id;
    document.getElementById('pickup-modal-user-id').value = userId;
    document.getElementById('pickup-modal-email').value = email;
    document.getElementById('pickup-modal-ticket').value = ticket;
    document.getElementById('pickup-modal-quantity').value = quantity;
    document.getElementById('pickup-modal-pricing').value = pricing;
    document.getElementById('pickup-modal-subtotal').value = subtotal;
    document.getElementById('pickup-modal-address').value = address;
    
    // Get correct thumbnail path
    const thumbnailPath = getThumbnailPath(design);
    
    // Populate modal fields
    document.getElementById('pickup-modal-ticket').textContent = ticket;
    document.getElementById('pickup-modal-name').textContent = name;
    document.getElementById('pickup-modal-design').src = thumbnailPath;
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

// Download design file handler
function handleDownloadDesign(event) {
    event.stopPropagation(); // Prevent event from bubbling
    
    const designFilePath = pickupModal.getAttribute('data-design-file');
    if (!designFilePath) return;
    
    // Create a temporary link to trigger download
    const downloadLink = document.createElement('a');
    downloadLink.href = '../user/' + designFilePath;
    
    // Extract filename from path
    const filename = designFilePath.split('/').pop();
    downloadLink.download = filename;
    
    // Trigger download
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

// View design in full size
function handleViewDesign(event) {
    event.stopPropagation(); // Prevent event from bubbling
    
    const designFilePath = pickupModal.getAttribute('data-design-file');
    if (!designFilePath) return;
    
    // Check if file is an image (not PSD, PDF, AI)
    if (isImageFile(designFilePath)) {
        // For images, show in viewer
        const imageViewerModal = document.getElementById('imageViewerModal');
        const expandedImage = document.getElementById('expandedDesignImage');
        const viewerLoading = document.getElementById('viewerLoading');
        
        // Show loading
        viewerLoading.style.display = 'block';
        expandedImage.style.display = 'none';
        
        expandedImage.src = '../user/' + designFilePath;
        expandedImage.onload = function() {
            viewerLoading.style.display = 'none';
            expandedImage.style.display = 'block';
        };
        expandedImage.onerror = function() {
            viewerLoading.style.display = 'none';
            showToast('Error', 'Failed to load image', 'error');
        };
        imageViewerModal.style.display = 'block';
    } else {
        // For non-image files, show toast message
        showToast('Info', 'Image viewing is not supported for this file type. Please download the file to view it.', 'info');
    }
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
    
    // Remove any existing event listeners from modal buttons first
    const downloadButtons = document.querySelectorAll('.download-design-btn');
    const viewButtons = document.querySelectorAll('.view-design-btn');
    
    downloadButtons.forEach(button => {
        button.replaceWith(button.cloneNode(true));
    });
    
    viewButtons.forEach(button => {
        button.replaceWith(button.cloneNode(true));
    });
    
    // Attach new event listeners to modal buttons
    document.querySelectorAll('.download-design-btn').forEach(button => {
        button.addEventListener('click', handleDownloadDesign);
    });
    
    document.querySelectorAll('.view-design-btn').forEach(button => {
        button.addEventListener('click', handleViewDesign);
    });
    
    // Modal close
    pickupModalClose.addEventListener('click', closePickupModal);
    closePickupBtn.addEventListener('click', closePickupModal);
    window.addEventListener('click', handleWindowClick);
    
    // Confirm button
    confirmPickupBtn.addEventListener('click', handleConfirmPickup);
}

// Toast function (FIXED VERSION)
function showToast(title, message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    
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
    
    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
    
    // Close button handler
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    });
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