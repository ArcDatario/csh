<?php 
require '../auth_check.php';
redirectIfNotLoggedIn();

require '../db_connection.php';

// Fetch user ID from session
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Query the database for the user's address only
    $stmt = $conn->prepare("SELECT address FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $address = $user['address'] ?? '';
    } else {
        // If no user is found, set default empty value
        $address = '';
    }
} else {
    // If no user ID is found in the session, redirect to login
    header("Location: ../login");
    exit();
}

// Use address directly
$full_address = trim($address);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Enterprises | Cancelled Orders</title>
    <link rel="icon" href="../assets/images/icons/shirt.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/quote.css">
    <link rel="stylesheet" href="../assets/css/order-process-modal.css">
    <link rel="stylesheet" href="../assets/css/profile-modal.css">

</head>
<body>
    <!-- Loader -->
    <div class="loader-container" id="loader" style="margin-right:10% !important;">
        <div class="loader" style="margin-right:10% !important;">
            <i class="fas fa-tshirt t-shirt"></i>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="#" class="logo">
                 <img src="../csh-logo.png" alt="" style="height: 55px; width: 100%;">
            </a>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
             <?php include "includes/navbar.php";?>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
    <div class="orders-tabs">
    <a href="quote" class="tab-button " >Pending</a>
    <a href="approved-order" class="tab-button  " >Approved</a>
    <a href="to-pickup-order" class="tab-button" >To Pick Up</a>
    <a href="processing-order" class="tab-button ">Processing</a>
    <a href="to-ship-order" class="tab-button ">To Ship</a>
    <a href="completed-order" class="tab-button ">Completed</a>
    <a href="cancelled-orders" class="tab-button active">Cancelled</a>
</div>

<!-- Desktop Sidebar (hidden on mobile) -->
<div class="orders-sidebar">
    <a href="quote" class="sidebar-item">
        <i class="fas fa-clock"></i> Pending
    </a>
    <a href="approved-order" class="sidebar-item">
        <i class="fas fa-check-circle"></i> Approved
    </a>
    <a href="to-pickup-order" class="sidebar-item">
        <i class="fas fa-box"></i> To Pick Up
    </a>
    <a href="processing-order" class="sidebar-item">
        <i class="fas fa-cog"></i> Processing
    </a>
    <a href="to-ship-order" class="sidebar-item">
        <i class="fas fa-shipping-fast"></i> To Ship
    </a>
    <a href="completed-order" class="sidebar-item">
        <i class="fas fa-flag-checkered"></i> Completed
    </a>
    <a href="cancelled-orders" class="sidebar-item active">
        <i class="fas fa-times-circle"></i> Cancelled
    </a>
</div>

        <div class="search-wrapper">
            <div class="search-container">
                <!-- Cancelled Orders Search -->
                <div class="cancelled-search" style="display: block;">
                    <input type="text"
                           id="CancelledSearchInput"
                           class="search-input"
                           placeholder="Search by Ticket #">
                    <span class="search-icon">&#128269;</span>
                    <button type="button" id="clearCancelledSearch" class="clear-search-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

<div class="quotes-container cancelled-orders-container" id="cancelled-orders-container" style="display:block;">
    <?php
    include '../db_connection.php';

    $user_id = $_SESSION['user_id'] ?? null;

    if ($user_id) {
        // Pagination setup
        $limit = 8; // orders per page
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset = ($page - 1) * $limit;

        // Count total cancelled orders
        $count_sql = "SELECT COUNT(*) AS total FROM orders WHERE user_id = ? AND status = 'cancelled'";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("i", $user_id);
        $count_stmt->execute();
        $total_orders = $count_stmt->get_result()->fetch_assoc()['total'];
        $total_pages = ceil($total_orders / $limit);
        $count_stmt->close();

        // Fetch paginated orders
        $sql = "SELECT orders.*, users.name, users.phone_number 
                FROM orders 
                INNER JOIN users ON orders.user_id = users.id 
                WHERE orders.user_id = ? AND orders.status = 'cancelled' 
                ORDER BY orders.cancelled_date DESC 
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo '<div class="no-orders">No cancelled orders found</div>';
        } else {
            while ($order = $result->fetch_assoc()) {
                // fetch items
                $items_sql = "SELECT shirt_color, quantity FROM items WHERE order_id = ?";
                $items_stmt = $conn->prepare($items_sql);
                $items_stmt->bind_param("i", $order['id']);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();

                $shirtItems = [];
                while ($item = $items_result->fetch_assoc()) {
                    $shirtItems[] = $item;
                }
                $items_stmt->close();

                $createdAt = date('M d, Y', strtotime($order['created_at']));
                $cancelledAt = date('M d, Y', strtotime($order['cancelled_date']));
                $cancellationReason = $order['cancellation_reason'] ?? '';
                $designFile = $order['design_file'];
                $ext = strtolower(pathinfo($designFile, PATHINFO_EXTENSION));
                $thumbnail = ($ext === 'psd') ? "../photoshop.png" : (($ext === 'pdf') ? "../pdf.png" : (($ext === 'ai') ? "../illustrator.png" : htmlspecialchars($designFile)));
                ?>
                <div class="quote-card animate__animated animate__fadeInUp" data-ticket="<?= htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') ?>">
                    <img src="<?= $thumbnail ?>" alt="Design" class="card-image">
                    <span class="card-status status-cancelled"><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="card-content">
                        <h3 class="card-title"><?= htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="card-details">
                            <div class="card-detail"><span class="detail-label">Quantity</span><span class="detail-value"><?= htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="card-detail"><span class="detail-label">Ticket #</span><span class="detail-value"><?= htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') ?></span></div>
                        </div>
                        <div class="card-details">
                            <div class="card-detail"><span class="detail-label">Order Date</span><span class="detail-value"><?= $createdAt ?></span></div>
                            <div class="card-detail"><span class="detail-label">Cancelled Date</span><span class="detail-value"><?= $cancelledAt ?></span></div>
                        </div>
                        <i class="fa fa-info-circle bottom-right-details-icon"
                            title="Click to see full order details"
                            onclick="openDetailsModalFromCard(this)"
                            data-id="<?= $order['id'] ?>"
                            data-user-id="<?= $order['user_id'] ?>"
                            data-ticket="<?= htmlspecialchars($order['ticket'], ENT_QUOTES) ?>"
                            data-design="<?= htmlspecialchars($order['design_file'], ENT_QUOTES) ?>"
                            data-mobile="<?= htmlspecialchars($order['phone_number'], ENT_QUOTES) ?>"
                            data-name="<?= htmlspecialchars($order['name'], ENT_QUOTES) ?>"
                            data-print-type="<?= htmlspecialchars($order['print_type'], ENT_QUOTES) ?>"
                            data-quantity="<?= htmlspecialchars($order['quantity'], ENT_QUOTES) ?>"
                            data-date="<?= htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES) ?>"
                            data-status="<?= htmlspecialchars($order['status'], ENT_QUOTES) ?>"
                            data-note="<?= htmlspecialchars($order['note'], ENT_QUOTES) ?>"
                            data-address="<?= htmlspecialchars($order['address'], ENT_QUOTES) ?>"
                            data-pricing="<?= htmlspecialchars($order['pricing'], ENT_QUOTES) ?>"
                            data-subtotal="<?= htmlspecialchars($order['subtotal'], ENT_QUOTES) ?>"
                            data-cancellation-reason="<?= htmlspecialchars($cancellationReason, ENT_QUOTES) ?>"
                            data-items='<?= json_encode($shirtItems, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                        </i>
                        <div class="card-actions">
                            <div class="button-group">
                                <a href="<?= htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8') ?>" 
                                class="download-btn" 
                                download 
                                title="Download design file">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }
        }
        $stmt->close();

        // Pagination Controls
        if ($total_pages > 0) {
            echo '<div class="pagination">';
            
            // Previous button
            if ($page > 1) {
                echo '<a href="?page='.($page-1).'" class="page-btn prev-next">‹ Prev</a>';
            } else {
                echo '<span class="page-btn prev-next disabled">‹ Prev</span>';
            }
            
            // First page
            if ($page > 3) {
                echo '<a href="?page=1" class="page-btn">1</a>';
                if ($page > 4) {
                    echo '<span class="page-dots">...</span>';
                }
            }
            
            // Page numbers around current page
            $startPage = max(2, $page - 1);
            $endPage = min($total_pages - 1, $page + 1);
            
            for ($i = $startPage; $i <= $endPage; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<a href="?page='.$i.'" class="page-btn '.$active.'">'.$i.'</a>';
            }
            
            // Last page
            if ($page < $total_pages - 2) {
                if ($page < $total_pages - 3) {
                    echo '<span class="page-dots">...</span>';
                }
                echo '<a href="?page='.$total_pages.'" class="page-btn">'.$total_pages.'</a>';
            }
            
            // Next button
            if ($page < $total_pages) {
                echo '<a href="?page='.($page+1).'" class="page-btn prev-next">Next ›</a>';
            } else {
                echo '<span class="page-btn prev-next disabled">Next ›</span>';
            }
            
            echo '</div>';
        }
    } else {
        echo '<div class="no-orders">No user ID found. Please log in.</div>';
    }
    ?>
</div>
<!-- Footer inside main content -->
<?php include 'footer.php'; ?>
    </main>

    <!-- ✅ Details Modal -->
    <div id="detailsModal" class="details-modal">
      <div class="detail-modal-content">
        <span class="detail-modal-close">&times;</span>

        <!-- Header: Ticket & Date -->
        <div class="detail-modal-header">
  <h2>Ticket #<span id="detail-modal-ticket"></span></h2>
  <span id="detail-modal-date" class="detail-modal-value"></span>
  <span class="status-label status-cancelled-label" style="background:#c7241c;">Status: <span id="detail-modal-status"></span></span>
  <div id="detail-modal-cancellation-reason" style="margin-top:8px;color:#c7241c;font-weight:bold;"></div>
</div>

        <hr>

        <!-- Design & Print Info -->
        <div class="detail-modal-section design-info">
          <div class="design-image-container">
            <img id="detail-modal-design" src="" alt="Design" class="design-image">
            <div class="design-buttons">
              <button class="view-design-btn">View</button>
              <button class="download-design-btn">Download</button>
            </div>
          </div>
          <div class="design-details">
            <div><strong>Print Type:</strong> <span id="detail-modal-print-type"></span></div>
            <div><strong>Total Quantity:</strong> <span id="detail-modal-quantity"></span></div>
          </div>
        </div>

        <hr>

        <!-- Items Table -->
        <div class="detail-modal-section items-section">
          <table class="items-table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Qty</th>
              </tr>
            </thead>
            <tbody id="detail-modal-shirt-items">
              <!-- Shirt items will populate here -->
            </tbody>
          </table>
        </div>

        <hr>

        <!-- Price and subtotal section removed as requested -->

        <!-- Hidden fields for JS -->
  <!-- Hidden price/subtotal fields removed -->
        <input type="hidden" id="user_id" name="user_id">
        <input type="hidden" id="ticket-value-input" name="ticket-value-input">
      </div>
    </div>

    <!-- Image Viewer Modal -->
    <div id="userImageViewerModal" class="image-viewer-modal" style="display:none;">
      <span class="close-viewer">&times;</span>
      <img class="image-viewer-content" id="userExpandedDesignImage" alt="Design Preview">
      <div id="viewerLoading" class="viewer-loading" style="display:none;">Loading...</div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/quote.js"></script>

    <script>
    function openDetailsModalFromCard(element) {
        const button = element.tagName === "I" ? element : element.querySelector('i.details-icon');
        
        if (button) {
            openDetailsModal({ currentTarget: button });
        }
    }

    // Grab the modal
    const detailsModal = document.getElementById('detailsModal');

    // Open Details Modal
// Open Details Modal
function openDetailsModal(event) {
    const button = event.currentTarget;
    const id = button.getAttribute("data-id");
    const userId = button.getAttribute("data-user-id");
    const ticket = button.getAttribute("data-ticket");
    const design = button.getAttribute("data-design");
    const printType = button.getAttribute("data-print-type");
    const quantity = button.getAttribute("data-quantity");
    const date = button.getAttribute("data-date");
    const status = button.getAttribute("data-status");
  const pricing = button.getAttribute("data-pricing");
  const subtotal = button.getAttribute("data-subtotal");
  const cancellationReason = button.getAttribute("data-cancellation-reason") || '';
  const items = JSON.parse(button.getAttribute("data-items") || "[]");

    // Determine correct design image
    const fileExtension = design.split('.').pop().toLowerCase();
    let imageSrc = ['psd','pdf','ai'].includes(fileExtension)
                    ? fileExtension === 'psd' ? '../photoshop.png'
                      : fileExtension === 'pdf' ? '../pdf.png'
                      : '../illustrator.png'
                    : '../user/' + design;

    // Populate modal fields
    // Populate modal fields
document.getElementById("detail-modal-ticket").textContent = ticket;
document.getElementById("detail-modal-design").src = imageSrc;
document.getElementById("detail-modal-print-type").textContent = printType;
document.getElementById("detail-modal-quantity").textContent = quantity;

// FIXED: Proper date formatting
const orderDate = new Date(date);
const formattedDate = isNaN(orderDate.getTime()) ? date : orderDate.toLocaleDateString('en-US', { 
  year: 'numeric', 
  month: 'short', 
  day: 'numeric' 
});
document.getElementById("detail-modal-date").textContent = formattedDate;

document.getElementById("detail-modal-status").textContent = status;
document.getElementById("detail-modal-cancellation-reason").textContent = cancellationReason ? `Reason: ${cancellationReason}` : '';
document.getElementById("user_id").value = userId;
document.getElementById("ticket-value-input").value = ticket;

    // Populate shirt items (for table tbody)
    const itemsContainer = document.getElementById("detail-modal-shirt-items");
    itemsContainer.innerHTML = "";
    if (items.length > 0) {
        items.forEach(item => {
            const tr = document.createElement("tr");
            tr.innerHTML = `<td>${item.shirt_color}</td><td>${item.quantity}</td>`;
            itemsContainer.appendChild(tr);
        });
    } else {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td colspan="2"><em>No shirt colors added</em></td>`;
        itemsContainer.appendChild(tr);
    }

    // Show the modal
    detailsModal.style.display = "flex";
    detailsModal.setAttribute('data-design-file', design);
    detailsModal.setAttribute(
        'data-is-viewable',
        ['jpg','jpeg','png','gif','webp'].includes(fileExtension)
    );
}

    // Close Details Modal
    function closeDetailsModal() {
        detailsModal.style.display = "none";
    }

    // Attach open/close events
    document.querySelectorAll(".details-btn").forEach(btn => {
        btn.addEventListener("click", openDetailsModal);
    });
    detailsModal.querySelector(".detail-modal-close").addEventListener("click", closeDetailsModal);
    detailsModal.addEventListener("click", e => {
        if (e.target === detailsModal) closeDetailsModal();
    });


    // Cancelled Orders Search Functionality (AJAX-based)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('CancelledSearchInput');
    const clearSearchBtn = document.getElementById('clearCancelledSearch');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            if (clearSearchBtn) {
                clearSearchBtn.style.display = searchTerm ? 'block' : 'none';
            }
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Set new timeout to avoid too many requests
            searchTimeout = setTimeout(() => {
                searchCancelledOrders(searchTerm, 1);
            }, 500); // 500ms delay
        });
    }
    
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            searchCancelledOrders('', 1);
        });
    }
});

function searchCancelledOrders(searchTerm = '', page = 1) {
    const container = document.getElementById('cancelled-orders-container');
    
    if (!container) {
        console.error('Cancelled orders container not found');
        return;
    }
    
    // Show loading state
    container.innerHTML = '<div class="no-orders">Searching...</div>';
    
    // Build URL with parameters
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    params.append('page', page);
    
    const searchUrl = 'functions/search_cancelled_orders.php?' + params.toString();
    
    console.log('Searching cancelled orders with URL:', searchUrl);
    
    fetch(searchUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Cancelled orders search response:', data);
            
            if (data.error) {
                container.innerHTML = `<div class="no-orders">Error: ${data.error}</div>`;
                return;
            }
            
            if (data.success === false) {
                container.innerHTML = `<div class="no-orders">${data.error || 'Search failed'}</div>`;
                return;
            }
            
            displayCancelledSearchResults(data, searchTerm, page);
        })
        .catch(error => {
            console.error('Cancelled orders search error:', error);
            container.innerHTML = '<div class="no-orders">Search failed: ' + error.message + '</div>';
        });
}

function displayCancelledSearchResults(data, searchTerm, currentPage) {
    const container = document.getElementById('cancelled-orders-container');
    const orders = data.orders || [];
    const totalPages = data.total_pages || 1;
    
    if (orders.length === 0) {
        const message = searchTerm 
            ? `No cancelled orders found for ticket "${searchTerm}"`
            : 'No cancelled orders found';
        container.innerHTML = `<div class="no-orders">${message}</div>`;
        return;
    }
    
    let html = '';
    
    orders.forEach(order => {
        // Use the thumbnail path from the server response
        const thumbnail = order.thumbnail || order.design_file;
        const statusClass = 'status-cancelled';
        const statusText = 'Cancelled';
        const cancellationReason = order.cancellation_reason || '';
        
        html += `
            <div class="quote-card animate__animated animate__fadeInUp" data-ticket="${escapeHtml(order.ticket)}">
                <img src="${thumbnail}" alt="Design" class="card-image" onerror="this.src='../image-placeholder.png'">
                <span class="card-status ${statusClass}">${statusText}</span>
                <div class="card-content">
                    <h3 class="card-title">${escapeHtml(order.print_type)}</h3>
                    <div class="card-details">
                        <div class="card-detail"><span class="detail-label">Quantity</span><span class="detail-value">${escapeHtml(order.quantity)}</span></div>
                        <div class="card-detail"><span class="detail-label">Ticket #</span><span class="detail-value">${escapeHtml(order.ticket)}</span></div>
                    </div>
                    <div class="card-details">
                        <div class="card-detail"><span class="detail-label">Order Date</span><span class="detail-value">${order.created_at_formatted}</span></div>
                        <div class="card-detail"><span class="detail-label">Cancelled Date</span><span class="detail-value">${order.cancelled_date_formatted}</span></div>
                    </div>
                    <i class="fa fa-info-circle bottom-right-details-icon"
                        title="Click to see full order details"
                        onclick="openDetailsModalFromCard(this)"
                        data-id="${order.id}"
                        data-user-id="${order.user_id}"
                        data-ticket="${escapeHtml(order.ticket)}"
                        data-design="${escapeHtml(order.design_file)}"
                        data-mobile="${escapeHtml(order.phone_number)}"
                        data-name="${escapeHtml(order.name)}"
                        data-print-type="${escapeHtml(order.print_type)}"
                        data-quantity="${escapeHtml(order.quantity)}"
                        data-date="${escapeHtml(order.created_at_formatted)}"
                        data-status="${escapeHtml(order.status)}"
                        data-note="${escapeHtml(order.note || '')}"
                        data-address="${escapeHtml(order.address)}"
                        data-pricing="${escapeHtml(order.pricing)}"
                        data-subtotal="${escapeHtml(order.subtotal)}"
                        data-cancellation-reason="${escapeHtml(cancellationReason)}"
                        data-items='${JSON.stringify(order.items).replace(/'/g, "&#39;")}'>
                    </i>
                    <div class="card-actions">
                        <div class="button-group">
                            <a href="${escapeHtml(order.design_file)}" class="download-btn" download title="Download design file">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    if (totalPages > 0) {
        html += '<div class="pagination" id="cancelled-search-pagination">';
        
        // Previous button
        if (currentPage > 1) {
            html += `<a href="javascript:void(0)" class="page-btn prev-next" onclick="searchCancelledOrders('${escapeHtml(searchTerm)}', ${currentPage - 1})">‹ Prev</a>`;
        } else {
            html += `<span class="page-btn prev-next disabled">‹ Prev</span>`;
        }
        
        // First page
        if (currentPage > 3) {
            html += `<a href="javascript:void(0)" class="page-btn" onclick="searchCancelledOrders('${escapeHtml(searchTerm)}', 1)">1</a>`;
            if (currentPage > 4) {
                html += `<span class="page-dots">...</span>`;
            }
        }
        
        // Page numbers around current page
        const startPage = Math.max(2, currentPage - 1);
        const endPage = Math.min(totalPages - 1, currentPage + 1);
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                html += `<span class="page-btn active">${i}</span>`;
            } else {
                html += `<a href="javascript:void(0)" class="page-btn" onclick="searchCancelledOrders('${escapeHtml(searchTerm)}', ${i})">${i}</a>`;
            }
        }
        
        // Last page
        if (currentPage < totalPages - 2) {
            if (currentPage < totalPages - 3) {
                html += `<span class="page-dots">...</span>`;
            }
            html += `<a href="javascript:void(0)" class="page-btn" onclick="searchCancelledOrders('${escapeHtml(searchTerm)}', ${totalPages})">${totalPages}</a>`;
        }
        
        // Next button
        if (currentPage < totalPages) {
            html += `<a href="javascript:void(0)" class="page-btn prev-next" onclick="searchCancelledOrders('${escapeHtml(searchTerm)}', ${currentPage + 1})">Next ›</a>`;
        } else {
            html += `<span class="page-btn prev-next disabled">Next ›</span>`;
        }
        
        html += '</div>';
    }
    
    container.innerHTML = html;
}

// Clear cancelled search functionality
function clearCancelledSearch() {
    const searchInput = document.getElementById('CancelledSearchInput');
    const clearSearchBtn = document.getElementById('clearCancelledSearch');
    
    if (searchInput) {
        searchInput.value = '';
        if (clearSearchBtn) {
            clearSearchBtn.style.display = 'none';
        }
        // Reload the original page content
        window.location.href = 'cancelled-orders?page=1';
    }
}

// Helper function to escape HTML (reuse from your existing code)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

    // Image viewer functionality
    const userImageViewerModal = document.getElementById('userImageViewerModal');
    const userExpandedImage = document.getElementById('userExpandedDesignImage');

    // View button
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('view-design-btn')) {
        const designFile = detailsModal.getAttribute('data-design-file');
        const isViewable = detailsModal.getAttribute('data-is-viewable') === 'true';

        if (isViewable && designFile) {
          userExpandedImage.src = '../user/' + designFile;
          userImageViewerModal.style.display = 'flex';
        } else {
          alert('This file cannot be previewed. Please download the file.');
        }
      }
    });

    // Download button
    document.addEventListener('click', function(e) {
      if (e.target.classList.contains('download-design-btn')) {
        const designFile = detailsModal.getAttribute('data-design-file');
        if (!designFile) return;

        const ticket = document.getElementById('detail-modal-ticket').textContent;
        const printType = document.getElementById('detail-modal-print-type').textContent;
        const filePath = '../user/' + designFile;
        const filename = designFile.split('/').pop();
        const extension = filename.split('.').pop();

        const link = document.createElement('a');
        link.href = filePath;
        link.download = `${ticket}-${printType.toLowerCase().replace(/ /g,'-')}.${extension}`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    });

    // Close viewer
    document.querySelector('#userImageViewerModal .close-viewer').onclick = function() {
      userImageViewerModal.style.display = 'none';
    }

    // Close when clicking outside image
    userImageViewerModal.onclick = function(e) {
      if (e.target === userImageViewerModal) {
        userImageViewerModal.style.display = 'none';
      }
    }

    // Add CSS for cancelled status
    const style = document.createElement('style');
    style.textContent = `
        .status-cancelled {
            background-color: #f44336;
            color: white;
        }
    `;
    document.head.appendChild(style);
    </script>
</body>
</html>