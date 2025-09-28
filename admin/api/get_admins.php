<?php
require_once '../../db_connection.php';
header('Content-Type: application/json');

$response = ['success' => false, 'data' => [], 'pagination' => []];

try {
    // Get parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $items_per_page = 7;

    // Calculate offset
    $offset = ($page - 1) * $items_per_page;

    // Build query
    $base_query = "FROM admins WHERE 1=1";
    
    // Add search condition if provided
    if (!empty($search)) {
        $search_term = $conn->real_escape_string($search);
        $base_query .= " AND (username LIKE '%$search_term%' OR fullname LIKE '%$search_term%' OR role LIKE '%$search_term%')";
    }

    // Get total count
    $count_query = "SELECT COUNT(*) as total " . $base_query;
    $count_result = $conn->query($count_query);
    $total_items = $count_result ? intval($count_result->fetch_assoc()['total']) : 0;
    $total_pages = ceil($total_items / $items_per_page);

    // Get paginated results
    $select_query = "SELECT id, username, fullname, role " . $base_query . " ORDER BY username ASC LIMIT $offset, $items_per_page";
    $result = $conn->query($select_query);
    
    if ($result) {
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
        
        $response = [
            'success' => true,
            'data' => $admins,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => $total_items,
                'items_per_page' => $items_per_page
            ]
        ];
    } else {
        $response['message'] = 'Database error: ' . $conn->error;
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>