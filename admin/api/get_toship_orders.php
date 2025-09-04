<?php
require_once '../../db_connection.php';

$sql = "SELECT orders.*, users.name, users.email, users.phone_number as mobile, users.address 
        FROM orders 
        INNER JOIN users ON orders.user_id = users.id 
        WHERE orders.status = 'to_ship'
        ORDER BY orders.shipping_date DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($order = $result->fetch_assoc()) {
        // Extract just the filename from the path
        $designFilePath = $order['design_file'];
        $filename = basename($designFilePath);
        
        // Get the file extension
        $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Set thumbnail based on file extension
        if ($fileExtension === 'psd') {
            $thumbnail = "../photoshop.png";
        } elseif ($fileExtension === 'pdf') {
            $thumbnail = "../pdf.png";
        } elseif ($fileExtension === 'ai') {
            $thumbnail = "../illustrator.png";
        } else {
            // For image files, use the actual file
            $thumbnail = "../user/" . $designFilePath;
        }
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($order['ticket']) . '</td>';
        echo '<td>';
        echo '<div class="user-cell">';
        echo '<img src="' . $thumbnail . '" alt="file design" width="50" height="50" onerror="this.onerror=null; this.src=\'../placeholder-image.png\';">';
        echo '<span>' . htmlspecialchars($order['name']) . '</span>';
        echo '</div>';
        echo '</td>';
        echo '<td>' . htmlspecialchars($order['print_type']) . '</td>';
        echo '<td>' . htmlspecialchars($order['quantity']) . '</td>';
        echo '<td>' . htmlspecialchars(date('M d, Y', strtotime($order['shipping_date']))) . '</td>';
        echo '<td><span class="status status-info">' . htmlspecialchars($order['status']) . '</span></td>';
        echo '<td>';
        echo '<button class="btn btn-outline view-to-ship-modal" ';
        echo 'data-id="' . htmlspecialchars($order['id']) . '" ';
        echo 'data-user-id="' . htmlspecialchars($order['user_id']) . '" ';
        echo 'data-ticket="' . htmlspecialchars($order['ticket']) . '" ';
        echo 'data-design="' . htmlspecialchars($order['design_file']) . '" ';
        echo 'data-mobile="' . htmlspecialchars($order['mobile']) . '" ';
        echo 'data-name="' . htmlspecialchars($order['name']) . '" ';
        echo 'data-print-type="' . htmlspecialchars($order['print_type']) . '" ';
        echo 'data-quantity="' . htmlspecialchars($order['quantity']) . '" ';
        echo 'data-date="' . htmlspecialchars(date('M d, Y', strtotime($order['shipping_date']))) . '" ';
        echo 'data-status="' . htmlspecialchars($order['status']) . '" ';
        echo 'data-note="' . htmlspecialchars($order['note']) . '" ';
        echo 'data-address="' . htmlspecialchars($order['address']) . '" ';
        echo 'data-email="' . htmlspecialchars($order['email']) . '" ';
        echo 'data-pricing="' . htmlspecialchars($order['pricing']) . '" ';
        echo 'data-subtotal="' . htmlspecialchars($order['subtotal']) . '">';
        echo 'View</button>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7">No orders ready to ship</td></tr>';
}

$conn->close();
?>