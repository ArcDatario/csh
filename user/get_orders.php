<?php
// Include database connection
require_once '../db_connection.php';

// Fetch orders from database
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($order = $result->fetch_assoc()) {
        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $order['status']));
        $createdAt = date('M d, Y', strtotime($order['created_at']));
        
        echo '
        <div class="quote-card animate__animated animate__fadeInUp">
            <img src="' . htmlspecialchars($order['design_file']) . '" alt="Design" class="card-image">
            <span class="card-status ' . $statusClass . '">' . htmlspecialchars($order['status']) . '</span>
            <div class="card-content">
                <h3 class="card-title">' . htmlspecialchars($order['print_type']) . '</h3>
                <div class="card-details">
                    <div class="card-detail">
                        <span class="detail-label">Quantity</span>
                        <span class="detail-value">' . htmlspecialchars($order['quantity']) . '</span>
                    </div>
                    <div class="card-detail">
                        <span class="detail-label">Ticket #</span>
                        <span class="detail-value">' . htmlspecialchars($order['ticket']) . '</span>
                    </div>
                </div>
                <div class="card-actions">
                    <button class="view-details-btn" onclick="openProcessModal(\'' . htmlspecialchars($order['id']) . '\')">
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

$conn->close();
?>