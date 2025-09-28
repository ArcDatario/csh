// ============================
// GLOBALS / SHARED
// ============================
let activeFiltersPickup = null; // stores current filters if applied

// Get DOM elements
const pickupModal = document.getElementById('pickupModal');
const pickupModalClose = document.querySelector('.quote-modal-close');
const confirmPickupBtn = document.getElementById('pickup-modal-confirm');
const closePickupBtn = document.getElementById('pickup-modal-close');


// ============================
// 1. REFRESH HANDLER (Pickup only)
// ============================
function updatePickupTable() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page_pickup') || 1;

    if (activeFiltersPickup) {
        activeFiltersPickup.delete('page');
        if (currentPage > 1) activeFiltersPickup.set('page', currentPage);
        applyPickupFilters(activeFiltersPickup);
    } else {
        const params = new URLSearchParams();
        if (currentPage > 1) params.set('page', currentPage);

        fetch('functions/get_pickup_orders.php?' + params.toString())
            .then(res => res.json())
            .then(data => renderPickupTable(data))
            .catch(err => console.error('Error refreshing pickup table:', err));
    }
}

// ============================
// 2. FILTERS (Pickup only)
// ============================
function applyPickupFilters(params) {
    fetch('functions/get_pickup_orders.php?' + params.toString())
        .then(res => res.json())
        .then(data => renderPickupTable(data))
        .catch(err => console.error('Error applying pickup filters:', err));
}

// ============================
// 3. INITIAL LOAD / MANUAL FILTER
// ============================
document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.querySelector('.filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            activeFiltersPickup = params;
            applyPickupFilters(params);
        });
    }

    const resetBtn = document.querySelector('.reset-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            activeFiltersPickup = null;
            updatePickupTable();
        });
    }

    attachPickupEventListeners();
    updatePickupTable();
});

// ============================
// 4. RENDER TABLE + PAGINATION
// ============================
function renderPickupTable(data) {
    const tbody = document.getElementById('pickup-table-body');
    const pagination = document.querySelector('#to-pickup-table .pagination');

    if (!tbody || !pagination) return;

    if (data.total_records === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center">No orders currently for pickup</td></tr>`;
        pagination.innerHTML = '';
    } else {
        tbody.innerHTML = data.table;
        pagination.innerHTML = data.pagination;
    }

    attachPickupViewButtonListeners();
    attachPickupPaginationListeners();
}

// ============================
// 5. PAGINATION HANDLER
// ============================
function attachPickupPaginationListeners() {
    document.querySelectorAll('#to-pickup-table .pagination a').forEach(link => {
        link.removeEventListener('click', handlePickupPageClick);
        link.addEventListener('click', handlePickupPageClick);
    });
}

function handlePickupPageClick(e) {
    e.preventDefault();
    const url = new URL(this.href);
    const page = url.searchParams.get('page') || 1;

    const params = activeFiltersPickup ? new URLSearchParams(activeFiltersPickup) : new URLSearchParams();
    params.set('page', page);

    fetch('functions/get_pickup_orders.php?' + params.toString())
        .then(res => res.json())
        .then(data => renderPickupTable(data))
        .catch(err => console.error('Error paginating pickup:', err));

    const newUrl = new URL(window.location);
    newUrl.searchParams.set('tab', 'to-pickup');
    newUrl.searchParams.set('page_pickup', page);
    window.history.replaceState({}, '', newUrl);
}

// ============================
// 6. VIEW BUTTONS
// ============================
function attachPickupViewButtonListeners() {
    document.querySelectorAll('.view-pickup-modal').forEach(button => {
        button.removeEventListener('click', handlePickupViewButtonClick);
        button.addEventListener('click', handlePickupViewButtonClick);
    });
}

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
    // Populate shirt colors & quantities
const shirtItemsContainer = document.getElementById('pickup-modal-shirt-items');
shirtItemsContainer.innerHTML = ''; // Clear previous content

const itemsData = this.getAttribute('data-items');
if (itemsData) {
    try {
        const shirtItems = JSON.parse(itemsData);
        if (shirtItems.length > 0) {
            shirtItems.forEach(item => {
        const div = document.createElement("div");
        div.classList.add("shirt-item");
        div.innerHTML = `
          <span class="shirt-color">${item.shirt_color}</span>
          <span class="shirt-qty">${item.quantity}</span>
        `;
        shirtItemsContainer.appendChild(div); // ✅ keep same container variable
      });
        } else {
            shirtItemsContainer.textContent = 'N/A';
        }
    } catch (e) {
        console.error('Failed to parse shirt items JSON', e);
        shirtItemsContainer.textContent = 'N/A';
    }
} else {
    shirtItemsContainer.textContent = 'N/A';
}

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

// ============================
// 7. MODAL ACTIONS & CLOSE
// ============================
// Modal close handlers
function closePickupModal() {
    pickupModal.style.display = 'none';
}

function handleWindowClick(event) {
    const imageViewerModal = document.getElementById('imageViewerModal');

    // Close pickup modal if clicking outside
    if (event.target === pickupModal) {
        closePickupModal();
    }

    // Close image viewer modal if clicking outside
    if (imageViewerModal && event.target === imageViewerModal) {
        imageViewerModal.style.display = 'none';
    }
}

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
            updatePickupTable(); // Refresh table after successful confirmation
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

// ============================
// 8. ATTACH LISTENERS
// ============================
function attachPickupEventListeners() {
    // View order buttons
    document.querySelectorAll('.view-pickup-modal').forEach(button => {
        button.removeEventListener('click', handlePickupViewButtonClick);
        button.addEventListener('click', handlePickupViewButtonClick);
    });

    // Download design buttons
    document.querySelectorAll('.download-design-btn').forEach(button => {
        button.removeEventListener('click', handleDownloadDesign);
        button.addEventListener('click', handleDownloadDesign);
    });

    // View design buttons
    document.querySelectorAll('.view-design-btn').forEach(button => {
        button.removeEventListener('click', handleViewDesign);
        button.addEventListener('click', handleViewDesign);
    });

    // Close modal
    const closePickupBtn = document.getElementById('pickup-modal-close');
    if (closePickupBtn) closePickupBtn.addEventListener('click', closePickupModal);

    // Confirm button
    const confirmPickupBtn = document.getElementById('pickup-modal-confirm');
    if (confirmPickupBtn) confirmPickupBtn.addEventListener('click', handleConfirmPickup);

    // Window click for closing modal
    window.addEventListener('click', handleWindowClick);
}

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
