<?php
// get_pickup_orders.php
require_once '../../db_connection.php';

// Fetch orders ready for pickup (status = 'approved' and is_for_pickup = 'no')
$sql = "SELECT orders.id, orders.user_id, orders.ticket, orders.design_file, orders.print_type, 
               orders.note, orders.address, orders.quantity, orders.created_at, orders.status, 
               orders.pricing, orders.subtotal, users.name, users.phone_number, users.email 
        FROM orders 
        INNER JOIN users ON orders.user_id = users.id 
        WHERE orders.status = 'to-pick-up' AND orders.is_for_pickup = 'no'
        ORDER BY orders.created_at DESC";

$result = $conn->query($sql);
?>

<?php if ($result->num_rows > 0): ?>
    <?php while ($order = $result->fetch_assoc()): 
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
    ?>
        <tr>
            <td><?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <div class="user-cell">
                    <img src="<?php echo $thumbnail; ?>" alt="file design" width="50" height="50" onerror="this.onerror=null; this.src='../placeholder-image.png';">
                    <span><?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </td>
            <td><?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
            <td>
                <span class="status status-success">
                    <?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </td>
            <td>
                <button class="btn btn-outline view-pickup-modal" 
                        data-id="<?php echo htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-user-id="<?php echo htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-ticket="<?php echo htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-design="<?php echo htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-mobile="<?php echo htmlspecialchars($order['phone_number'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-name="<?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-print-type="<?php echo htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-quantity="<?php echo htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-date="<?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8'); ?>"
                        data-status="<?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-note="<?php echo htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-address="<?php echo htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-email="<?php echo htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-pricing="<?php echo htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-subtotal="<?php echo htmlspecialchars($order['subtotal'], ENT_QUOTES, 'UTF-8'); ?>">
                    View
                </button>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="7">No orders ready for pickup</td>
    </tr>
<?php endif; ?>

<?php
$conn->close();
?>