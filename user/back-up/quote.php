
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
    <title>CSH Enterprises | Modern Cloth Printing</title>
    <link rel="icon" href="../assets/images/tshirt.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/quote.css">

    <style>
     /* Order Process Modal Styles */
.order-process-modal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    align-items: center;
    justify-content: center;
}

.order-process-modal-content {
    background-color: #fff;
    padding: 25px;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.order-process-close-btn {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 24px;
    color: #666;
    cursor: pointer;
    transition: color 0.2s;
}

.order-process-close-btn:hover {
    color: #333;
}

.order-process-title {
    font-size: 1.2rem;
    color: #333;
    margin-bottom: 20px;
    font-weight: 600;
}

.order-process-steps-container {
    margin-top: 15px;
}

.order-step {
    display: flex;
    margin-bottom: 20px;
    opacity: 0.6;
}

.order-step-completed {
    opacity: 1;
}

.order-step-number {
    width: 26px;
    height: 26px;
    background-color: #4CAF50;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.order-step-connector {
    width: 2px;
    background-color: #e0e0e0;
    margin: 0 12px;
    min-height: 40px;
}

.order-step-content {
    flex: 1;
    padding-bottom: 15px;
}

.order-step-title {
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
    font-size: 1rem;
}

.order-step-description {
    color: #555;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 5px;
}

.order-step-date {
    color: #777;
    font-size: 0.8rem;
    font-style: italic;
}

.order-summary-details {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin-top: 8px;
    font-size: 0.9rem;
}

.order-subtotal {
    font-weight: 600;
    margin-top: 5px;
    color: #333;
}

.order-approval-section {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.order-approval-actions {
    text-align: center;
}

.order-approval-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 15px;
}

.order-agree-btn, .order-cancel-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.order-agree-btn {
    background-color: #28a745;
    color: white;
}

.order-agree-btn:hover {
    background-color: #218838;
}

.order-cancel-btn {
    background-color: #dc3545;
    color: white;
}

.order-cancel-btn:hover {
    background-color: #c82333;
}

body.order-modal-open {
    overflow: hidden;
}



/* Responsive styles for order approval buttons */
.order-approval-actions {
    margin-top: 20px;
    text-align: center;
}

.order-approval-buttons {
    display: flex;
    flex-direction: column; /* Stack buttons vertically on smaller screens */
    gap: 10px;
    justify-content: center;
    align-items: center;
}

@media (min-width: 768px) {
    .order-approval-buttons {
        flex-direction: row; /* Display buttons side by side on larger screens */
        gap: 15px;
    }
}

.order-agree-btn, .order-cancel-btn {
    width: 100%; /* Full width on smaller screens */
    max-width: 200px; /* Limit button width on larger screens */
    padding: 10px 20px;
    font-size: 0.9rem;
    border-radius: 5px;
    transition: all 0.2s;
}

.order-agree-btn {
    background-color: #28a745;
    color: white;
}

.order-agree-btn:hover {
    background-color: #218838;
}

.order-cancel-btn {
    background-color: #dc3545;
    color: white;
}

.order-cancel-btn:hover {
    background-color: #c82333;
}


/* Agree Confirmation Modal Styles */
.agree-confirmation-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    z-index: 1050;
}

.agree-confirmation-modal.show {
    opacity: 1;
    visibility: visible;
}

.agree-confirmation-modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    max-width: 400px;
    width: 90%;
}

.agree-confirmation-modal-content h3 {
    margin: 0 0 10px;
    font-size: 1.5rem;
    color: #333;
}

.agree-confirmation-modal-content p {
    margin: 0 0 20px;
    font-size: 1rem;
    color: #555;
}

/* Modal Buttons */
.modal-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

.modal-close-btn {
    background-color: #28a745;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.2s ease;
}

.modal-close-btn:hover {
    background-color: #218838;
}

.modal-cancel-btn {
    background-color: #dc3545;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.2s ease;
}

.modal-cancel-btn:hover {
    background-color: #c82333;
}
    </style>

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
                 <img src="../assets/images/icons/tshirt.png" alt="" style="height: 45px; width: 35px;">
                CSH
            </a>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <nav id="nav">
                                
                <ul>
                    <li><a href="home">Home</a></li>
                    <li><a href="home#services">Services</a></li>
                    <li><a href="home#gallery">Gallery</a></li>
                    <li><a href="home#contact">Contact</a></li>
                    <li><a href="quote" class="active">Quote</a></li>                     
                    <li><a href="logout">Logout</a></li>
                </ul>

            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="quotes-container">
            
          
        <?php
include '../db_connection.php';

// Fetch initial orders for the logged-in user
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($order = $result->fetch_assoc()) {
            $status = $order['is_approved_admin'] === 'yes' ? 'Approved' : ($order['admin_approved_date'] ? 'Pending' : 'Processing');
            $statusClass = 'status-' . strtolower(str_replace(' ', '-', $status));
            $createdAt = date('M d, Y', strtotime($order['created_at']));
            $subtotal = $order['pricing'] * $order['quantity'];
            
            // Check if user has already approved/rejected
            $isUserApproved = null;
            if ($order['is_user_approved'] === 'yes') {
                $isUserApproved = true;
            } elseif ($order['is_user_approved'] === 'no') {
                $isUserApproved = false;
            }

            // Format dates
            $pickupDate = $order['pickup_date'] ? date('M d, Y', strtotime($order['pickup_date'])) : 'Pending';
            $processingDate = $order['processing_date'] ? date('M d, Y', strtotime($order['processing_date'])) : 'Pending';
            $deliveredDate = $order['delivered_date'] ? date('M d, Y', strtotime($order['delivered_date'])) : 'Pending';

            echo '
            <div class="quote-card animate__animated animate__fadeInUp">
                <img src="' . htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8') . '" alt="Design" class="card-image">
                <span class="card-status ' . $statusClass . '">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>
                <div class="card-content">
                    <h3 class="card-title">' . htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8') . '</h3>
                    <div class="card-details">
                        <div class="card-detail">
                            <span class="detail-label">Quantity</span>
                            <span class="detail-value">' . htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') . '</span>
                        </div>
                        <div class="card-detail">
                            <span class="detail-label">Ticket #</span>
                            <span class="detail-value">' . htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') . '</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="view-details-btn" onclick="openOrderProcessModal(
                            \'' . htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') . '\',
                            \'' . $createdAt . '\',
                            \'' . ($order['admin_approved_date'] ? date('M d, Y', strtotime($order['admin_approved_date'])) : '') . '\',
                            ' . ($order['is_approved_admin'] === 'yes' ? 'true' : 'false') . ',
                            ' . $order['pricing'] . ',
                            ' . $subtotal . ',
                            ' . $order['quantity'] . ',
                            ' . ($isUserApproved === null ? 'null' : ($isUserApproved ? 'true' : 'false')) . ',
                            ' . ($order['is_for_pickup'] !== null ? 'true' : 'false') . ',
                            \'' . $pickupDate . '\',
                            ' . ($order['is_for_processing'] !== null ? 'true' : 'false') . ',
                            \'' . $processingDate . '\',
                            ' . ($order['is_delivered'] !== null ? 'true' : 'false') . ',
                            \'' . $deliveredDate . '\'
                        )">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <span class="quote-date">' . $createdAt . '</span>
                    </div>
                </div>
            </div>';
        }
    } else {
        echo '<div class="no-orders">No orders found</div>';
    }
    $stmt->close();
} else {
    echo '<div class="no-orders">No user ID found. Please log in.</div>';
}
$conn->close();
?>
        </div>
    </main>


    <div id="orderProcessModal" class="order-process-modal">
    <div class="order-process-modal-content">
        <span class="order-process-close-btn" onclick="closeOrderProcessModal()">&times;</span>
        <h2 id="orderProcessTitle" class="order-process-title"></h2>
        <div id="orderProcessSteps" class="order-process-steps-container"></div>
        <div id="orderApprovalSection" class="order-approval-section"></div>
    </div>
</div>

    <!-- Add Quote Button -->
    <button class="add-quote-btn pulse" id="addQuoteBtn">
        <i class="fas fa-plus-circle"></i> Add Quote
    </button>

    <!-- Quote Modal -->
    <div class="quote-modal" id="quoteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Quote</h2>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <form id="quoteForm" enctype="multipart/form-data" method="post">
            <div class="form-group">
    <label for="designFile">Upload Design</label>
    <div class="file-input-container">
        <div class="file-input-btn">
            <i class="fas fa-cloud-upload-alt"></i>
            <span class="upload-text">Click to upload design file</span>
            <input type="file" id="designFile" name="designFile" class="file-input" accept="image/*,.pdf,.ai,.psd" required>
        </div>
        <div id="file-name" class="file-name-display"></div>
    </div>
</div>
    
    <div class="form-group" style="display: flex; gap: 10px;">
        <div style="flex: 7;">
            <label for="printType">Print Type</label>
            <select id="printType" name="printType" class="form-control" style="width: 100%;" required>
                <option value="">Select print type</option>
                <option value="Direct to Film Print">Direct to Film Print</option>
                <option value="Screen Printing">Screen Printing</option>
                <option value="Emboss Print">Emboss Print</option>
                <option value="Hi-Density Print">Hi-Density Print</option>
                <option value="Glitters Print">Glitters Print</option>
                <option value="Silk Screen Print">Silk Screen Print</option>
            </select>
        </div>
        <div style="flex: 3;">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" class="form-control" min="1" placeholder="Quantity" style="width: 100%; background-color:transparent;" required>
        </div>
    </div>
    
   <input type="text" name="address" id="address" class="address" 
       value="<?php echo htmlspecialchars($full_address, ENT_QUOTES, 'UTF-8'); ?>" readonly style="display:none;">

    <div class="form-group">
        <label for="note">Note</label>
        <textarea id="note" name="note" class="form-control note-input" rows="2" placeholder="Enter any additional notes or instructions" style="background-color:transparent;"></textarea>
    </div>
    
    <button type="submit" class="submit-btn">
        <i class="fas fa-paper-plane"></i> Submit Quote
    </button>
</form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/quote.js"></script>

    <script>
// Open order process modal
function openOrderProcessModal(
    ticketNumber, 
    quotePlacedDate, 
    approvedDate, 
    isApproved, 
    unitPrice, 
    subtotal, 
    quantity, 
    isUserApproved,
    isForPickup,
    pickupDate,
    isForProcessing,
    processingDate,
    isDelivered,
    deliveredDate
) {
    const modal = document.getElementById('orderProcessModal');
    const stepsContainer = document.getElementById('orderProcessSteps');
    const title = document.getElementById('orderProcessTitle');
    
    // Set title with ticket number
    title.textContent = 'Ticket #' + ticketNumber + ' Process Details';
    title.className = 'order-process-title';
    
    // Clear previous steps
    stepsContainer.innerHTML = '';
    
    // Always show the first step (Quote Placed)
    const steps = [
        {
            title: "Quote Placed",
            description: "Your order request has been received",
            date: quotePlacedDate,
            completed: true
        }
    ];
    
    // If admin approved, show pricing details + user confirmation buttons
    if (isApproved) {
        steps.push({
            title: "Admin Approved",
            description: `
                <div class="order-summary-details">
                    <p>Unit Price: ₱${unitPrice.toFixed(2)}</p>
                    <p>Quantity: ${quantity}</p>
                    <p class="order-subtotal">Subtotal: ₱${subtotal.toFixed(2)}</p>
                </div>
            `,
            date: approvedDate,
            completed: true,
            needsUserApproval: (isUserApproved === null)
        });
        
        // Add remaining steps if user has approved
        if (isUserApproved === true) {
            // Pick up step
            if (isForPickup === 'true') {
                steps.push({
                    title: "Pick up",
                    description: "Your items will be picked up at your location",
                    date: pickupDate,
                    completed: pickupDate !== 'Pending'
                });
            }
            
            // Processing step
            if (isForProcessing === 'true') {
                steps.push({
                    title: "Processing",
                    description: "Items are in the printing process",
                    date: processingDate,
                    completed: processingDate !== 'Pending'
                });
            }
            
            // Delivered step
            if (isDelivered === 'true') {
                steps.push({
                    title: "Delivered",
                    description: "Items have been delivered",
                    date: deliveredDate,
                    completed: deliveredDate !== 'Pending'
                });
            }
        }
    } 
    // If pending admin approval, show only "Quote Placed" + "Admin Review"
    else {
        steps.push({
            title: "Admin Review",
            description: "Your order is being reviewed by our team",
            date: approvedDate || "Pending",
            completed: false
        });
    }
    
    // Render steps
    steps.forEach((step, index) => {
        const stepElement = document.createElement('div');
        stepElement.className = `order-step ${step.completed ? 'order-step-completed' : ''}`;
        
        stepElement.innerHTML = `
            <div class="order-step-number">${index + 1}</div>
            <div class="order-step-connector"></div>
            <div class="order-step-content">
                <div class="order-step-title">${step.title}</div>
                <div class="order-step-description">${step.description}</div>
                <div class="order-step-date">${step.date}</div>
            </div>
        `;
        
        // Show user confirmation buttons if admin approved but user hasn't confirmed yet
        if (step.needsUserApproval) {
            stepElement.innerHTML += `
                  <div class="order-approval-actions">
                    <div class="order-approval-buttons">
                        <button class="order-agree-btn" onclick="userConfirmOrder('${ticketNumber}', true)">
                            <i class="fas fa-check-circle"></i> Agree
                        </button>
                        <button class="order-cancel-btn" onclick="userConfirmOrder('${ticketNumber}', false)">
                            <i class="fas fa-times-circle"></i> Reject
                        </button>
                    </div>
                </div>
            `;
        }
        
        stepsContainer.appendChild(stepElement);
    });
    
    // Show the modal
    modal.style.display = 'flex';
    document.body.classList.add('order-modal-open');
}

// User confirms/rejects the approved pricing
function userConfirmOrder(ticketNumber, isConfirmed) {
    // Send AJAX request to update order status
    fetch('update_is_user_approved.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            ticketNumber: ticketNumber,
            isConfirmed: isConfirmed 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create a confirmation modal dynamically
            const confirmationModal = document.createElement('div');
            confirmationModal.className = 'agree-confirmation-modal';
            confirmationModal.innerHTML = `
                <div class="agree-confirmation-modal-content">
                    <h3>${isConfirmed ? "Quote Confirmed" : "Order Rejected"}</h3>
                    <p>${isConfirmed ? "Items will be picked up at your location" : "Your order has been rejected."}</p>
                    <div class="modal-buttons">
                        <button id="closeAgreeConfirmationModal" class="modal-close-btn">Agree & Continue</button>
                        <button id="cancelAgreeConfirmationModal" class="modal-cancel-btn">Cancel</button>
                    </div>
                </div>
            `;

            document.body.appendChild(confirmationModal);

            // Show the modal
            setTimeout(() => {
                confirmationModal.classList.add('show');
            }, 10);

            // Close modal on "OK" button click
            document.getElementById('closeAgreeConfirmationModal').addEventListener('click', () => {
                confirmationModal.classList.remove('show');
                setTimeout(() => {
                    confirmationModal.remove();
                }, 300);

                // Call the toast notification
                if (isConfirmed) {
                    showToast('Agree item will be picked up at your location');
                    setTimeout(() => {
                        location.reload(); // Reload the page after a short delay
                    }, 1500);
                } else {
                    showToast('Order has been rejected', 'error');
                }

                // Close the order process modal
                closeOrderProcessModal();
            });

            // Close modal on "Cancel" button click
            document.getElementById('cancelAgreeConfirmationModal').addEventListener('click', () => {
                confirmationModal.classList.remove('show');
                setTimeout(() => {
                    confirmationModal.remove();
                }, 300);
            });
        } else {
            // Show an error toast
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        // Show an error toast for network issues
        showToast('An error occurred. Please try again.', 'error');
    });
}

function closeOrderProcessModal() {
    document.getElementById('orderProcessModal').style.display = 'none';
    document.body.classList.remove('order-modal-open');
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('orderProcessModal')) {
        closeOrderProcessModal();
    }
});
    </script>



</body>
</html>