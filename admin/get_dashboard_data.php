<?php
require_once 'auth_check.php';
require_once '../db_connection.php';

header('Content-Type: application/json');

try {
    // Get current month and year
    $currentMonth = date('m');
    $currentYear = date('Y');
    $lastMonth = date('m', strtotime('-1 month'));
    $lastMonthYear = date('Y', strtotime('-1 month'));

    // Function to execute query safely
    function executeQuery($conn, $query, $params = [], $types = "") {
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        return $stmt->get_result();
    }

    // 1. Total Revenue (completed orders this month)
    $query = "SELECT SUM(total) as total_revenue FROM orders WHERE status = 'completed' 
              AND MONTH(completion_date) = ? AND YEAR(completion_date) = ?";
    $result = executeQuery($conn, $query, [$currentMonth, $currentYear], "ii");
    $currentRevenue = $result->fetch_assoc()['total_revenue'] ?? 0;

    // Last month's revenue for comparison
    $result = executeQuery($conn, $query, [$lastMonth, $lastMonthYear], "ii");
    $lastMonthRevenue = $result->fetch_assoc()['total_revenue'] ?? 0;

    // Calculate percentage change
    $revenueChange = 0;
    if ($lastMonthRevenue > 0) {
        $revenueChange = (($currentRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
    }
    $revenueChangeFormatted = number_format(abs($revenueChange), 1);
    $revenueTrendClass = $revenueChange >= 0 ? 'positive' : 'negative';
    $revenueTrendIcon = $revenueChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

    // 2. Expected Revenue (all non-completed, non-rejected orders)
    $query = "SELECT SUM(subtotal) as expected_revenue FROM orders 
              WHERE status NOT IN ('completed', 'rejected') 
              AND MONTH(created_at) = ? AND YEAR(created_at) = ?";
    $result = executeQuery($conn, $query, [$currentMonth, $currentYear], "ii");
    $expectedRevenue = $result->fetch_assoc()['expected_revenue'] ?? 0;

    // 3. New Users (this month)
    $query = "SELECT COUNT(*) as new_users FROM users 
              WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?";
    $result = executeQuery($conn, $query, [$currentMonth, $currentYear], "ii");
    $currentUsers = $result->fetch_assoc()['new_users'] ?? 0;

    // Last month's users for comparison
    $result = executeQuery($conn, $query, [$lastMonth, $lastMonthYear], "ii");
    $lastMonthUsers = $result->fetch_assoc()['new_users'] ?? 0;

    // Calculate percentage change
    $usersChange = 0;
    if ($lastMonthUsers > 0) {
        $usersChange = (($currentUsers - $lastMonthUsers) / $lastMonthUsers) * 100;
    }
    $usersChangeFormatted = number_format(abs($usersChange), 1);
    $usersTrendClass = $usersChange >= 0 ? 'positive' : 'negative';
    $usersTrendIcon = $usersChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

    // 4. Pending Orders (this month, not completed or rejected)
    $query = "SELECT COUNT(*) as pending_orders FROM orders 
              WHERE status NOT IN ('completed', 'rejected') 
              AND MONTH(created_at) = ? AND YEAR(created_at) = ?";
    $result = executeQuery($conn, $query, [$currentMonth, $currentYear], "ii");
    $pendingOrders = $result->fetch_assoc()['pending_orders'] ?? 0;

    // Get monthly revenue data for the bar chart
    $query = "SELECT MONTH(completion_date) as month, SUM(total) as monthly_revenue 
              FROM orders 
              WHERE status = 'completed' AND YEAR(completion_date) = ?
              GROUP BY MONTH(completion_date) 
              ORDER BY MONTH(completion_date)";
    $result = executeQuery($conn, $query, [$currentYear], "i");

    $monthlyRevenue = array_fill(0, 12, 0); // Initialize all months with 0 (0-indexed)
    while ($row = $result->fetch_assoc()) {
        $monthlyRevenue[$row['month'] - 1] = $row['monthly_revenue'] ?? 0; // Adjust for 0-index
    }

    // Get service distribution for the doughnut chart
    $query = "SELECT print_type, COUNT(*) as count 
              FROM orders 
              WHERE status = 'completed' 
              GROUP BY print_type";
    $result = executeQuery($conn, $query);

    $serviceLabels = [];
    $serviceData = [];
    while ($row = $result->fetch_assoc()) {
        $serviceLabels[] = $row['print_type'];
        $serviceData[] = $row['count'];
    }

    $totalServices = array_sum($serviceData);
    $servicePercentages = array_map(function($count) use ($totalServices) {
        return $totalServices > 0 ? round(($count / $totalServices) * 100) : 0;
    }, $serviceData);

    echo json_encode([
        'currentRevenue' => $currentRevenue,
        'expectedRevenue' => $expectedRevenue,
        'currentUsers' => $currentUsers,
        'pendingOrders' => $pendingOrders,
        'revenueChangeFormatted' => $revenueChangeFormatted,
        'revenueTrendClass' => $revenueTrendClass,
        'revenueTrendIcon' => $revenueTrendIcon,
        'usersChangeFormatted' => $usersChangeFormatted,
        'usersTrendClass' => $usersTrendClass,
        'usersTrendIcon' => $usersTrendIcon,
        'monthlyRevenue' => $monthlyRevenue,
        'serviceLabels' => $serviceLabels,
        'servicePercentages' => $servicePercentages
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>