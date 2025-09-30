<?php
require '../../auth_check.php';
require '../../db_connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$printType = isset($_GET['print_type']) ? trim($_GET['print_type']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

try {
    // Build the base query for counting total records
    $count_sql = "SELECT COUNT(*) AS total 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.user_id = ? AND o.status = 'cancelled'";
    
    // Build the base query for fetching records
    $sql = "SELECT o.*, u.name, u.phone_number 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.user_id = ? AND o.status = 'cancelled'";
    
    $params = [$user_id];
    $param_types = "i";
    
    // Add search condition if search term is provided
    if (!empty($searchTerm)) {
        $searchCondition = " AND o.ticket LIKE ?";
        $count_sql .= $searchCondition;
        $sql .= $searchCondition;
        $params[] = "$searchTerm%";
        $param_types .= "s";
    }
    
    // Add print type filter if provided
    if (!empty($printType)) {
        $printCondition = " AND o.print_type = ?";
        $count_sql .= $printCondition;
        $sql .= $printCondition;
        $params[] = $printType;
        $param_types .= "s";
    }
    
    // Add ordering and pagination
    $sql .= " ORDER BY o.cancelled_date DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $param_types .= "ii";
    
    // Count total records
    $count_stmt = $conn->prepare($count_sql);
    
    // Bind parameters for count query
    if (!empty($searchTerm) && !empty($printType)) {
        $count_stmt->bind_param("iss", $user_id, $searchTerm, $printType);
    } elseif (!empty($searchTerm)) {
        $count_stmt->bind_param("is", $user_id, $searchTerm);
    } elseif (!empty($printType)) {
        $count_stmt->bind_param("is", $user_id, $printType);
    } else {
        $count_stmt->bind_param("i", $user_id);
    }
    
    $count_stmt->execute();
    $total_result = $count_stmt->get_result();
    $total_orders = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_orders / $limit);
    $count_stmt->close();
    
    // Fetch paginated records
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    
    if ($result->num_rows > 0) {
        while ($order = $result->fetch_assoc()) {
            // Fetch shirt items for this order
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
            
            // Format dates
            $createdAt = date('M d, Y', strtotime($order['created_at']));
            $cancelledAt = date('M d, Y', strtotime($order['cancelled_date']));
            
            // Determine thumbnail
            $designFile = $order['design_file'];
            $ext = strtolower(pathinfo($designFile, PATHINFO_EXTENSION));
            $thumbnail = ($ext === 'psd') ? "../photoshop.png" : 
                        (($ext === 'pdf') ? "../pdf.png" : 
                        (($ext === 'ai') ? "../illustrator.png" : $designFile));
            
            $orders[] = [
                'id' => $order['id'],
                'user_id' => $order['user_id'],
                'ticket' => $order['ticket'],
                'design_file' => $order['design_file'],
                'thumbnail' => $thumbnail,
                'phone_number' => $order['phone_number'],
                'name' => $order['name'],
                'print_type' => $order['print_type'],
                'quantity' => $order['quantity'],
                'created_at' => $order['created_at'],
                'created_at_formatted' => $createdAt,
                'cancelled_date_formatted' => $cancelledAt,
                'status' => $order['status'],
                'note' => $order['note'],
                'address' => $order['address'],
                'pricing' => $order['pricing'],
                'subtotal' => $order['subtotal'],
                'cancellation_reason' => $order['cancellation_reason'] ?? '',
                'items' => $shirtItems
            ];
        }
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'total_orders' => $total_orders
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>