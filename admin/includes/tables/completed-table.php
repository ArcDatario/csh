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
            <!-- Content loaded via JS -->
        </tbody>
    </table>

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

<!-- Completed Order Modal -->
<div id="completedModal" class="quote-modal">
    <div class="quote-modal-content">
        <span class="completed-modal-close">&times;</span>
        <h2>Completed Order Details</h2>
        <div class="quote-modal-body">

            <!-- Ticket & Customer -->
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
                        <button class="download-design-btn">Download</button>
                    </div>
                </div>
            </div>

            <!-- Order Details & Pricing -->
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Print Type:</span>
                    <span id="completed-modal-print-type" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Unit Price:</span>
                    <span id="completed-modal-pricing" class="quote-modal-value"></span>
                </div>
            </div>

            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Total Quantity:</span>
                    <span id="completed-modal-quantity" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Subtotal:</span>
                    <span id="completed-modal-subtotal" class="quote-modal-value"></span>
                </div>
            </div>

            <!-- Shirt Items -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Items:</span>
                <div id="completed-modal-shirt-items" class="shirt-items-container"></div>
            </div>

            <!-- Timeline -->
            <div class="quote-modal-timeline">
                <h3>Order Timeline</h3>
                <div class="timeline-item"><span class="timeline-label">Order Placed:</span> <span id="completed-modal-created" class="timeline-value"></span></div>
                <div class="timeline-item"><span class="timeline-label">Design Approved:</span> <span id="completed-modal-designer-approved" class="timeline-value"></span></div>
                <div class="timeline-item"><span class="timeline-label">Admin Approved:</span> <span id="completed-modal-admin-approved" class="timeline-value"></span></div>
                <div class="timeline-item"><span class="timeline-label">Processing Started:</span> <span id="completed-modal-processing" class="timeline-value"></span></div>
                <div class="timeline-item"><span class="timeline-label">Pickup Date:</span> <span id="completed-modal-pickup" class="timeline-value"></span></div>
                <div class="timeline-item"><span class="timeline-label">Shipping Date:</span> <span id="completed-modal-shipping" class="timeline-value"></span></div>
                <div class="timeline-item"><span class="timeline-label">Completed Date:</span> <span id="completed-modal-completed" class="timeline-value"></span></div>
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
.quote-modal-timeline { margin-top:20px; padding-top:20px; border-top:1px solid #eee; }
.quote-modal-timeline h3 { margin-bottom:15px; font-size:1.1rem; color:#333; }
.timeline-item { display:flex; margin-bottom:8px; }
.timeline-label { font-weight:500; color:#666; min-width:150px; }
.timeline-value { color:#333; }
</style>

<script>
// ============================
// HELPERS
// ============================
function getCompletedThumbnailPath(path) {
    const ext = path.split('.').pop().toLowerCase();
    if (ext==='psd') return "../photoshop.png";
    if (ext==='pdf') return "../pdf.png";
    if (ext==='ai') return "../illustrator.png";
    return "../user/" + path;
}

function formatCompletedDate(dateStr) {
    if (!dateStr || dateStr==='0000-00-00 00:00:00') return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
}

// ============================
// DOWNLOAD HANDLER
// ============================
function handleCompletedDownloadDesign(event) {
    event.stopPropagation();
    const modal = document.getElementById('completedModal');
    const designFile = modal.getAttribute('data-design-file');
    if (!designFile) { showCompletedToast('Error','No file to download','error'); return; }

    const link = document.createElement('a');
    link.href = '../user/' + designFile;
    link.download = designFile.split('/').pop();
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showCompletedToast('Download','File download started','success');
}

// ============================
// GLOBALS / SHARED
// ============================
let activeFiltersCompleted = null; // stores current filters if applied

// ============================
// 1. REFRESH HANDLER (Completed only)
// ============================
function updateCompletedTable() {
    const urlParams = new URLSearchParams(window.location.search);
    let currentPage = urlParams.get('page_completed') || 1;

    if (activeFiltersCompleted) {
        activeFiltersCompleted.delete('page');
        if (currentPage > 1) activeFiltersCompleted.set('page', currentPage);
        applyCompletedFilters(activeFiltersCompleted);
    } else {
        const params = new URLSearchParams();
        if (currentPage > 1) params.set('page', currentPage);

        fetch('api/get_completed_orders.php?' + params.toString())
            .then(res => res.json())
            .then(data => renderCompletedTable(data))
            .catch(err => console.error('Error refreshing completed table:', err));
    }
}

// ============================
// 2. FILTERS (Completed only)
// ============================
function applyCompletedFilters(params) {
    fetch('api/get_completed_orders.php?' + params.toString())
        .then(res => res.json())
        .then(data => renderCompletedTable(data))
        .catch(err => console.error('Error applying completed filters:', err));
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
            activeFiltersCompleted = params;
            applyCompletedFilters(params);
        });
    }

    const resetBtn = document.querySelector('.completed-reset-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            activeFiltersCompleted = null;
            updateCompletedTable();
        });
    }

    // Initial load
    updateCompletedTable();
});

// ============================
// 4. RENDER TABLE + PAGINATION
// ============================
function renderCompletedTable(data) {
    const tbody = document.getElementById('completed-table-body');
    const pagination = document.querySelector('#completed-table .pagination');

    if (!tbody || !pagination) return;

    if (data.total_records === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center">No completed orders found</td></tr>`;
        pagination.innerHTML = '';
    } else {
        tbody.innerHTML = data.table;
        pagination.innerHTML = data.pagination;
    }

    attachCompletedViewButtonListeners();
    attachCompletedPaginationListeners();
}

// ============================
// 5. PAGINATION BUTTON LISTENERS
// ============================
function attachCompletedPaginationListeners() {
    document.querySelectorAll('#completed-table .pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const page = url.searchParams.get('page');
            if (!activeFiltersCompleted) activeFiltersCompleted = new URLSearchParams();
            activeFiltersCompleted.set('page', page);
            applyCompletedFilters(activeFiltersCompleted);
        });
    });
}

// ============================
// MODAL HANDLERS
// ============================
function attachCompletedViewButtonListeners() {
    document.querySelectorAll('.view-completed-modal').forEach(btn => btn.addEventListener('click', handleCompletedViewButtonClick));
}

function handleCompletedViewButtonClick() {
    const modal = document.getElementById('completedModal');
    const data = Object.fromEntries(Array.from(this.attributes).map(attr => [attr.name.replace('data-',''), attr.value]));

    modal.setAttribute('data-current-id', data.id);
    modal.setAttribute('data-design-file', data.design);

    document.getElementById('completed-modal-ticket').textContent = data.ticket;
    document.getElementById('completed-modal-name').textContent = data.name;
    document.getElementById('completed-modal-mobile').textContent = data.mobile || 'N/A';
    document.getElementById('completed-modal-email').textContent = data.email;
    document.getElementById('completed-modal-address').textContent = data.address || 'N/A';
    document.getElementById('completed-modal-print-type').textContent = data.print_type;
    document.getElementById('completed-modal-quantity').textContent = data.quantity;
    document.getElementById('completed-modal-pricing').textContent = data.pricing ? '₱'+parseFloat(data.pricing).toFixed(2) : 'N/A';
    document.getElementById('completed-modal-subtotal').textContent = data.subtotal ? '₱'+parseFloat(data.subtotal).toFixed(2) : 'N/A';
    document.getElementById('completed-modal-note').textContent = data.note || 'No notes';
    document.getElementById('completed-modal-design').src = getCompletedThumbnailPath(data.design);

    // Shirt items
    const itemsContainer = modal.querySelector('#completed-modal-shirt-items');
    itemsContainer.innerHTML = '';
    let items = [];
    try { items = JSON.parse(data.items || '[]'); } catch(e){ console.error(e); }
    if (items.length) items.forEach(i => itemsContainer.innerHTML += `<div class="shirt-item"><span class="shirt-color">${i.shirt_color}</span> <span class="shirt-qty">${i.quantity}</span></div>`);
    else itemsContainer.innerHTML = "<em>No shirt colors added</em>";

    // Timeline
    document.getElementById('completed-modal-created').textContent = formatCompletedDate(data.created);
    document.getElementById('completed-modal-designer-approved').textContent = formatCompletedDate(data.designer_approved);
    document.getElementById('completed-modal-admin-approved').textContent = formatCompletedDate(data.admin_approved);
    document.getElementById('completed-modal-processing').textContent = formatCompletedDate(data.processing);
    document.getElementById('completed-modal-pickup').textContent = formatCompletedDate(data.pickup);
    document.getElementById('completed-modal-shipping').textContent = formatCompletedDate(data.shipping);
    document.getElementById('completed-modal-completed').textContent = formatCompletedDate(data.completed);

    // Attach download listener
    modal.querySelectorAll('.download-design-btn').forEach(btn => btn.replaceWith(btn.cloneNode(true)));
    modal.querySelectorAll('.download-design-btn').forEach(btn => btn.addEventListener('click', handleCompletedDownloadDesign));

    modal.style.display = 'block';
}

// ============================
// MODAL INIT
// ============================
function initializeCompletedModals() {
    const modal = document.getElementById('completedModal');
    modal.querySelector('.completed-modal-close').addEventListener('click', ()=>modal.style.display='none');
    modal.querySelector('#completed-modal-close').addEventListener('click', ()=>modal.style.display='none');
    window.addEventListener('click', e => { if(e.target===modal) modal.style.display='none'; });
}

// ============================
// INITIALIZE
// ============================
function initializeCompletedTable() {
    if (document.getElementById('completed-table').style.display!=='none') {
        updateCompletedTable();
    }
}
// Toast function
function showCompletedToast(title, message, type = 'info') {
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
// ============================
// DOM READY
// ============================
document.addEventListener('DOMContentLoaded', function() {
    initializeCompletedModals();
});

window.updateCompletedTable = updateCompletedTable;
window.initializeCompletedTable = initializeCompletedTable;
</script>
