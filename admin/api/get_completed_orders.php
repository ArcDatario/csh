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
            orders.delivered_date,
            orders.completion_date,
            users.name, 
            users.phone_number, 
            users.email 
        FROM orders 
        INNER JOIN users ON orders.user_id = users.id 
        WHERE orders.status = 'completed'
        ORDER BY orders.completion_date DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($order = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($order['ticket']) . '</td>';
        echo '<td>';
        echo '<div class="user-cell">';
        echo '<img src="../user/' . htmlspecialchars($order['design_file']) . '" alt="file design" width="50" height="50">';
        echo '<span>' . htmlspecialchars($order['name']) . '</span>';
        echo '</div>';
        echo '</td>';
        echo '<td>' . htmlspecialchars($order['print_type']) . '</td>';
        echo '<td>' . htmlspecialchars($order['quantity']) . '</td>';
        echo '<td>' . (isset($order['completion_date']) ? date('M d, Y', strtotime($order['completion_date'])) : 'N/A') . '</td>';
        echo '<td><span class="status status-success">Completed</span></td>';
        echo '<td>';
        echo '<button class="btn btn-outline view-completed-modal" 
                data-id="' . htmlspecialchars($order['id']) . '"
                data-user-id="' . htmlspecialchars($order['user_id']) . '"
                data-ticket="' . htmlspecialchars($order['ticket']) . '"
                data-design="' . htmlspecialchars($order['design_file']) . '"
                data-mobile="' . htmlspecialchars($order['phone_number']) . '"
                data-name="' . htmlspecialchars($order['name']) . '"
                data-print-type="' . htmlspecialchars($order['print_type']) . '"
                data-quantity="' . htmlspecialchars($order['quantity']) . '"
                data-date="' . (isset($order['completion_date']) ? date('M d, Y', strtotime($order['completion_date'])) : 'N/A') . '"
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
                data-delivered="' . htmlspecialchars($order['delivered_date']) . '"
                data-completed="' . htmlspecialchars($order['completion_date']) . '">View</button>';
        echo '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7">No completed orders found</td></tr>';
}
?>