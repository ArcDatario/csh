<?php
include '../../db_connection.php'; // Your database connection file

$sql = "SELECT orders.id, orders.user_id, orders.ticket, orders.design_file, orders.print_type, 
               orders.note, orders.address, orders.quantity, orders.created_at, orders.status, 
               orders.pricing, orders.subtotal, orders.processing_date,
               users.name, users.phone_number, users.email 
        FROM orders 
        INNER JOIN users ON orders.user_id = users.id 
        WHERE orders.is_for_processing = 'yes' AND orders.status = 'processing'
        ORDER BY orders.processing_date DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($order = $result->fetch_assoc()) {
        // Determine the appropriate thumbnail based on file extension
        $designFile = $order['design_file'];
        $fileExtension = strtolower(pathinfo($designFile, PATHINFO_EXTENSION));
        
        // Define image formats that can be displayed directly
        $imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $isViewable = in_array($fileExtension, $imageFormats);
        
        if ($isViewable) {
            // For image files, use the actual file
            $thumbnail = "../user/" . htmlspecialchars($designFile, ENT_QUOTES, 'UTF-8');
        } else {
            // For non-image files, use appropriate placeholder
            if ($fileExtension === 'psd') {
                $thumbnail = "../photoshop.png";
            } elseif ($fileExtension === 'pdf') {
                $thumbnail = "../pdf.png";
            } elseif ($fileExtension === 'ai') {
                $thumbnail = "../illustrator.png";
            } else {
                $thumbnail = "../file.png"; // default placeholder
            }
        }
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td><img src="' . $thumbnail . '" width="50" height="50" style="object-fit: cover;"></td>';
        echo '<td>' . htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars(date('M d, Y', strtotime($order['processing_date'])), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td><span class="status status-warning">On-process</span></td>';
        echo '<td>
                <button class="btn btn-outline view-on-process-modal" 
                    data-id="' . htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8') . '"
                    data-user-id="' . htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8') . '"
                    data-ticket="' . htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') . '"
                    data-design="' . htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8') . '"
                    data-mobile="' . htmlspecialchars($order['phone_number'], ENT_QUOTES, 'UTF-8') . '"
                    data-name="' . htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8') . '"
                    data-print-type="' . htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8') . '"
                    data-quantity="' . htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') . '"
                    data-date="' . htmlspecialchars(date('M d, Y', strtotime($order['processing_date'])), ENT_QUOTES, 'UTF-8') . '"
                    data-status="' . htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') . '"
                    data-note="' . htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8') . '"
                    data-address="' . htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8') . '"
                    data-email="' . htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') . '"
                    data-pricing="' . htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8') . '"
                    data-subtotal="' . htmlspecialchars($order['subtotal'], ENT_QUOTES, 'UTF-8') . '"
                    data-viewable="' . ($isViewable ? 'yes' : 'no') . '">
                    View
                </button>
              </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7" class="text-center">No orders currently being processed</td></tr>';
}
?>