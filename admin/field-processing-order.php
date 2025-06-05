<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Safely check for Field Manager role and redirect
if (isset($_SESSION['admin_role'])) {
    $current_page = basename($_SERVER['PHP_SELF']);
    
     if ($_SESSION['admin_role'] === "Field Manager" && 
        !in_array($current_page, ['inventory.php', 'field-processing-order.php'])) {
        header('Location: field-processing-order.php');
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
    <?php include "includes/link-css.php";?>
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
                <div class="theme-toggle" id="themeToggle" style="display:none;">
                <span style="margin-right:8px;" style="display:none;">Dark Mode</span>
                <i class="fas fa-moon"></i>
            </div>
                    
          <?php include "includes/notification.php";?>

    </div>
                    
                   <?php include "includes/profile.php";?>
                </div>
            </header>
            
            <!-- Table -->
            <section class="table-card fade-in">
                <div class="table-header">
        <h3 class="table-title">Orders for Processing</h3>
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
    require_once '../db_connection.php';

    // Fetch only orders with status 'processing'
    $sql = "SELECT orders.*, users.name 
            FROM orders 
            INNER JOIN users ON orders.user_id = users.id 
            WHERE orders.status = 'processing' AND orders.is_for_processing ='no'
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
                                data-ticket="<?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-design="<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-print-type="<?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-quantity="<?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?>">
                            Process
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No orders currently for processing</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
                </div>
            </section>
        </main>
</div>
  
<div id="processingModal" class="quote-modal">
  <div class="quote-modal-content">
    <span class="quote-modal-close">&times;</span>
    <h2>Order Processing</h2>
    <div class="quote-modal-body">
      <!-- Ticket -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Ticket #:</span>
        <span id="processing-modal-ticket" class="quote-modal-value"></span>
      </div>
      
      <!-- Design with buttons -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Design:</span>
        <div class="design-image-container">
          <img id="processing-modal-design" src="" alt="Design" class="design-image">
          <div class="design-buttons">
            <button class="view-design-btn">View</button>
            <button class="download-design-btn">Download</button>
          </div>
        </div>
      </div>
      
      <!-- Print Type -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Print Type:</span>
        <span id="processing-modal-print-type" class="quote-modal-value"></span>
      </div>
      
      <!-- Quantity -->
      <div class="quote-modal-row">
        <span class="quote-modal-label">Quantity:</span>
        <span id="processing-modal-quantity" class="quote-modal-value"></span>
      </div>
      
      <!-- Hidden fields -->
      <input type="hidden" id="processing-modal-id" name="id">
      <input type="hidden" id="processing-modal-ticket-input" name="ticket">
    </div>
    <div class="quote-modal-footer">
      <button id="processing-modal-confirm" class="quote-modal-btn btn-process">Mark as Processing</button>
      <button id="processing-modal-close" class="quote-modal-btn btn-close">Close</button>
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

<script>
// Get DOM elements
const processingModal = document.getElementById('processingModal');
const processingModalClose = document.querySelector('.quote-modal-close');
const confirmBtn = document.getElementById('processing-modal-confirm');
const closeBtn = document.getElementById('processing-modal-close');

// Handle view button click
function handleViewButtonClick() {
    const id = this.getAttribute('data-id');
    const ticket = this.getAttribute('data-ticket');
    const design = this.getAttribute('data-design');
    const printType = this.getAttribute('data-print-type');
    const quantity = this.getAttribute('data-quantity');

    // Store data in modal
    processingModal.setAttribute('data-current-id', id);

    // Populate modal fields
    document.getElementById('processing-modal-ticket').textContent = ticket;
    document.getElementById('processing-modal-design').src = '../user/' + design;
    document.getElementById('processing-modal-print-type').textContent = printType;
    document.getElementById('processing-modal-quantity').textContent = quantity;
    document.getElementById('processing-modal-id').value = id;
    document.getElementById('processing-modal-ticket-input').value = ticket;

    // Show modal
    processingModal.style.display = 'block';
}

// Handle confirm processing
function handleConfirmProcessing() {
    const id = document.getElementById('processing-modal-id').value;
    const ticket = document.getElementById('processing-modal-ticket-input').value;

    // Show loading state
    const originalText = confirmBtn.textContent;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing...';

    fetch('functions/update_processing.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&ticket=${ticket}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            processingModal.style.display = 'none';
            refreshOrdersTable(); // Refresh table after successful update
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'An error occurred while updating order', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        confirmBtn.disabled = false;
        confirmBtn.textContent = originalText;
    });
}

// Modal close handlers
function closeModal() {
    processingModal.style.display = 'none';
}

function handleWindowClick(event) {
    if (event.target === processingModal) {
        closeModal();
    }
}

// Table refresh functionality
function refreshOrdersTable() {
    fetch('functions/get_processing_orders.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('admins-table-body').innerHTML = data;
            attachEventListeners(); // Reattach event listeners after refresh
        })
        .catch(error => console.error('Error refreshing table:', error));
}

// Image Viewer functionality
function setupImageViewer() {
    const imageViewerModal = document.getElementById('imageViewerModal');
    const closeViewer = document.querySelector('.close-viewer');

    // View button functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-design-btn')) {
            const imgSrc = e.target.closest('.design-image-container').querySelector('img').src;
            document.getElementById('expandedDesignImage').src = imgSrc;
            imageViewerModal.style.display = 'block';
        }
    });

    // Close button functionality
    closeViewer.addEventListener('click', function() {
        imageViewerModal.style.display = 'none';
    });

    // Close when clicking outside image
    imageViewerModal.addEventListener('click', function(e) {
        if (e.target === imageViewerModal) {
            imageViewerModal.style.display = 'none';
        }
    });
}

// Download functionality
function setupDownloadButtons() {
    // Remove any previous event listener to avoid multiple downloads
    const downloadBtn = document.querySelector('.download-design-btn');
    if (downloadBtn) {
        // Clone the button to remove previous listeners
        const newBtn = downloadBtn.cloneNode(true);
        downloadBtn.parentNode.replaceChild(newBtn, downloadBtn);

        newBtn.addEventListener('click', function() {
            const container = newBtn.closest('.design-image-container');
            const imgSrc = container.querySelector('img').src;
            const ticket = document.getElementById('processing-modal-ticket').textContent;
            const printType = document.getElementById('processing-modal-print-type').textContent;

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
        });
    }
}

// Attach all event listeners
function attachEventListeners() {
    // View buttons
    document.querySelectorAll('.view-quote-modal').forEach(button => {
        button.addEventListener('click', handleViewButtonClick);
    });
    
    // Modal buttons
    confirmBtn.addEventListener('click', handleConfirmProcessing);
    closeBtn.addEventListener('click', closeModal);
    processingModalClose.addEventListener('click', closeModal);
    window.addEventListener('click', handleWindowClick);
    
    // Image viewer and download
    setupImageViewer();
    setupDownloadButtons();
}

// Initialize
function init() {
    attachEventListeners();
    refreshOrdersTable();
    
    // Set up periodic refresh (every 5 seconds)
    setInterval(refreshOrdersTable, 5000);
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

// Start the application
document.addEventListener('DOMContentLoaded', init);
</script>

<?php include "includes/script-src.php";?>
</body>
</html>