<?php
require_once '../../db_connection.php'; // Include your database connection file

// Fetch orders on pickup (status = 'to-pick-up' AND is_for_pickup = 'yes')
$sql = "SELECT orders.*, users.name, users.phone_number, users.email 
        FROM orders 
        INNER JOIN users ON orders.user_id = users.id 
        WHERE orders.status = 'to-pick-up' AND orders.is_for_pickup = 'yes'
        ORDER BY orders.created_at DESC";
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
        
        echo '<tr>
                <td>'.htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8').'</td>
                <td>
                    <div class="user-cell">
                        <img src="'.$thumbnail.'" alt="file design" width="50" height="50" onerror="this.onerror=null; this.src=\'../placeholder-image.png\';">
                        <span>'.htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8').'</span>
                    </div>
                </td>
                <td>'.htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($order['pickup_attempt'], ENT_QUOTES, 'UTF-8').'</td>
                <td>
                    <span class="status status-warning">
                        On Pickup
                    </span>
                </td>
                <td>
                    <button class="btn btn-outline view-on-pickup-modal" 
                            data-id="'.htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8').'"
                            data-user-id="'.htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8').'"
                            data-ticket="'.htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8').'"
                            data-design="'.htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8').'"
                            data-mobile="'.htmlspecialchars($order['phone_number'], ENT_QUOTES, 'UTF-8').'"
                            data-name="'.htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8').'"
                            data-print-type="'.htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8').'"
                            data-quantity="'.htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8').'"
                            data-date="'.htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8').'"
                            data-status="'.htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8').'"
                            data-note="'.htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8').'"
                            data-address="'.htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8').'"
                            data-email="'.htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8').'"
                            data-pricing="'.htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8').'"
                            data-subtotal="'.htmlspecialchars($order['subtotal'], ENT_QUOTES, 'UTF-8').'"
                            data-items=\''.htmlspecialchars(json_encode($shirtItems), ENT_QUOTES, 'UTF-8').'\'>
                        View
                    </button>
                </td>
            </tr>';
    }
} else {
    echo '<tr><td colspan="8">No orders on pickup</td></tr>';
}
?>