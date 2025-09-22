<?php
include '../../db_connection.php'; // Your database connection file


// Read filters from GET
$filter_print  = $_GET['print_type'] ?? '';
$filter_start  = $_GET['start_date'] ?? '';
$filter_end    = $_GET['end_date'] ?? '';
$filter_search = $_GET['search'] ?? '';


$where_clauses = [
    "orders.status = 'processing'",
    "orders.is_for_processing = 'yes'"
];

if ($filter_print !== '') {
    $where_clauses[] = "orders.print_type = '" . $conn->real_escape_string($filter_print) . "'";
}
if ($filter_start !== '' && $filter_end !== '') {
    $where_clauses[] = "DATE(orders.created_at) BETWEEN '" . $conn->real_escape_string($filter_start) . "' 
                        AND '" . $conn->real_escape_string($filter_end) . "'";
}
if ($filter_search !== '') {
    $search = $conn->real_escape_string($filter_search);
    $where_clauses[] = "(orders.ticket LIKE '%$search%' OR users.name LIKE '%$search%')";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$query = "SELECT orders.*, users.name, users.email, users.phone_number
        FROM orders 
        INNER JOIN users ON orders.user_id = users.id 
          $where_sql
          ORDER BY orders.created_at DESC";

$result = $conn->query($query);

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
                    data-viewable="' . ($isViewable ? 'yes' : 'no') . '"
                    data-items=\''.json_encode($shirtItems, JSON_HEX_APOS | JSON_HEX_QUOT).'\'>
                    View
                </button>
              </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7" class="text-center">No orders currently being processed</td></tr>';
}
?>