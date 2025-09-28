<div id="cancelled-table" class="table-responsive tab-content">
    <table id="cancelled-orders-table">
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>Design</th>
                <th>Print Type</th>
                <th>Quantity</th>
                <th>Date Cancelled</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="cancelled-table-body">
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

<!-- Cancelled Order Modal -->
<div id="cancelledModal" class="quote-modal">
    <div class="quote-modal-content">
        <span class="cancelled-modal-close">&times;</span>
        <h2>Cancelled Order Details</h2>
        <div class="quote-modal-body">

            <!-- Ticket & Customer -->
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Ticket #:</span>
                    <span id="cancelled-modal-ticket" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Customer:</span>
                    <span id="cancelled-modal-name" class="quote-modal-value"></span>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Email:</span>
                    <span id="cancelled-modal-email" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Mobile #:</span>
                    <span id="cancelled-modal-mobile" class="quote-modal-value"></span>
                </div>
            </div>

            <!-- Address -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Address:</span>
                <span id="cancelled-modal-address" class="quote-modal-value address-value"></span>
            </div>

            <!-- Design -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Design:</span>
                <div class="design-image-container">
                    <img id="cancelled-modal-design" src="" alt="Design" class="design-image">
                    <div class="design-buttons">
                        <button class="download-design-btn">Download</button>
                    </div>
                </div>
            </div>

            <!-- Order Details & Pricing -->
            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Print Type:</span>
                    <span id="cancelled-modal-print-type" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Unit Price:</span>
                    <span id="cancelled-modal-pricing" class="quote-modal-value"></span>
                </div>
            </div>

            <div class="quote-modal-row grouped-row">
                <div class="grouped-item">
                    <span class="quote-modal-label">Total Quantity:</span>
                    <span id="cancelled-modal-quantity" class="quote-modal-value"></span>
                </div>
                <div class="grouped-item">
                    <span class="quote-modal-label">Subtotal:</span>
                    <span id="cancelled-modal-subtotal" class="quote-modal-value"></span>
                </div>
            </div>

            <!-- Shirt Items -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Items:</span>
                <div id="cancelled-modal-shirt-items" class="shirt-items-container"></div>
            </div>

            <!-- Timeline -->
            <div class="quote-modal-timeline">
                <h3>Order Timeline</h3>
                <div class="timeline-item"><span class="timeline-label">Order Placed:</span> <span id="cancelled-modal-created" class="timeline-value"></span></div>
                <div class="timeline-item"><span class="timeline-label">Design Approved:</span> <span id="cancelled-modal-designer-approved" class="timeline-value"></span></div>
                <div class="timeline-item"><span class="timeline-label">Cancelled Date:</span> <span id="cancelled-modal-cancelled" class="timeline-value"></span></div>
            </div>

            <!-- Notes -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Notes:</span>
                <span id="cancelled-modal-note" class="quote-modal-value note-value"></span>
            </div>

            <!-- Cancellation Reason -->
            <div class="quote-modal-row">
                <span class="quote-modal-label">Cancellation Reason:</span>
                <span id="cancelled-modal-reason" class="quote-modal-value note-value"></span>
            </div>
        </div>

        <div class="quote-modal-footer">
            <button id="cancelled-modal-close" class="quote-modal-btn btn-secondary">Close</button>
        </div>
    </div>
</div>

<style>
.quote-modal-timeline { margin-top:20px; padding-top:20px; border-top:1px solid #eee; }
.quote-modal-timeline h3 { margin-bottom:15px; font-size:1.1rem; color:#333; }
.timeline-item { display:flex; margin-bottom:8px; }
.timeline-label { font-weight:500; color:#666; min-width:150px; }
.timeline-value { color:#333; }
.status-cancelled { background-color:#f8d7da; color:#721c24; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:500; }
</style>

<script>
// ============================
// HELPERS
// ============================
function getCancelledThumbnailPath(path) {
    const ext = path.split('.').pop().toLowerCase();
    if (ext==='psd') return "../photoshop.png";
    if (ext==='pdf') return "../pdf.png";
    if (ext==='ai') return "../illustrator.png";
    return "../user/" + path;
}

function formatCancelledDate(dateStr) {
    if (!dateStr || dateStr==='0000-00-00 00:00:00') return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
}

// ============================
// DOWNLOAD HANDLER
// ============================
function handleCancelledDownloadDesign(event) {
    event.stopPropagation();
    const modal = document.getElementById('cancelledModal');
    const designFile = modal.getAttribute('data-design-file');
    if (!designFile) { showCancelledToast('Error','No file to download','error'); return; }

    const link = document.createElement('a');
    link.href = '../user/' + designFile;
    link.download = designFile.split('/').pop();
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showCancelledToast('Download','File download started','success');
}

// ============================
// GLOBALS / SHARED
// ============================
let activeFiltersCancelled = null; // stores current filters if applied

// ============================
// 1. REFRESH HANDLER (Cancelled only)
// ============================
function updateCancelledTable() {
    const urlParams = new URLSearchParams(window.location.search);
    let currentPage = urlParams.get('page_cancelled') || 1;

    if (activeFiltersCancelled) {
        activeFiltersCancelled.delete('page');
        if (currentPage > 1) activeFiltersCancelled.set('page', currentPage);
        applyCancelledFilters(activeFiltersCancelled);
    } else {
        const params = new URLSearchParams();
        if (currentPage > 1) params.set('page', currentPage);

        fetch('api/get_cancelled_orders.php?' + params.toString())
            .then(res => res.json())
            .then(data => renderCancelledTable(data))
            .catch(err => console.error('Error refreshing cancelled table:', err));
    }
}

// ============================
// 2. FILTERS (Cancelled only)
// ============================
function applyCancelledFilters(params) {
    fetch('api/get_cancelled_orders.php?' + params.toString())
        .then(res => res.json())
        .then(data => renderCancelledTable(data))
        .catch(err => console.error('Error applying cancelled filters:', err));
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
            activeFiltersCancelled = params;
            applyCancelledFilters(params);
        });
    }

    const resetBtn = document.querySelector('.cancelled-reset-filters');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            activeFiltersCancelled = null;
            updateCancelledTable();
        });
    }

    // Initial load
    updateCancelledTable();
});

// ============================
// 4. RENDER TABLE + PAGINATION
// ============================
function renderCancelledTable(data) {
    const tbody = document.getElementById('cancelled-table-body');
    const pagination = document.querySelector('#cancelled-table .pagination');

    if (!tbody || !pagination) return;

    if (data.total_records === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center">No cancelled orders found</td></tr>`;
        pagination.innerHTML = '';
    } else {
        tbody.innerHTML = data.table;
        pagination.innerHTML = data.pagination;
    }

    attachCancelledViewButtonListeners();
    attachCancelledPaginationListeners();
}

// ============================
// 5. PAGINATION BUTTON LISTENERS
// ============================
function attachCancelledPaginationListeners() {
    document.querySelectorAll('#cancelled-table .pagination a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const page = url.searchParams.get('page');
            if (!activeFiltersCancelled) activeFiltersCancelled = new URLSearchParams();
            activeFiltersCancelled.set('page', page);
            applyCancelledFilters(activeFiltersCancelled);
        });
    });
}

// ============================
// MODAL HANDLERS
// ============================
function attachCancelledViewButtonListeners() {
    document.querySelectorAll('.view-cancelled-modal').forEach(btn => btn.addEventListener('click', handleCancelledViewButtonClick));
}

function handleCancelledViewButtonClick() {
    const modal = document.getElementById('cancelledModal');
    const data = Object.fromEntries(Array.from(this.attributes).map(attr => [attr.name.replace('data-',''), attr.value]));

    modal.setAttribute('data-current-id', data.id);
    modal.setAttribute('data-design-file', data.design);

    document.getElementById('cancelled-modal-ticket').textContent = data.ticket;
    document.getElementById('cancelled-modal-name').textContent = data.name;
    document.getElementById('cancelled-modal-mobile').textContent = data.mobile || 'N/A';
    document.getElementById('cancelled-modal-email').textContent = data.email;
    document.getElementById('cancelled-modal-address').textContent = data.address || 'N/A';
    document.getElementById('cancelled-modal-print-type').textContent = data.print_type;
    document.getElementById('cancelled-modal-quantity').textContent = data.quantity;
    document.getElementById('cancelled-modal-pricing').textContent = data.pricing ? '₱'+parseFloat(data.pricing).toFixed(2) : 'N/A';
    document.getElementById('cancelled-modal-subtotal').textContent = data.subtotal ? '₱'+parseFloat(data.subtotal).toFixed(2) : 'N/A';
    document.getElementById('cancelled-modal-note').textContent = data.note || 'No notes';
    document.getElementById('cancelled-modal-reason').textContent = data.reason || 'No reason provided';
    document.getElementById('cancelled-modal-design').src = getCancelledThumbnailPath(data.design);

    // Shirt items
    const itemsContainer = modal.querySelector('#cancelled-modal-shirt-items');
    itemsContainer.innerHTML = '';
    let items = [];
    try { items = JSON.parse(data.items || '[]'); } catch(e){ console.error(e); }
    if (items.length) items.forEach(i => itemsContainer.innerHTML += `<div class="shirt-item"><span class="shirt-color">${i.shirt_color}</span> <span class="shirt-qty">${i.quantity}</span></div>`);
    else itemsContainer.innerHTML = "<em>No shirt colors added</em>";

    // Timeline
    document.getElementById('cancelled-modal-created').textContent = formatCancelledDate(data.created);
    document.getElementById('cancelled-modal-designer-approved').textContent = formatCancelledDate(data.designer_approved);
    document.getElementById('cancelled-modal-cancelled').textContent = formatCancelledDate(data.cancelled);

    // Attach download listener
    modal.querySelectorAll('.download-design-btn').forEach(btn => btn.replaceWith(btn.cloneNode(true)));
    modal.querySelectorAll('.download-design-btn').forEach(btn => btn.addEventListener('click', handleCancelledDownloadDesign));

    modal.style.display = 'block';
}

// ============================
// MODAL INIT
// ============================
function initializeCancelledModals() {
    const modal = document.getElementById('cancelledModal');
    modal.querySelector('.cancelled-modal-close').addEventListener('click', ()=>modal.style.display='none');
    modal.querySelector('#cancelled-modal-close').addEventListener('click', ()=>modal.style.display='none');
    window.addEventListener('click', e => { if(e.target===modal) modal.style.display='none'; });
}

// ============================
// INITIALIZE
// ============================
function initializeCancelledTable() {
    if (document.getElementById('cancelled-table').style.display!=='none') {
        updateCancelledTable();
    }
}

// Toast function
function showCancelledToast(title, message, type = 'info') {
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
    initializeCancelledModals();
});

window.updateCancelledTable = updateCancelledTable;
window.initializeCancelledTable = initializeCancelledTable;
</script>