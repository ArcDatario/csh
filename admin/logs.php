<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Role-based redirect (Field Manager, Designer restrictions)
if (isset($_SESSION['admin_role'])) {
    $current_page = basename($_SERVER['PHP_SELF']);
    
    if ($_SESSION['admin_role'] === "Field Manager" && $current_page != 'inventory.php') {
        header('Location: inventory.php');
        exit();
    }
    
    if ($_SESSION['admin_role'] === "Designer" && $current_page != 'orders.php') {
        header('Location: orders.php');
        exit();
    }
}

// Include database connection
require_once '../db_connection.php';

// --- Filter setup ---
$filter_role = isset($_GET['role']) ? trim($_GET['role']) : '';
$filter_category = isset($_GET['category']) ? trim($_GET['category']) : '';
$filter_start = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filter_end = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// --- Build WHERE clauses ---
$where_clauses = [];
if ($filter_role !== '') {
    $where_clauses[] = "a.role = '" . $conn->real_escape_string($filter_role) . "'";
}
if ($filter_category !== '') {
    $where_clauses[] = "sl.is_from = '" . $conn->real_escape_string($filter_category) . "'";
}
if ($filter_start !== '' && $filter_end !== '') {
    $where_clauses[] = "DATE(sl.created_at) BETWEEN '" . $conn->real_escape_string($filter_start) . "' 
                        AND '" . $conn->real_escape_string($filter_end) . "'";
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// --- Pagination setup ---
$logs_per_page = 7;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $logs_per_page;

// --- Maximum default logs ---
$default_limit = 100;

// --- Count total logs (with filters) ---
$count_query = "SELECT COUNT(*) as total 
                FROM system_logs sl 
                LEFT JOIN admins a ON sl.account_id = a.id 
                $where_sql";
$count_result = $conn->query($count_query);
$total_logs = $count_result ? intval($count_result->fetch_assoc()['total']) : 0;

// Apply default limit if no filters
if (empty($where_clauses) && $total_logs > $default_limit) {
    $total_logs = $default_limit;
}
$total_pages = ceil($total_logs / $logs_per_page);

// --- Fetch logs ---
$query = "SELECT sl.*, a.username, a.fullname, a.role, a.image 
          FROM system_logs sl 
          LEFT JOIN admins a ON sl.account_id = a.id 
          $where_sql
          ORDER BY sl.created_at DESC";

// Apply default limit only if no filters
if (empty($where_clauses)) {
    $query .= " LIMIT $default_limit";
}

$result = $conn->query($query);
$all_logs = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Slice logs for current page
$logs = array_slice($all_logs, $offset, $logs_per_page);

// --- Function to get user image with fallback ---
function getUserImage($userData) {
    if (!empty($userData['image'])) {
        $imagePath = 'profile/' . $userData['image'];
        if (file_exists($imagePath)) {
            return $imagePath;
        }
    }
    if (!empty($userData['fullname'])) {
        return 'profile/admin_1.png';
    }
    return '../user/system.png';
}

// --- Fetch distinct roles for the dropdown ---
$roles_result = $conn->query("SELECT DISTINCT role FROM admins ORDER BY role ASC");
$roles = [];
if ($roles_result) {
    while ($row = $roles_result->fetch_assoc()) {
        $roles[] = $row['role'];
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <?php include "includes/link-css.php";?>
    <link rel="stylesheet" href="assets/css/admintoapprove.css">
    <style>
        /* Table fixed layout and column widths */
        #logs-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        #logs-table th, #logs-table td {
            padding: 10px; text-align: center; vertical-align: middle;
            overflow: hidden; text-overflow: ellipsis; white-space: normal;
        }
        #logs-table th:first-child, #logs-table td:first-child { width: 5%; min-width: 40px; }
        #logs-table th.account-column, #logs-table td.account-column { width: 15%; }
        #logs-table th.role-column, #logs-table td.role-column { width: 10%; }
        #logs-table th.action-column, #logs-table td.action-column { width: 30%; }
        #logs-table th.category-column, #logs-table td.category-column { width: 20%; }
        #logs-table th.date-column, #logs-table td.date-column { width: 20%; }
        .user-cell span { display: inline-block; max-width: 100%; overflow: hidden; word-break: break-word; }
.pagination { 
    margin-top: 20px; 
    text-align: center; 
    white-space: nowrap;
}

.pagination a {
    display: inline-block;
    padding: 6px 12px;
    margin: 0 3px;
    border: 1px solid #ccc;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
    font-size: 14px;
}

.pagination a:hover {
    background-color: #f0f0f0;
}

.pagination a.btn-primary {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff;
}

.pagination .dots {
    display: inline-block;
    margin: 0 5px;
    color: #888;
    font-size: 14px;
    pointer-events: none;
}

    </style>
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <button class="mobile-menu-toggle" id="menuToggle"><i class="fa-solid fa-bars"></i></button>
    <?php include "includes/sidebar.php";?>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Main Content -->
    <main class="main">
        <header class="header">
            <h1 class="header-dashboard">System Logs</h1>
            <div class="user-menu">
                <div class="theme-toggle" id="themeToggle" style="display:none;"><i class="fas fa-moon"></i></div>
                <?php include "includes/notification.php";?>
            </div>
            <?php include "includes/profile.php";?>
        </header>
        
        <section class="table-card fade-in">
            <div class="table-header">
                <h3 class="table-title">System Activity Logs</h3>
                <div class="table-actions">
                    <form method="GET" class="filter-form">
                            <!-- Add this hidden input to preserve current page -->
                            <input type="hidden" name="page" value="1">
                        <select name="role">
                            <option value="">All Roles</option>
                            <?php foreach ($roles as $role_option): ?>
                                <option value="<?= htmlspecialchars($role_option) ?>" <?= $filter_role === $role_option ? "selected" : "" ?>>
                                    <?= htmlspecialchars($role_option) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="category">
                            <option value="">All Categories</option>
                            <option value="admin_management" <?= $filter_category=="admin_management" ? "selected" : "" ?>>Admin Management</option>
                            <option value="orders" <?= $filter_category=="orders" ? "selected" : "" ?>>Orders</option>
                            <option value="inventory_management" <?= $filter_category=="inventory_management" ? "selected" : "" ?>>Inventory Management</option>
                        </select>

                        <input type="date" name="start_date" value="<?= htmlspecialchars($filter_start) ?>">
                        <input type="date" name="end_date" value="<?= htmlspecialchars($filter_end) ?>">

                        <!-- Styled Filter & Reset Buttons -->
                        <button type="submit" class="btn btn-outline">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <a href="logs.php" class="btn btn-outline">
                            <i class="fas fa-undo"></i>
                            <span>Reset</span>
                        </a>
                    </form>
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="logs-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="account-column">Account</th>
                            <th class="role-column">Role</th>
                            <th class="action-column">Action</th>
                            <th class="category-column">Category</th>
                            <th class="date-column">Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="6" style="text-align:center; padding:20px;">No logs found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $index => $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['id']) ?></td>
                                <td>
                                    <div class="user-cell">
                                        <img src="<?= getUserImage($log) ?>" width="50" height="50" alt="avatar" onerror="this.src='profile/admin_1.png'">
                                        <span><?= !empty($log['fullname']) ? htmlspecialchars($log['fullname']) : "System" ?></span>
                                    </div>
                                </td>
                                <td><?= !empty($log['role']) ? htmlspecialchars($log['role']) : 'System' ?></td>
                                <td><?= htmlspecialchars($log['content']) ?></td>
                                <td>
                                    <?php 
                                        if (!empty($log['is_from'])) {
                                            $module_name = ucwords(str_replace('_',' ',$log['is_from']));
                                            echo htmlspecialchars($module_name);
                                        } else echo '-';
                                    ?>
                                </td>
                                <td>
                                    <?php $date = new DateTime($log['created_at']); echo $date->format('M j, Y g:i A'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
<div class="pagination">
    <?php 
    // Build query parameters for pagination links
    $query_params = [];
    if (!empty($filter_role)) $query_params['role'] = $filter_role;
    if (!empty($filter_category)) $query_params['category'] = $filter_category;
    if (!empty($filter_start)) $query_params['start_date'] = $filter_start;
    if (!empty($filter_end)) $query_params['end_date'] = $filter_end;
    ?>
    
    <?php if ($page > 1): ?>
        <?php $query_params['page'] = $page - 1; ?>
        <a href="?<?= http_build_query($query_params) ?>" class="btn btn-outline">&laquo; Prev</a>
    <?php endif; ?>

    <!-- Always show first page -->
    <?php $query_params['page'] = 1; ?>
    <a href="?<?= http_build_query($query_params) ?>" class="btn <?= $page == 1 ? 'btn-primary' : 'btn-outline' ?>">1</a>

    <!-- Dots -->
    <?php if ($page > 3): ?>
        <span class="dots">...</span>
    <?php endif; ?>

    <!-- Pages around current -->
    <?php for ($i = max(2, $page - 2); $i <= min($total_pages - 1, $page + 2); $i++): ?>
        <?php $query_params['page'] = $i; ?>
        <a href="?<?= http_build_query($query_params) ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <!-- Dots -->
    <?php if ($page < $total_pages - 2): ?>
        <span class="dots">...</span>
    <?php endif; ?>

    <!-- Always show last page -->
    <?php if ($total_pages > 1): ?>
        <?php $query_params['page'] = $total_pages; ?>
        <a href="?<?= http_build_query($query_params) ?>" class="btn <?= $page == $total_pages ? 'btn-primary' : 'btn-outline' ?>">
            <?= $total_pages ?>
        </a>
    <?php endif; ?>

    <?php if ($page < $total_pages): ?>
        <?php $query_params['page'] = $page + 1; ?>
        <a href="?<?= http_build_query($query_params) ?>" class="btn btn-outline">Next &raquo;</a>
    <?php endif; ?>
</div>

        </section>
    </main>
</div>

<?php include "includes/script-src.php";?>
</body>
</html>
