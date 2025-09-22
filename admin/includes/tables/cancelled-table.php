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
            <!-- Content will be loaded via JavaScript -->
        </tbody>
    </table>
        <div class="pagination">
          <?php 
          // Build base URL with all parameters except page
          $base_url = "?tab=" . urlencode($active_tab);
          if (!empty($filter_print)) $base_url .= "&print_type=" . urlencode($filter_print);
          if (!empty($filter_start)) $base_url .= "&start_date=" . urlencode($filter_start);
          if (!empty($filter_end)) $base_url .= "&end_date=" . urlencode($filter_end);
          if (!empty($filter_search)) $base_url .= "&search=" . urlencode($filter_search);
          ?>
          
          <?php if ($page > 1): ?>
              <a href="<?= $base_url ?>&page=<?= $page - 1 ?>" class="btn btn-outline">&laquo; Prev</a>
          <?php endif; ?>

          <!-- Always show first page -->
          <a href="<?= $base_url ?>&page=1" class="btn <?= $page == 1 ? 'btn-primary' : 'btn-outline' ?>">1</a>

          <!-- Dots -->
          <?php if ($page > 3): ?>
              <span class="dots">...</span>
          <?php endif; ?>

          <!-- Pages around current -->
          <?php for ($i = max(2, $page - 2); $i <= min($total_pages - 1, $page + 2); $i++): ?>
              <a href="<?= $base_url ?>&page=<?= $i ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>">
                  <?= $i ?>
              </a>
          <?php endfor; ?>

          <!-- Dots -->
          <?php if ($page < $total_pages - 2): ?>
              <span class="dots">...</span>
          <?php endif; ?>

          <!-- Always show last page -->
          <?php if ($total_pages > 1): ?>
              <a href="<?= $base_url ?>&page=<?= $total_pages ?>" class="btn <?= $page == $total_pages ? 'btn-primary' : 'btn-outline' ?>">
                  <?= $total_pages ?>
              </a>
          <?php endif; ?>

          <?php if ($page < $total_pages): ?>
              <a href="<?= $base_url ?>&page=<?= $page + 1 ?>" class="btn btn-outline">Next &raquo;</a>
          <?php endif; ?>
      </div>
</div>

<!-- Cancelled Order Modal -->
<div id="cancelledModal" class="quote-modal">
  <div class="quote-modal-content">
    <!-- Top Close X -->
    <span class="cancelled-modal-close">&times;</span>

    <h2>Cancelled Order Details</h2>
    <div class="quote-modal-body">

      <!-- Ticket and Customer -->
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

      <!-- Order Details -->
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

      <!-- Pricing -->
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

      <!-- Items -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Items:</span>
        <div id="cancelled-modal-shirt-items" class="shirt-items-container"></div>
      </div>

      <!-- Timeline -->
      <!-- Timeline -->
<div class="quote-modal-timeline">
    <h3>Order Timeline</h3>
    <div class="timeline-item">
        <span class="timeline-label">Order Placed:</span>
        <span id="cancelled-modal-created" class="timeline-value"></span>
    </div>
    <div class="timeline-item">
        <span class="timeline-label">Design Approved:</span>
        <span id="cancelled-modal-designer-approved" class="timeline-value"></span>
    </div>
    <div class="timeline-item">
        <span class="timeline-label">Cancelled Date:</span>
        <span id="cancelled-modal-cancelled" class="timeline-value"></span>
    </div>
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

    <!-- Footer Close Button -->
    <div class="quote-modal-footer">
      <button id="cancelled-modal-close" class="quote-modal-btn btn-secondary">Close</button>
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

.status-cancelled {
    background-color: #f8d7da;
    color: #721c24;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}
</style>
<script>
// Helper function to get thumbnail path
function getCancelledThumbnailPath(designFilePath) {
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

// Download design file handler for cancelled orders
function handleCancelledDownloadDesign(event) {
    event.stopPropagation(); // Prevent event from bubbling
    
    const designFilePath = document.getElementById('cancelledModal').getAttribute('data-design-file');
    if (!designFilePath) {
        showToast('Download Error', 'No file available for download', 'error');
        return;
    }
    
    // Create a temporary link to trigger download
    const downloadLink = document.createElement('a');
    
    // Use absolute path to avoid confusion with includes
    downloadLink.href = '../user/' + designFilePath;
    
    // Extract filename from path for the download attribute
    const filename = designFilePath.split('/').pop();
    downloadLink.download = filename;
    
    // For security, set target to _blank to avoid navigation issues
    downloadLink.target = '_blank';
    
    // Trigger download
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
    
    // Show success toast
    showToast('Download', 'File download started', 'success');
}

// Function to fetch and update the cancelled orders table
function updateCancelledTable() {
        const printType = document.querySelector('select[name="print_type"]').value;
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const search = document.querySelector('input[name="search"]').value;
        const params = new URLSearchParams(new FormData(document.querySelector('.filter-form')));
            // Add the active tab to params
        const activeTab = document.querySelector('.tab-btn.active').getAttribute('data-tab');
        params.set('tab', activeTab); 
        fetch('api/get_cancelled_orders.php?' + params.toString())
        .then(response => response.text())
        .then(data => {
            document.getElementById('cancelled-table-body').innerHTML = data;
            attachCancelledViewButtonListeners();
        })
        .catch(error => {
            console.error('Error fetching cancelled orders:', error);
        });
}

// Function to attach event listeners to view buttons
function attachCancelledViewButtonListeners() {
    document.querySelectorAll('.view-cancelled-modal').forEach(button => {
        button.addEventListener('click', handleCancelledViewButtonClick);
    });
}

// View button click handler for cancelled orders
function handleCancelledViewButtonClick() {
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
    cancelled: this.getAttribute('data-cancelled'),
    reason: this.getAttribute('data-reason') || 'No reason provided'
  };

    let items = [];
    try {
        const rawItems = this.getAttribute('data-items');
        items = JSON.parse(rawItems || "[]");
    } catch (e) {
        console.error("Error parsing shirt items:", e);
    }

    // Store data in modal
    const cancelledModal = document.getElementById('cancelledModal');
    cancelledModal.setAttribute('data-current-id', orderData.id);
    cancelledModal.setAttribute('data-design-file', orderData.design);
    
    // Get correct thumbnail path
    const thumbnailPath = getCancelledThumbnailPath(orderData.design);
    
    // Populate modal fields
    document.getElementById('cancelled-modal-ticket').textContent = orderData.ticket;
    document.getElementById('cancelled-modal-name').textContent = orderData.name;
    document.getElementById('cancelled-modal-mobile').textContent = orderData.mobile || 'N/A';
    document.getElementById('cancelled-modal-email').textContent = orderData.email;
    document.getElementById('cancelled-modal-address').textContent = orderData.address || 'N/A';
    document.getElementById('cancelled-modal-print-type').textContent = orderData.printType;
    document.getElementById('cancelled-modal-quantity').textContent = orderData.quantity;
    document.getElementById('cancelled-modal-pricing').textContent = orderData.pricing ? '₱' + parseFloat(orderData.pricing).toFixed(2) : 'N/A';
    document.getElementById('cancelled-modal-subtotal').textContent = orderData.subtotal ? '₱' + parseFloat(orderData.subtotal).toFixed(2) : 'N/A';
    document.getElementById('cancelled-modal-note').textContent = orderData.note || 'No notes';
    document.getElementById('cancelled-modal-design').src = thumbnailPath;
    document.getElementById('cancelled-modal-reason').textContent = orderData.reason;
    
    // Populate timeline
  document.getElementById('cancelled-modal-created').textContent = formatCancelledDate(orderData.created);
  document.getElementById('cancelled-modal-designer-approved').textContent = formatCancelledDate(orderData.designerApproved);
  document.getElementById('cancelled-modal-cancelled').textContent = formatCancelledDate(orderData.cancelled);
    
    // Populate Shirt Colors & Quantities
    const itemsContainer = document.getElementById("cancelled-modal-shirt-items");
    itemsContainer.innerHTML = "";

    if (items.length > 0) {
        items.forEach(item => {
            const div = document.createElement("div");
            div.classList.add("shirt-item");
            div.innerHTML = `
              <span class="shirt-color">${item.shirt_color}</span>
              <span class="shirt-qty">${item.quantity}</span>
            `;
            itemsContainer.appendChild(div);
        });
    } else {
        itemsContainer.innerHTML = "<em>No shirt colors added</em>";
    }

    // Remove any existing event listeners from modal buttons first
    const downloadButtons = document.querySelectorAll('#cancelledModal .download-design-btn');
    
    downloadButtons.forEach(button => {
        button.replaceWith(button.cloneNode(true));
    });
    
    // Attach new event listeners to modal buttons
    document.querySelectorAll('#cancelledModal .download-design-btn').forEach(button => {
        button.addEventListener('click', handleCancelledDownloadDesign);
    });
    
    // Show modal
    cancelledModal.style.display = 'block';
}

// Format date for display
function formatCancelledDate(dateString) {
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

// Toast function
function showToast(title, message, type = 'info') {
    // Your existing toast implementation
    console.log(`${title}: ${message} (${type})`);
}

// Initialize modals and event listeners
function initializeCancelledModals() {
    const cancelledModal = document.getElementById('cancelledModal');
    
    // Close buttons
    document.querySelector('.cancelled-modal-close').addEventListener('click', function() {
        cancelledModal.style.display = 'none';
    });
    
    document.getElementById('cancelled-modal-close').addEventListener('click', function() {
        cancelledModal.style.display = 'none';
    });
    
    // Close when clicking outside modal
    window.addEventListener('click', function(event) {
        if (event.target === cancelledModal) {
            cancelledModal.style.display = 'none';
        }
    });
}

// Initialize the table when tab is active
function initializeCancelledTable() {
    if (document.getElementById('cancelled-table').style.display !== 'none') {
        updateCancelledTable();
        setInterval(updateCancelledTable, 3000);
    }
}


// Improved tab switching logic for all tabs
document.addEventListener('DOMContentLoaded', function() {
  initializeCancelledModals();

  // Map tab names to their content IDs
  var tabMap = {
    'to-pickup': 'to-pickup-table',
    'on-pickup': 'on-pickup-table',
    'to-ship': 'to-ship-table',
    'completed': 'completed-table',
    'cancel': 'cancelled-table'
  };

  // Tab button click handler
  document.querySelectorAll('.tab-btn').forEach(function(tabBtn) {
    tabBtn.addEventListener('click', function() {
      var tab = tabBtn.getAttribute('data-tab');

      // Remove active class from all tab buttons
      document.querySelectorAll('.tab-btn').forEach(function(btn) {
        btn.classList.remove('active');
      });
      tabBtn.classList.add('active');

      // Hide all tab contents
      document.querySelectorAll('.tab-content').forEach(function(tabContent) {
        tabContent.classList.remove('active');
        tabContent.style.display = 'none';
      });

      // Show the selected tab content
      var contentId = tabMap[tab];
      if (contentId) {
        var contentDiv = document.getElementById(contentId);
        if (contentDiv) {
          contentDiv.classList.add('active');
          contentDiv.style.display = 'block';
          // If Cancelled tab, initialize table
          if (tab === 'cancel') {
            initializeCancelledTable();
          }
        }
      }
    });
  });
});

// Make functions available globally
window.updateCancelledTable = updateCancelledTable;
window.initializeCancelledTable = initializeCancelledTable;
</script>