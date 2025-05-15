
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
    <link rel="stylesheet" href="../assets/css/quote-modal.css">


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

    <style>

</style>

<div class="orders-tabs">
    <button class="tab-button active" data-tab="pending-orders-container">Pending</button>
    <button class="tab-button" data-tab="approved-orders-container">Approved</button>
    <button class="tab-button" data-tab="pickup-orders-container">To Pick Up</button>
    <button class="tab-button" data-tab="ship-orders-container">To Ship</button>
    <button class="tab-button" data-tab="completed-orders-container">Completed</button>
</div>



        <div class="quotes-container" id="pending-orders-container" style="display:flex;">
           
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
                        <button class="view-details-btn view-pending-orders">
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

?>
        </div>

        <div class="quotes-container approved-orders-container" id="approved-orders-container" style="display:none;">
        <h1>asdasdasdasd</h1>
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

    <script src="../assets/js/submit-quote.js"></script>

    <script src="../assets/js/quote-swtich-tab-modal.js"></script>

</body>
</html>