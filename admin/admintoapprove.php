<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Safely check for Field Manager role and redirect
if (isset($_SESSION['admin_role'])) {
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if ($_SESSION['admin_role'] === "Field Manager" && $current_page != 'inventory.php') {
        header('Location: inventory.php');
        exit();
    }
    
    if ($_SESSION['admin_role'] === "Designer" && $current_page != 'orders.php') {
        header('Location: orders.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Dashboard</title>
    <link rel="icon" href="assets/images/analysis.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admintoapprove.css">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <button class="mobile-menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

        <?php include "includes/sidebar.php";?>
    
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Main Content -->
        <main class="main">
            <header class="header">
                <h1 class="header-dashboard">Dashboard</h1>
                
                <div class="user-menu">
                <div class="theme-toggle" id="themeToggle">
                <span style="margin-right:8px;">Dark Mode</span>
                <i class="fas fa-moon"></i>
            </div>
                    
          <?php include "includes/notification.php";?>

    </div>
                    
                    <div class="user-avatar">Csh</div>
                </div>
            </header>
            
            <!-- Cards Grid -->
           
            
            <!-- Table -->
            <section class="table-card fade-in">
                <div class="table-header">
        <h3 class="table-title">Users</h3>
        <div class="table-actions">
            <button class="btn btn-outline">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
        </div>
    </div>
                
                <div class="table-responsive">
                <table id="admins-table" class="">
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
    <?php
    require_once 'auth_check.php';
    require_once '../db_connection.php';

    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }

    // Fetch orders with user names from the database
   
    $sql = "SELECT orders.*, users.name, users.phone_number 
            FROM orders 
            INNER JOIN users ON orders.user_id = users.id 
            WHERE orders.status = 'pending' 
            ORDER BY orders.created_at DESC";
    $result = $conn->query($sql);
    ?>
    <tbody id="admins-table-body">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($order = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <div class="user-cell">
                            <img src="../user/<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>" alt="file design" width="50" height="50">
                            <span><?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <span class="status status-warning">
                            <?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </td>
                    <td>
                     
<button class="btn btn-outline view-quote-modal" 
        data-id="<?php echo htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?>"
        data-user-id="<?php echo htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8'); ?>"
        data-pricing="<?php echo htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8'); ?>"
        data-subtotal="<?php echo htmlspecialchars($order['subtotal'], ENT_QUOTES, 'UTF-8'); ?>"
        data-ticket="<?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?>"
        data-design="<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>"
        data-mobile="<?php echo htmlspecialchars($order['phone_number'], ENT_QUOTES, 'UTF-8'); ?>"
        data-name="<?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?>"
        data-print-type="<?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?>"
        data-quantity="<?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?>"
        data-date="<?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?>"
        data-status="<?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>"
        data-note="<?php echo htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8'); ?>"
        data-address="<?php echo htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8'); ?>">
    View
</button>

                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No orders found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
                </div>
            </section>
        </main>
</div>
  
<div id="quoteModal" class="quote-modal">
  <div class="quote-modal-content">
    <span class="quote-modal-close">&times;</span>
    <h2>Order Details</h2>
    <div class="quote-modal-body">
      <!-- Group 1: Ticket and Customer in one row -->
      <div class="quote-modal-row grouped-row">
        <div class="grouped-item">
          <span class="quote-modal-label">Ticket #:</span>
          <span id="quote-modal-ticket" class="quote-modal-value"></span>
        </div>
        <div class="grouped-item">
          <span class="quote-modal-label">Customer:</span>
          <span id="quote-modal-name" class="quote-modal-value"></span>
        </div>
      </div>
      
      <!-- Group 2: Image with buttons and details -->
      <div class="quote-modal-row grouped-row-2">
        <div class="grouped-item">
          <span class="quote-modal-label">Design:</span>
          <div class="design-image-container">
            <img id="quote-modal-design" src="" alt="Design" class="design-image">
            <div class="design-buttons">
              <button class="view-design-btn">View</button>
              <button class="download-design-btn">Download</button>
            </div>
          </div>
        </div>
        <div class="grouped-item details-column">
          <div class="detail-row">
            <span class="quote-modal-label">Print Type:</span>
            <span id="quote-modal-print-type" class="quote-modal-value"></span>
          </div>
          <div class="detail-row">
            <span class="quote-modal-label">Quantity:</span>
            <span id="quote-modal-quantity" class="quote-modal-value"></span>
          </div>
          <div class="detail-row">
            <span class="quote-modal-label">Mobile #:</span>
            <span id="quote-modal-mobile" class="quote-modal-value"></span>
          </div>
        </div>
      </div>
      
      <!-- Note -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Note:</span>
        <span id="quote-modal-note" class="quote-modal-value note-value"></span>
      </div>
      
      <!-- Group 3: Date and Status -->
      <div class="quote-modal-row grouped-row">
        <div class="grouped-item">
          <span class="quote-modal-label">Date:</span>
          <span id="quote-modal-date" class="quote-modal-value"></span>
        </div>
        <div class="grouped-item">
          <span class="quote-modal-label">Status:</span>
          <span id="quote-modal-status" class="quote-modal-value"></span>
        </div>
      </div>
      
      <!-- Address -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Address:</span>
        <span id="quote-modal-address" class="quote-modal-value address-value"></span>
      </div>

      <div class="quote-modal-row grouped-row">
        <div class="grouped-item">
          <span class="quote-modal-label">Price per pcs:</span>
          <span id="quote-modal-price" class="quote-modal-price-value"></span>
        </div>
        <div class="grouped-item">
          <span class="quote-modal-label">Subtotal:</span>
          <span id="quote-modal-subtotal" class="quote-modal-subtotal-value"></span>
        </div>
      </div>
      
      <!-- Subtotal -->
      <div class="quote-modal-row">
      <input type="number" id="subtotal-value" class="subtotal-value" name="subtotal" hidden>
      <input type="number" id="pricing-value" class="pricing-value" name="pricing" hidden>

      <input type="number" id="user_id" class="user_id" name="user_id" hidden>

      <input type="number" id="ticket-value-input" class="ticket-value-input" name="ticket-value-input" hidden>

      
        <span id="subtotal-text" class="subtotal-text">Updated: ₱</span>
      </div>
    </div>
    <div class="quote-modal-footer">
      <input type="number" id="quote-modal-input" placeholder="Update Price per pcs" name="price">

      <button id="quote-modal-save" class="quote-modal-btn">Approved</button>
    </div>
  </div>
</div>

<!-- Image Viewer Modal -->
<div id="imageViewerModal" class="image-viewer-modal">
  <span class="close-viewer">&times;</span>
  <img class="image-viewer-content" id="expandedDesignImage">
  <div id="viewerLoading" class="viewer-loading">Loading...</div>
</div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

   

<script src="assets/js/script.js"></script>

<script>
// Get DOM elements
const quoteModal = document.getElementById('quoteModal');
const quoteModalClose = document.querySelector('.quote-modal-close');
const priceInput = document.getElementById('quote-modal-input');
const quantitySpan = document.getElementById('quote-modal-quantity');
const subtotalInput = document.getElementById('subtotal-value');
const subtotalText = document.getElementById('subtotal-text');
const saveBtn = document.getElementById('quote-modal-save');

// Price calculation handler
function handlePriceCalculation() {
    const pricePerPiece = parseFloat(priceInput.value.trim());
    const quantity = parseInt(quantitySpan.textContent.trim());

    if (!isNaN(pricePerPiece) && pricePerPiece >= 0 && !isNaN(quantity) && quantity > 0) {
        const subtotal = pricePerPiece * quantity;
        subtotalInput.value = subtotal.toFixed(2);
        subtotalText.textContent = `Updated: ₱${subtotal.toFixed(2)}`;
    } else {
        subtotalInput.value = '';
        subtotalText.textContent = 'Updated: ₱0.00';
    }
}

// ...existing code...

function handleViewButtonClick() {
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
    const pricing = this.getAttribute('data-pricing'); // Get pricing value
    const subtotal = this.getAttribute('data-subtotal'); // Get subtotal value

    // Store data in modal
    quoteModal.setAttribute('data-current-id', id);

    // Populate modal fields
    document.getElementById('quote-modal-ticket').textContent = ticket;
    document.getElementById('quote-modal-name').textContent = name;
    document.getElementById('quote-modal-design').src = '../user/' + design;
    document.getElementById('quote-modal-print-type').textContent = printType;
    document.getElementById('quote-modal-quantity').textContent = quantity;
    document.getElementById('quote-modal-date').textContent = date;
    document.getElementById('quote-modal-status').textContent = status;
    document.getElementById('quote-modal-note').textContent = note || 'N/A';
    document.getElementById('quote-modal-address').textContent = address || 'N/A';
    document.getElementById('quote-modal-mobile').textContent = mobile || 'N/A';
    document.getElementById('user_id').value = userId;
    document.getElementById('ticket-value-input').value = ticket;

    // Populate pricing and subtotal fields
    document.getElementById('quote-modal-price').textContent = pricing ? `₱${parseFloat(pricing).toFixed(2)}` : 'N/A';
    document.getElementById('quote-modal-subtotal').textContent = subtotal ? `₱${parseFloat(subtotal).toFixed(2)}` : 'N/A';

    // Set the values for the input fields as requested
    document.getElementById('pricing-value').value = pricing ? parseFloat(pricing) : '';
    document.getElementById('subtotal-value').value = subtotal ? parseFloat(subtotal) : '';

    // Reset input fields for manual update
    priceInput.value = '';
    subtotalText.textContent = 'Updated: ₱0.00';

    // Show modal
    quoteModal.style.display = 'block';
}

// ...existing code...

// Save quote handler
function handleSaveQuote() {
    const quoteAmount = priceInput.value;
    const subtotalAmount = subtotalInput.value;
    const id = quoteModal.getAttribute('data-current-id');
    const userId = document.getElementById('user_id').value;
    const ticket = document.getElementById('ticket-value-input').value;
    const quantity = document.getElementById('quote-modal-quantity').textContent.trim();
    const pricingValue = document.getElementById('pricing-value').value; // <-- get the hidden pricing value

    // Only validate quoteAmount if it's not empty (allow empty if pricingValue exists)
    if (quoteAmount && isNaN(quoteAmount)) {
        showToast('Error', 'Please enter a valid quote amount', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('price', quoteAmount); // can be empty if not entered
    formData.append('subtotal', subtotalAmount);
    formData.append('user_id', userId);
    formData.append('ticket', ticket);
    formData.append('quantity', quantity);
    formData.append('pricing', pricingValue); // <-- always send this

    // Show loading state
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    fetch('functions/approved_pricing.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            quoteModal.style.display = 'none';
            refreshDesignersTable(); // Refresh table after successful save
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred while updating pricing', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    });
}

// Modal close handlers
function closeModal() {
    quoteModal.style.display = 'none';
}

function handleWindowClick(event) {
    if (event.target === quoteModal) {
        closeModal();
    }
}

// Table refresh functionality
function refreshDesignersTable() {
    fetch('functions/get_admin_orders.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('admins-table-body').innerHTML = data;
            attachEventListeners(); // Reattach event listeners after refresh
        })
        .catch(error => console.error('Error refreshing table:', error));
}

// Attach all event listeners
function attachEventListeners() {
    // Price calculation
    priceInput.addEventListener('input', handlePriceCalculation);
    
    // View buttons
    document.querySelectorAll('.view-quote-modal').forEach(button => {
        button.addEventListener('click', handleViewButtonClick);
    });
    
    // Modal close
    quoteModalClose.addEventListener('click', closeModal);
    window.addEventListener('click', handleWindowClick);
    
    // Save button
    saveBtn.addEventListener('click', handleSaveQuote);
}

// Initialize
function init() {
    attachEventListeners();
    refreshDesignersTable();
    
    // Set up periodic refresh (every 5 seconds)
    setInterval(refreshDesignersTable, 5000);
}

// Start the application
document.addEventListener('DOMContentLoaded', init);

// Your existing toast function exactly as provided
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
</script>

<script>
    // Image Viewer Modal
    const imageViewerModal = document.createElement('div');
imageViewerModal.className = 'image-viewer-modal';
imageViewerModal.innerHTML = `
  <span class="close-viewer">&times;</span>
  <img class="image-viewer-content" id="viewed-image">
`;
document.body.appendChild(imageViewerModal);

// View button functionality
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('view-design-btn')) {
    const imgSrc = e.target.closest('.design-image-container').querySelector('img').src;
    document.getElementById('viewed-image').src = imgSrc;
    imageViewerModal.style.display = 'block';
  }
});

// Close button functionality
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('close-viewer')) {
    imageViewerModal.style.display = 'none';
  }
});

// Close viewer when clicking outside the image
imageViewerModal.addEventListener('click', function (e) {
  if (e.target === imageViewerModal) {
    imageViewerModal.style.display = 'none';
  }
});

// Download button functionality
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('download-design-btn')) {
    const container = e.target.closest('.design-image-container');
    const imgSrc = container.querySelector('img').src;
    const ticket = document.getElementById('quote-modal-ticket').textContent;
    const printType = document.getElementById('quote-modal-print-type').textContent;
    
    // Extract filename from URL
    const filename = imgSrc.split('/').pop();
    const extension = filename.split('.').pop();
    
    // Create download link
    const link = document.createElement('a');
    link.href = imgSrc;
    link.download = `${ticket}-${printType.toLowerCase().replace(/ /g, '-')}.${extension}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
});

// Close image viewer
document.querySelector('.close-viewer').onclick = function() {
  imageViewerModal.style.display = 'none';
}

// Close when clicking outside image
imageViewerModal.onclick = function(e) {
  if (e.target === imageViewerModal) {
    imageViewerModal.style.display = 'none';
  }
}
</script>

<script src="assets/js/orders.js"></script>



</body>
</html>