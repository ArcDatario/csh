<?php
require_once '../../db_connection.php';

$sql = "SELECT 
            orders.id, 
            orders.ticket, 
            orders.user_id, 
            orders.print_type, 
            orders.quantity, 
            orders.pricing, 
            orders.subtotal, 
            orders.note, 
            orders.status, 
            orders.address, 
            orders.design_file, 
            orders.created_at,
            orders.designer_approved_date,
            orders.admin_approved_date,
            orders.processing_date,
            orders.pickup_date,
            orders.shipping_date,
            orders.cancelled_date,
            orders.cancellation_reason,
            users.name, 
            users.phone_number, 
            users.email 
        FROM orders 
        INNER JOIN users ON orders.user_id = users.id 
        WHERE orders.status = 'cancelled'
        ORDER BY orders.cancelled_date DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($order = $result->fetch_assoc()) {

        // Fetch shirt items for this order
        $items_sql = "SELECT shirt_color, quantity 
                      FROM items 
                      WHERE order_id = " . intval($order['id']);
        $items_result = $conn->query($items_sql);

        $shirtItems = [];
        if ($items_result && $items_result->num_rows > 0) {
            while ($item = $items_result->fetch_assoc()) {
                $shirtItems[] = $item;
            }
        }

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
        echo '<td>' . (isset($order['cancelled_date']) ? date('M d, Y', strtotime($order['cancelled_date'])) : 'N/A') . '</td>';
        echo '<td><span class="status status-cancelled">Cancelled</span></td>';
        echo '<td>';
        echo '<button class="btn btn-outline view-cancelled-modal" 
                data-id="' . htmlspecialchars($order['id']) . '"
                data-user-id="' . htmlspecialchars($order['user_id']) . '"
                data-ticket="' . htmlspecialchars($order['ticket']) . '"
                data-design="' . htmlspecialchars($order['design_file']) . '"
                data-mobile="' . htmlspecialchars($order['phone_number']) . '"
                data-name="' . htmlspecialchars($order['name']) . '"
                data-print-type="' . htmlspecialchars($order['print_type']) . '"
                data-quantity="' . htmlspecialchars($order['quantity']) . '"
                data-items=\'' . json_encode($shirtItems, JSON_HEX_APOS | JSON_HEX_QUOT) . '\'
                data-date="' . (isset($order['cancelled_date']) ? date('M d, Y', strtotime($order['cancelled_date'])) : 'N/A') . '"
                data-status="' . htmlspecialchars($order['status']) . '"
                data-note="' . htmlspecialchars($order['note']) . '"
                data-address="' . htmlspecialchars($order['address']) . '"
                data-email="' . htmlspecialchars($order['email']) . '"
                data-pricing="' . htmlspecialchars($order['pricing']) . '"
                data-subtotal="' . htmlspecialchars($order['subtotal']) . '"
                data-created="' . htmlspecialchars($order['created_at']) . '"
                data-designer-approved="' . htmlspecialchars($order['designer_approved_date']) . '"
                data-admin-approved="' . htmlspecialchars($order['admin_approved_date']) . '"
                data-processing="' . htmlspecialchars($order['processing_date']) . '"
                data-pickup="' . htmlspecialchars($order['pickup_date']) . '"
                data-shipping="' . htmlspecialchars($order['shipping_date']) . '"
                data-cancelled="' . htmlspecialchars($order['cancelled_date']) . '"
                data-reason="' . htmlspecialchars($order['cancellation_reason']) . '">View</button>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7">No cancelled orders found</td></tr>';
}
?>