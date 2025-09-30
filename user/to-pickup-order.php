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
    <title>CSH Enterprises | To Pick Up Orders</title>
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
    <a href="quote" class="tab-button" >Pending</a>
    <a href="approved-order" class="tab-button" >Approved</a>
    <a href="to-pickup-order" class="tab-button active" >To Pick Up</a>
    <a href="processing-order" class="tab-button">Processing</a>
    <a href="to-ship-order" class="tab-button">To Ship</a>
    <a href="completed-order" class="tab-button">Completed</a>
    <a href="cancelled-orders" class="tab-button">Cancelled</a>
</div>

<!-- Desktop Sidebar (hidden on mobile) -->
<div class="orders-sidebar">
    <a href="quote" class="sidebar-item">
        <i class="fas fa-clock"></i> Pending
    </a>
    <a href="approved-order" class="sidebar-item">
        <i class="fas fa-check-circle"></i> Approved
    </a>
    <a href="to-pickup-order" class="sidebar-item active">
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
    <a href="cancelled-orders" class="sidebar-item">
        <i class="fas fa-times-circle"></i> Cancelled
    </a>
</div>

 

        <div class="search-wrapper">
            <div class="search-container">
                <!-- Print Type Filter -->
                <div class="print-type-filter">
                    <select id="pickupPrintTypeFilter" class="form-control">
                        <option value="">All Print Types</option>
                        <option value="Direct to Film Print">Direct to Film Print</option>
                        <option value="Screen Printing">Screen Printing</option>
                        <option value="Emboss Print">Emboss Print</option>
                        <option value="Hi-Density Print">Hi-Density Print</option>
                        <option value="Glitters Print">Glitters Print</option>
                        <option value="Silk Screen Print">Silk Screen Print</option>
                    </select>
                </div>
                <!-- Search Input -->
                <div class="pickup-search">
                    <input type="text"
                           id="ToPickupSearchInput"
                           class="search-input"
                           placeholder="Search by Ticket #">
                    <button type="button" id="clearToPickupSearch" class="clear-search-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="quotes-container pickup-orders-container" id="pickup-orders-container" style="display:block;"></div>
        <!-- Footer inside main content -->
<?php include 'footer.php'; ?>
    </main>

    <div id="toPickUpProcessModal" class="order-process-modal">
        <div class="order-process-modal-content">
            <span class="order-process-close-btn" onclick="closeToPickUpProcessModal()">&times;</span>
            <h2 id="toPickUpProcessTitle" class="order-process-title">Ticket #12345 Process Details</h2>
            
            <div id="toPickUpProcessSteps" class="order-process-steps-container">
                <!-- Quote Placed Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">1</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="toPickUpQuotePlacedTitle" class="order-step-title">Quote Placed</div>
                        <div id="toPickUpQuotePlacedDesc" class="order-step-description">Your order request has been received</div>
                        <div id="toPickUpQuotePlacedDate" class="order-step-date">Jan 15, 2023</div>
                    </div>
                </div>
                
                <!-- Agreed Price Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">2</div>
                    <div class="order-step-connector-completed"></div>
                    <div class="order-step-content">
                        <div id="toPickUpAdminApprovedTitle" class="order-step-title">Agreed Price</div>
                        <div class="order-step-description">
                            <div id="toPickUpOrderSummary" class="order-summary-details">
                                <p id="toPickUpUnitPrice">Unit Price: $10.00</p>
                                <p id="toPickUpQuantity">Quantity: 5</p>
                                <p id="toPickUpSubtotal" class="order-subtotal">Subtotal: $50.00</p>
                                
                            </div>
                        </div>
                        <div id="toPickUpAdminApprovedDate" class="order-step-date">Jan 16, 2023</div>
                    </div>
                </div>
                
                <!-- To Pick Up Step -->
                <div class="order-step order-step-completed">
                    <div class="order-step-number">3</div>
                    <div class="order-step-connector-current"></div>
                    <div class="order-step-content">
                        <div id="toPickUpProcessingTitle" class="order-step-title">To Pick Up</div>
                        <div id="toPickUpProcessingDesc" class="order-step-description">An email will be sent to you once your items will be picked up by our logistics</div>
                        <p id="toPickUpPickupAttempt" style="display:none;">Pickup Attempt: <span id="toPickUpPickupAttemptValue"></span></p>
                        <div id="toPickUpProcessingDate" class="order-step-date">Pending</div>
                    </div>
                </div>
                
                <!-- Processing Step -->
                <div class="order-step">
                    <div class="order-step-number">4</div>
                    <div class="order-step-connector-pending"></div>
                    <div class="order-step-content">
                        <div id="toPickUpReadyTitle" class="order-step-title">Processing</div>
                        <div id="toPickUpReadyDesc" class="order-step-description">Items are being prepared for pickup</div>
                        <div id="toPickUpReadyDate" class="order-step-date">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- ✅ Details Modal -->
<div id="detailsModal" class="details-modal">
  <div class="detail-modal-content">
    <span class="detail-modal-close">&times;</span>

    <!-- Header: Ticket & Date -->
    <div class="detail-modal-header">
      <h2>Ticket #<span id="detail-modal-ticket"></span></h2>
      <span id="detail-modal-date" class="detail-modal-value"></span>
      <span class="status-label">Status: <span id="detail-modal-status"></span></span>
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

    <!-- Price & Subtotal Section -->
    <div class="detail-modal-section price-section">
      <div class="price-detail">
        <span class="detail-modal-label">Price per pcs:</span>
        <span id="detail-modal-price" class="detail-modal-price-value"></span>
      </div>
      <div class="price-detail">
        <span class="detail-modal-label">Subtotal:</span>
        <span id="detail-modal-subtotal" class="detail-modal-subtotal-value"></span>
      </div>
    </div>

    <!-- Hidden fields for JS (kept as is) -->
    <input type="hidden" id="subtotal-value" name="subtotal">
    <input type="hidden" id="pricing-value" name="pricing">
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
    // If the element itself is the icon, use it
    const button = element.tagName === "I" ? element : element.querySelector('i.details-icon');
    
    if (button) {
        openDetailsModal({ currentTarget: button });
    }
}
      // Grab the modal
// Grab the modal
const detailsModal = document.getElementById('detailsModal');

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
    const items = JSON.parse(button.getAttribute("data-items") || "[]");

    // Determine correct design image
    const fileExtension = design.split('.').pop().toLowerCase();
    let imageSrc = ['psd','pdf','ai'].includes(fileExtension)
                    ? fileExtension === 'psd' ? '../photoshop.png'
                      : fileExtension === 'pdf' ? '../pdf.png'
                      : '../illustrator.png'
                    : '../user/' + design;

    // Populate modal fields
    document.getElementById("detail-modal-ticket").textContent = ticket;
    document.getElementById("detail-modal-design").src = imageSrc;
    document.getElementById("detail-modal-print-type").textContent = printType;
    document.getElementById("detail-modal-quantity").textContent = quantity;
    document.getElementById("detail-modal-date").textContent = date;
    document.getElementById("detail-modal-status").textContent = status;
    document.getElementById("user_id").value = userId;
    document.getElementById("ticket-value-input").value = ticket;

    document.getElementById("detail-modal-price").textContent = `₱${parseFloat(pricing).toFixed(2)}`;
    document.getElementById("detail-modal-subtotal").textContent = `₱${parseFloat(subtotal).toFixed(2)}`;

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



    function openToPickUpOrderModal(event) {
        const button = event.currentTarget;
        const ticket = button.getAttribute('data-approved-ticket');
        const createdAt = button.getAttribute('data-approved-created-at');
        const pricing = button.getAttribute('data-pricing');
        const quantity = button.getAttribute('data-quantity');
        const subtotal = button.getAttribute('data-subtotal');
        const adminApprovedDate = button.getAttribute('data-admin-approved-date');
        const userApprovedDate = button.getAttribute('data-user-approved-date');
        const readyDate = button.getAttribute('data-ready-date');
        const isForPickup = button.getAttribute('data-is-for-pickup');
        const pickupDate = button.getAttribute('data-pickup-date');
        const pickupAttempt = button.getAttribute('data-pickup-attempt');

        // Format the dates
        const date = new Date(createdAt);
        const formattedDate = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });

        // Format user approved date (Agreed Price date)
        let formattedUserApprovedDate = 'N/A';
        if (userApprovedDate && userApprovedDate !== 'null' && userApprovedDate !== '') {
            const userDate = new Date(userApprovedDate);
            if (!isNaN(userDate)) {
                formattedUserApprovedDate = userDate.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
            }
        }

        document.getElementById('toPickUpProcessTitle').textContent = `Ticket #${ticket} Process Details`;
        document.getElementById('toPickUpQuotePlacedDate').textContent = formattedDate;
        document.getElementById('toPickUpUnitPrice').textContent = `Unit Price: ₱${parseFloat(pricing).toFixed(2)}`;
        document.getElementById('toPickUpQuantity').textContent = `Quantity: ${quantity}`;
        document.getElementById('toPickUpSubtotal').textContent = `Subtotal: ₱${parseFloat(subtotal).toFixed(2)}`;
        document.getElementById('toPickUpAdminApprovedDate').textContent = formattedUserApprovedDate;

        // Show Pickup Attempt if isForPickup is 'yes'
        const pickupAttemptElem = document.getElementById('toPickUpPickupAttempt');
        const pickupAttemptValueElem = document.getElementById('toPickUpPickupAttemptValue');
        if (isForPickup && isForPickup.trim().toLowerCase() === 'yes') {
            pickupAttemptElem.style.display = '';
            pickupAttemptValueElem.textContent = pickupAttempt && pickupAttempt !== 'null' ? pickupAttempt : 'N/A';
        } else {
            pickupAttemptElem.style.display = 'none';
            pickupAttemptValueElem.textContent = '';
        }

        // All future steps show as Pending
        document.getElementById('toPickUpProcessingDate').textContent = 'Pending';
        document.getElementById('toPickUpReadyDesc').textContent = 'Items are being prepared for pickup';
        document.getElementById('toPickUpReadyDate').textContent = 'Pending';

        // --- TO PICK UP STEP LOGIC (Step 3) ---
        if (isForPickup && isForPickup.trim().toLowerCase() === 'yes') {
            document.getElementById('toPickUpProcessingDesc').textContent = 'Our Logistics will be pick up the items on your location';
            // Format pickup date if available
            let formattedPickupDate = 'Pending';
            if (pickupDate && pickupDate !== 'null' && pickupDate !== '') {
                const pickupDateObj = new Date(pickupDate);
                if (!isNaN(pickupDateObj.getTime())) {
                    formattedPickupDate = pickupDateObj.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                }
            }
            document.getElementById('toPickUpProcessingDate').textContent = formattedPickupDate;
        } else {
            document.getElementById('toPickUpProcessingDesc').textContent = 'An email will be sent to you once your items will be picked up by our logistics';
            document.getElementById('toPickUpProcessingDate').textContent = 'Pending';
        }

        document.getElementById('toPickUpProcessModal').setAttribute('data-ticket', ticket);
        document.getElementById('toPickUpProcessModal').style.display = 'flex';
    }

    function closeToPickUpProcessModal() {
        document.getElementById('toPickUpProcessModal').style.display = 'none';
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to view buttons
        const viewButtons = document.querySelectorAll('.to-pick-up-order-btn');
        viewButtons.forEach(button => {
            button.addEventListener('click', openToPickUpOrderModal);
        });

        // Close modal when clicking outside
        document.getElementById('toPickUpProcessModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeToPickUpProcessModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeToPickUpProcessModal();
            }
        });
    });

    // To Pick Up Orders Search Functionality (AJAX-based)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ToPickupSearchInput');
    const clearSearchBtn = document.getElementById('clearToPickupSearch');
    const printTypeFilter = document.getElementById('pickupPrintTypeFilter');
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchPickupOrders(searchInput.value, 1, printTypeFilter.value);
            }, 400);
        });
    }
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchPickupOrders('', 1, printTypeFilter.value);
        });
    }
    if (printTypeFilter) {
        printTypeFilter.addEventListener('change', function() {
            searchPickupOrders(searchInput.value, 1, printTypeFilter.value);
        });
    }
    // Initial load with filter
    searchPickupOrders(searchInput ? searchInput.value : '', 1, printTypeFilter ? printTypeFilter.value : '');
});

function searchPickupOrders(searchTerm = '', page = 1, printType = '') {
    const container = document.getElementById('pickup-orders-container');
    if (!container) return;
    container.innerHTML = '<div class="no-orders">Searching...</div>';
    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (printType) params.append('print_type', printType);
    params.append('page', page);
    const searchUrl = 'functions/search_to_pickup_orders.php?' + params.toString();
    fetch(searchUrl)
        .then(response => response.json())
        .then(data => {
            displayPickupSearchResults(data, searchTerm, page, printType);
        })
        .catch(() => {
            container.innerHTML = '<div class="no-orders">Error searching orders.</div>';
        });
}

function displayPickupSearchResults(data, searchTerm, currentPage, printType = '') {
    const container = document.getElementById('pickup-orders-container');
    const orders = data.orders || [];
    const totalPages = data.total_pages || 1;
    
    if (orders.length === 0) {
        const message = searchTerm 
            ? `No pickup orders found for ticket "${searchTerm}"`
            : 'No pickup orders found';
        container.innerHTML = `<div class="no-orders">${message}</div>`;
        return;
    }
    
    let html = '';
    
    orders.forEach(order => {
        // Use the thumbnail path from the server response
        const thumbnail = order.thumbnail || order.design_file;
        const statusClass = order.status === 'to-pick-up' ? 'status-approved' : 'status-pending';
        const statusText = order.status === 'to-pick-up' ? 'To Pick Up' : 'Pending';
        
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
                    <span class="quote-date">${order.created_at_formatted}</span>
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
                        data-items='${JSON.stringify(order.items).replace(/'/g, "&#39;")}'>
                    </i>
                    <div class="card-actions">
                        <div class="button-group">
                            <button class="view-details-btn to-pick-up-order-btn" 
                                data-approved-id="${order.id}" 
                                data-approved-ticket="${escapeHtml(order.ticket)}" 
                                data-approved-created-at="${escapeHtml(order.created_at)}"
                                data-approved-admin="${escapeHtml(order.is_approved_admin)}"
                                data-pricing="${escapeHtml(order.pricing)}"
                                data-quantity="${escapeHtml(order.quantity)}"
                                data-subtotal="${escapeHtml(order.subtotal)}"
                                data-admin-approved-date="${escapeHtml(order.admin_approved_date || '')}"
                                data-user-approved-date="${escapeHtml(order.user_approved_date || '')}"
                                data-ready-date="${escapeHtml(order.created_at)}"
                                data-is-for-pickup="${escapeHtml(order.is_for_pickup || '')}"
                                data-pickup-date="${escapeHtml(order.pickup_date || '')}"
                                data-pickup-attempt="${escapeHtml(order.pickup_attempt || '')}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="${escapeHtml(order.design_file)}" class="download-btn" download>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    if (totalPages > 0) {
        html += '<div class="pagination" id="pickup-search-pagination">';
        // Previous button
        if (currentPage > 1) {
            html += `<a href="#" class="page-btn prev-next" data-page="${currentPage - 1}">‹ Prev</a>`;
        } else {
            html += `<span class="page-btn prev-next disabled">‹ Prev</span>`;
        }
        // First page
        if (currentPage > 3) {
            html += `<a href="#" class="page-btn" data-page="1">1</a>`;
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
                html += `<a href="#" class="page-btn" data-page="${i}">${i}</a>`;
            }
        }
        // Last page
        if (currentPage < totalPages - 2) {
            if (currentPage < totalPages - 3) {
                html += `<span class="page-dots">...</span>`;
            }
            html += `<a href="#" class="page-btn" data-page="${totalPages}">${totalPages}</a>`;
        }
        // Next button
        if (currentPage < totalPages) {
            html += `<a href="#" class="page-btn prev-next" data-page="${currentPage + 1}">Next ›</a>`;
        } else {
            html += `<span class="page-btn prev-next disabled">Next ›</span>`;
        }
        html += '</div>';
    }
    container.innerHTML = html;
    // Re-attach event listeners to the new buttons
    document.querySelectorAll('.view-details-btn.to-pick-up-order-btn').forEach(button => {
        button.addEventListener('click', openToPickUpOrderModal);
    });
    // Pagination click events
    container.querySelectorAll('.pagination .page-btn').forEach(btn => {
        if (!btn.classList.contains('current') && !btn.classList.contains('disabled')) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                searchPickupOrders(searchTerm, page, printType);
            });
        }
    });
}

// Clear pickup search functionality
function clearPickupSearch() {
    const searchInput = document.getElementById('ToPickupSearchInput');
    if (searchInput) {
        searchInput.value = '';
        searchPickupOrders('', 1, document.getElementById('pickupPrintTypeFilter').value);
    }
}

// Helper function to escape HTML (reuse from your existing code)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

    
        const userImageViewerModal = document.getElementById('userImageViewerModal');
const userExpandedImage = document.getElementById('userExpandedDesignImage');
const userDetailsodal = document.getElementById('detailsModal'); // Your details modal

// View button
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('view-design-btn')) {
    const designFile = userDetailsodal.getAttribute('data-design-file');
    const isViewable = userDetailsodal.getAttribute('data-is-viewable') === 'true';

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
    const designFile = userDetailsodal.getAttribute('data-design-file');
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
    </script>
</body>
</html>