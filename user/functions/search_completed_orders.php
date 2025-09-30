<?php
require '../../auth_check.php';
require '../../db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}


$search_term = $_GET['search'] ?? '';
$print_type = $_GET['print_type'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

try {

    // Build query with search and print type filter
    $where_conditions = ["orders.user_id = ?", "orders.status = 'completed'"];
    $params = [$user_id];
    $param_types = "i";

    if (!empty($search_term)) {
        $where_conditions[] = "orders.ticket LIKE ?";
        $params[] = "$search_term%";
        $param_types .= "s";
    }
    if (!empty($print_type)) {
        $where_conditions[] = "orders.print_type = ?";
        $params[] = $print_type;
        $param_types .= "s";
    }

    $where_clause = implode(" AND ", $where_conditions);

    // Count total matching orders
    $count_sql = "SELECT COUNT(*) AS total FROM orders WHERE $where_clause";
    $count_stmt = $conn->prepare($count_sql);
    if (!$count_stmt) {
        throw new Exception("Count prepare failed: " . $conn->error);
    }
    
    $count_stmt->bind_param($param_types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_orders = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_orders / $limit);
    $count_stmt->close();

    // Fetch paginated orders - SELECT ALL FIELDS NEEDED
    $sql = "SELECT orders.*, users.name, users.phone_number 
            FROM orders 
            INNER JOIN users ON orders.user_id = users.id 
            WHERE $where_clause 
            ORDER BY orders.created_at DESC 
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $param_types .= "ii";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($order = $result->fetch_assoc()) {
        // Fetch items for each order
        $items_sql = "SELECT shirt_color, quantity FROM items WHERE order_id = ?";
        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->bind_param("i", $order['id']);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $shirtItems = [];
        while ($item = $items_result->fetch_assoc()) {
            $shirtItems[] = $item;
        }
        $items_stmt->close();
        
        // Thumbnail logic
        $designFile = $order['design_file'];
        $ext = strtolower(pathinfo($designFile, PATHINFO_EXTENSION));
        $thumbnail = ($ext === 'psd') ? "../photoshop.png" : 
                    (($ext === 'pdf') ? "../pdf.png" : 
                    (($ext === 'ai') ? "../illustrator.png" : $designFile));
        
        // Add ALL required data for the modal
        $order['thumbnail'] = $thumbnail;
        $order['items'] = $shirtItems;
        $order['created_at_formatted'] = date('M d, Y', strtotime($order['completion_date']));
        
        // Add completed order specific fields
        if ($order['completion_date']) {
            $order['completion_date_formatted'] = date('M d, Y', strtotime($order['completion_date']));
        } else {
            $order['completion_date_formatted'] = '';
        }
        
        // Ensure all modal fields are present
        $order['pricing'] = $order['pricing'] ?? 0;
        $order['subtotal'] = $order['subtotal'] ?? 0;
        $order['admin_approved_date'] = $order['admin_approved_date'] ?? '';
        $order['user_approved_date'] = $order['user_approved_date'] ?? '';
        $order['processing_date'] = $order['processing_date'] ?? '';
        $order['shipping_date'] = $order['shipping_date'] ?? '';
        $order['completion_date'] = $order['completion_date'] ?? '';
        $order['is_for_pickup'] = $order['is_for_pickup'] ?? 0;
        $order['pickup_date'] = $order['pickup_date'] ?? '';
        
        $orders[] = $order;
    }

    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'total_orders' => $total_orders
    ]);

    $stmt->close();
    
} catch (Exception $e) {
    // Make sure error response is valid JSON
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>