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

    <!-- Pagination -->
    <div class="pagination">
        <?php 
        $base_url = "?tab=" . urlencode($active_tab);
        if (!empty($filter_print)) $base_url .= "&print_type=" . urlencode($filter_print);
        if (!empty($filter_start)) $base_url .= "&start_date=" . urlencode($filter_start);
        if (!empty($filter_end)) $base_url .= "&end_date=" . urlencode($filter_end);
        if (!empty($filter_search)) $base_url .= "&search=" . urlencode($filter_search);
        ?>
        
        <?php if ($page > 1): ?>
            <a href="<?= $base_url ?>&page=<?= $page - 1 ?>" class="btn btn-outline">&laquo; Prev</a>
        <?php endif; ?>

        <a href="<?= $base_url ?>&page=1" class="btn <?= $page == 1 ? 'btn-primary' : 'btn-outline' ?>">1</a>

        <?php if ($page > 3): ?><span class="dots">...</span><?php endif; ?>

        <?php for ($i = max(2, $page - 2); $i <= min($total_pages - 1, $page + 2); $i++): ?>
            <a href="<?= $base_url ?>&page=<?= $i ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages - 2): ?><span class="dots">...</span><?php endif; ?>

        <?php if ($total_pages > 1): ?>
            <a href="<?= $base_url ?>&page=<?= $total_pages ?>" class="btn <?= $page == $total_pages ? 'btn-primary' : 'btn-outline' ?>"><?= $total_pages ?></a>
        <?php endif; ?>

        <?php if ($page < $total_pages): ?>
            <a href="<?= $base_url ?>&page=<?= $page + 1 ?>" class="btn btn-outline">Next &raquo;</a>
        <?php endif; ?>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container"></div>

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

            <!-- Design -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Design:</span>
                <div class="design-image-container">
                    <img id="toship-modal-design" src="" alt="Design" class="design-image">
                    <div class="design-buttons">
                        <button class="download-design-btn">Download</button>
                    </div>
                </div>
            </div>

            <!-- Print Type & Quantity -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Print Type:</span>
                <span id="toship-modal-print-type" class="quote-modal-value"></span>
            </div>
            <div class="quote-modal-row">
                <span class="quote-modal-label">Quantity:</span>
                <span id="toship-modal-quantity" class="quote-modal-value"></span>
            </div>

            <!-- Shirt Items -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Items:</span>
                <div id="quote-modal-shirt-items" class="shirt-items-container"></div>
            </div>

            <!-- Pricing -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Unit Price:</span>
                <span id="toship-modal-pricing" class="quote-modal-value"></span>
            </div>
            <div class="quote-modal-row">
                <span class="quote-modal-label">Subtotal:</span>
                <span id="toship-modal-subtotal" class="quote-modal-value"></span>
            </div>

            <!-- Notes & Shipping Date -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Notes:</span>
                <span id="toship-modal-note" class="quote-modal-value"></span>
            </div>
            <div class="quote-modal-row">
                <span class="quote-modal-label">Shipping Date:</span>
                <span id="toship-modal-ship-date" class="quote-modal-value"></span>
            </div>

            <!-- Hidden Inputs -->
            <input type="hidden" id="toship-modal-id" name="id">
            <input type="hidden" id="toship-modal-user-id" name="user_id">
            <input type="hidden" id="toship-modal-ticket-input" name="ticket">
            <input type="hidden" id="toship-modal-design-file" name="design_file">
        </div>

        <div class="quote-modal-footer">
            <button id="toship-modal-complete" class="quote-modal-btn btn-process">Mark as Shipped</button>
            <button id="toship-modal-close-btn" class="quote-modal-btn btn-close">Close</button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
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
// ============================
// Helper: Thumbnail Path
// ============================
function getToShipThumbnailPath(designFilePath) {
    const filename = designFilePath.split('/').pop();
    const fileExtension = filename.split('.').pop().toLowerCase();

    if (fileExtension === 'psd') return "../photoshop.png";
    if (fileExtension === 'pdf') return "../pdf.png";
    if (fileExtension === 'ai') return "../illustrator.png";
    return "../user/" + designFilePath;
}

// ============================
// Download Design Handler
// ============================
function handleToShipDownloadDesign(event) {
    event.stopPropagation();
    const designFilePath = document.getElementById('toship-modal-design-file').value;
    if (!designFilePath) {
        showToShipToast('Download Error', 'No file available for download', 'error');
        return;
    }
    const downloadLink = document.createElement('a');
    downloadLink.href = '../user/' + designFilePath;
    downloadLink.download = designFilePath.split('/').pop();
    downloadLink.target = '_blank';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
    showToShipToast('Download', 'File download started', 'success');
}

// ============================
// GLOBALS / SHARED
// ============================
let activeFiltersToShip = null; // stores current filters if applied

// ============================
// 1. REFRESH HANDLER (To-Ship only)
// ============================
function updateToShipTable() {
    const urlParams = new URLSearchParams(window.location.search);
    let currentPage = urlParams.get('page_toship') || 1;

    if (activeFiltersToShip) {
        activeFiltersToShip.delete('page');
        if (currentPage > 1) activeFiltersToShip.set('page', currentPage);
        applyToShipFilters(activeFiltersToShip);
    } else {
        const params = new URLSearchParams();
        if (currentPage > 1) params.set('page', currentPage);

        fetch('api/get_toship_orders.php?' + params.toString())
            .then(res => res.json())
            .then(data => renderToShipTable(data))
            .catch(err => console.error('Error refreshing to-ship table:', err));
    }
}

// ============================
// 2. FILTERS (To-Ship only)
// ============================
function applyToShipFilters(params) {
    fetch('api/get_toship_orders.php?' + params.toString())
        .then(res => res.json())
        .then(data => renderToShipTable(data))
        .catch(err => console.error('Error applying to-ship filters:', err));
}

// ============================
// 3. INITIAL LOAD / MANUAL FILTER
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('.filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            activeFiltersToShip = params;
            applyToShipFilters(params);
        });
    }

    const resetBtn = document.querySelector('.reset-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            activeFiltersToShip = null;
            updateToShipTable();
        });
    }

    // Initial load
    updateToShipTable();
});

// ============================
// 4. RENDER TABLE + PAGINATION
// ============================
function renderToShipTable(data) {
    const tbody = document.getElementById('toship-table-body');
    const pagination = document.querySelector('#to-ship-table .pagination');

    if (!tbody || !pagination) return;

    if (data.total_records === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center">No orders currently to ship</td></tr>`;
        pagination.innerHTML = '';
    } else {
        tbody.innerHTML = data.table;
        pagination.innerHTML = data.pagination;
    }

    attachToShipViewButtonListeners();
    attachToShipPaginationListeners();
}

// ============================
// 5. PAGINATION BUTTON LISTENERS
// ============================
function attachToShipPaginationListeners() {
    document.querySelectorAll('#to-ship-table .pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const page = url.searchParams.get('page');
            if (!activeFiltersToShip) activeFiltersToShip = new URLSearchParams();
            activeFiltersToShip.set('page', page);
            applyToShipFilters(activeFiltersToShip);
        });
    });
}


// ============================
// Attach View Button Listeners
// ============================
function attachToShipViewButtonListeners() {
    document.querySelectorAll('.view-to-ship-modal').forEach(button => {
        button.addEventListener('click', handleToShipViewButtonClick);
    });
}

// ============================
// View Button Click Handler
// ============================
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

    // Parse shirt items
    let items = [];
    try {
        const rawItems = this.getAttribute('data-items');
        items = JSON.parse(rawItems || "[]");
    } catch (e) {
        console.error("Error parsing shirt items:", e);
    }

    // Populate modal
    const modal = document.getElementById('toShipModal');
    modal.setAttribute('data-current-id', orderData.id);
    document.getElementById('toship-modal-id').value = orderData.id;
    document.getElementById('toship-modal-user-id').value = orderData.userId;
    document.getElementById('toship-modal-email').value = orderData.email;
    document.getElementById('toship-modal-ticket-input').value = orderData.ticket;
    document.getElementById('toship-modal-design-file').value = orderData.design;

    const thumbnailPath = getToShipThumbnailPath(orderData.design);

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
    document.getElementById('toship-modal-design').src = thumbnailPath;
    document.getElementById('toship-modal-ship-date').textContent = orderData.date || new Date().toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });

    // Populate shirt items
    const itemsContainer = modal.querySelector("#quote-modal-shirt-items");
    itemsContainer.innerHTML = "";
    if (items.length > 0) {
        items.forEach(item => {
            const div = document.createElement("div");
            div.classList.add("shirt-item");
            div.innerHTML = `<span class="shirt-color">${item.shirt_color}</span><span class="shirt-qty">${item.quantity}</span>`;
            itemsContainer.appendChild(div);
        });
    } else {
        itemsContainer.innerHTML = "<em>No shirt colors added</em>";
    }

    // Reset modal buttons
    modal.querySelectorAll('.download-design-btn').forEach(button => {
        button.replaceWith(button.cloneNode(true));
    });
    modal.querySelectorAll('.download-design-btn').forEach(button => {
        button.addEventListener('click', handleToShipDownloadDesign);
    });

    modal.style.display = 'block';
}

// ============================
// Initialize Modals
// ============================
function initializeToShipModals() {
    const toShipModal = document.getElementById('toShipModal');
    const confirmModal = document.getElementById('toship-confirm-modal');

    document.querySelector('.toship-modal-close').addEventListener('click', () => toShipModal.style.display = 'none');
    document.getElementById('toship-modal-close-btn').addEventListener('click', () => toShipModal.style.display = 'none');
    document.getElementById('toship-confirm-no').addEventListener('click', () => confirmModal.style.display = 'none');

    window.addEventListener('click', (event) => {
        if (event.target === toShipModal) toShipModal.style.display = 'none';
        if (event.target === confirmModal) confirmModal.style.display = 'none';
    });

    // Complete button
    document.getElementById('toship-modal-complete').addEventListener('click', () => confirmModal.style.display = 'block');

    // Confirm delivery
    document.getElementById('toship-confirm-yes').addEventListener('click', function() {
        const id = document.getElementById('toship-modal-id').value;
        const userId = document.getElementById('toship-modal-user-id').value;
        const ticket = document.getElementById('toship-modal-ticket-input').value;
        const email = document.getElementById('toship-modal-email').value;
        const quantity = document.getElementById('toship-modal-quantity').textContent;
        const pricing = document.getElementById('toship-modal-pricing').textContent.replace('₱', '');
        const subtotal = document.getElementById('toship-modal-subtotal').textContent.replace('₱', '');
        const address = document.getElementById('toship-modal-address').textContent;

        const originalText = this.textContent;
        this.disabled = true;
        this.textContent = 'Processing...';

        fetch('api/confirm_delivery.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, user_id: userId, email, ticket, quantity, pricing, subtotal, address })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToShipToast('Success', data.message, 'success');
                updateToShipTable();
                toShipModal.style.display = 'none';
                confirmModal.style.display = 'none';
            } else {
                showToShipToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            showToShipToast('Error', 'An error occurred while updating order', 'error');
            console.error(error);
        })
        .finally(() => {
            this.disabled = false;
            this.textContent = originalText;
        });
    });
}

// ============================
// Initialize Table
// ============================
function initializeToShipTable() {
    if (document.getElementById('to-ship-table').style.display !== 'none') {
        updateToShipTable();
    }
}

// ============================
// Toast Function
// ============================
function showToShipToast(title, message, type='info') {
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
            <i class="fas ${type==='success'?'fa-check':type==='error'?'fa-times':type==='warning'?'fa-exclamation':'fa-info'}"></i>
        </div>
        <div class="toast-content">
            <h4 class="toast-title">${title}</h4>
            <p class="toast-message">${message}</p>
        </div>
        <button class="toast-close">&times;</button>
    `;
    toastContainer.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => { toast.classList.remove('show'); setTimeout(()=>toast.remove(),300); }, 5000);

    toast.querySelector('.toast-close').addEventListener('click', () => { toast.classList.remove('show'); setTimeout(()=>toast.remove(),300); });
}

// ============================
// DOMContentLoaded
// ============================
document.addEventListener('DOMContentLoaded', function() {
    initializeToShipModals();
    initializeToShipTable();
    window.updateToShipTable = updateToShipTable;
    window.initializeToShipTable = initializeToShipTable;
});
</script>
