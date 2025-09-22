<?php
require '../../db_connection.php';

// --- Filter setup ---
$filter_print  = isset($_GET['print_type']) ? trim($_GET['print_type']) : '';
$filter_start  = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end    = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filter_search = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Build WHERE clauses ---
$where_clauses = [];
$where_clauses[] = "orders.status = 'pending'"; // always pending

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

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// --- Query orders ---
$sql = "SELECT orders.*, users.name, users.phone_number, users.email
        FROM orders
        INNER JOIN users ON orders.user_id = users.id
        $where_sql
        ORDER BY orders.created_at DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($order = $result->fetch_assoc()) {
        // Fetch shirt items
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

        // Determine thumbnail
        $designFile = $order['design_file'];
        $fileExtension = strtolower(pathinfo($designFile, PATHINFO_EXTENSION));
        $imageFormats = ['jpg','jpeg','png','gif','bmp','webp'];
        $isViewable = in_array($fileExtension, $imageFormats);

        if ($isViewable) {
            $thumbnail = "../user/" . htmlspecialchars($designFile, ENT_QUOTES, 'UTF-8');
        } else {
            $icons = [
                'psd' => "../photoshop.png",
                'pdf' => "../pdf.png",
                'ai'  => "../illustrator.png"
            ];
            $thumbnail = $icons[$fileExtension] ?? "../file.png";
        }

        echo '<tr>
                <td>'.htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8').'</td>
                <td>
                    <div class="user-cell">
                        <img src="'.$thumbnail.'" alt="Design file" width="50" height="50" style="object-fit: cover;">
                        <span>'.htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8').'</span>
                    </div>
                </td>
                <td>'.htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8').'</td>
                <td>'.htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8').'</td>
                <td>
                    <span class="status status-warning">'.htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8').'</span>
                </td>
                <td>
                    <button class="btn btn-outline view-quote-modal"
                            data-id="'.htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8').'"
                            data-user-id="'.htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8').'"
                            data-ticket="'.htmlspecialchars($order['ticket'], ENT_QUOTES, 'UTF-8').'"
                            data-design="'.htmlspecialchars($order['design_file'], ENT_QUOTES, 'UTF-8').'"
                            data-mobile="'.htmlspecialchars($order['phone_number'], ENT_QUOTES, 'UTF-8').'"
                            data-name="'.htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8').'"
                            data-email="'.htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8').'"
                            data-print-type="'.htmlspecialchars($order['print_type'], ENT_QUOTES, 'UTF-8').'"
                            data-quantity="'.htmlspecialchars($order['quantity'], ENT_QUOTES, 'UTF-8').'"
                            data-date="'.htmlspecialchars(date('M d, Y', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8').'"
                            data-status="'.htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8').'"
                            data-note="'.htmlspecialchars($order['note'], ENT_QUOTES, 'UTF-8').'"
                            data-address="'.htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8').'"
                            data-pricing="'.htmlspecialchars($order['pricing'], ENT_QUOTES, 'UTF-8').'"
                            data-subtotal="'.htmlspecialchars($order['subtotal'], ENT_QUOTES, 'UTF-8').'"
                            data-items=\''.json_encode($shirtItems, JSON_HEX_APOS | JSON_HEX_QUOT).'\'
                            data-viewable="'.($isViewable ? 'yes' : 'no').'">
                        View
                    </button>
                </td>
              </tr>';
    }
} else {
    echo '<tr><td colspan="7" class="text-center">No orders found</td></tr>';
}
?>
