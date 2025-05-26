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

if ($result->num_rows > 0) {
    while ($order = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') . '</td>
                <td>
                    <div class="user-cell">
                        <img src="../user/' . htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8') . '" alt="file design" width="50" height="50">
                        <span>' . htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8') . '</span>
                    </div>
                </td>
                <td>' . htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8') . '</td>
                <td>' . htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') . '</td>
                <td>' . htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') . '</td>
                <td>
                    <span class="status status-success">
                        ' . htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') . '
                    </span>
                </td>
                <td>
                    <button class="btn btn-outline view-pickup-modal" 
                            data-id="' . htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8') . '"
                            data-user-id="' . htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8') . '"
                            data-ticket="' . htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8') . '"
                            data-design="' . htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8') . '"
                            data-mobile="' . htmlspecialchars($order['phone_number'], ENT_QUOTES, 'UTF-8') . '"
                            data-name="' . htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8') . '"
                            data-print-type="' . htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8') . '"
                            data-quantity="' . htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8') . '"
                            data-date="' . htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') . '"
                            data-status="' . htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') . '"
                            data-note="' . htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8') . '"
                            data-address="' . htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8') . '"
                            data-email="' . htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') . '"
                            data-pricing="' . htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8') . '"
                            data-subtotal="' . htmlspecialchars($order['subtotal'], ENT_QUOTES, 'UTF-8') . '">
                        View
                    </button>
                </td>
            </tr>';
    }
} else {
    echo '<tr>
            <td colspan="7">No orders ready for pickup</td>
        </tr>';
}

$conn->close();
?>