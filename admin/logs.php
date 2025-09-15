<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Safely check for Field Manager role and redirect
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

// Fetch logs from database with user image information
// Pagination setup
$logs_per_page = 7;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $logs_per_page;

// Count total logs for pagination
$count_query = "SELECT COUNT(*) as total FROM system_logs";
$count_result = $conn->query($count_query);
$total_logs = $count_result ? intval($count_result->fetch_assoc()['total']) : 0;
$total_pages = ceil($total_logs / $logs_per_page);

// Fetch paginated logs
$logs = [];
$query = "SELECT sl.*, a.username, a.fullname, a.role, a.image 
          FROM system_logs sl 
          LEFT JOIN admins a ON sl.account_id = a.id 
          ORDER BY sl.created_at DESC 
          LIMIT $offset, $logs_per_page";
$result = $conn->query($query);
if ($result) {
    $logs = $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get user image with fallback
function getUserImage($userData) {
    // First try the user's actual profile picture
    if (!empty($userData['image'])) {
        $imagePath = 'profile/' . $userData['image'];
        if (file_exists($imagePath)) {
            return $imagePath;
        }
    }
    
    
    // Default fallback for users
    if (!empty($userData['fullname'])) {
        return 'profile/admin_1.png';
    }
    
    // Fallback for system actions
    return '../user/system.png';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <?php include "includes/link-css.php";?>
    <link rel="stylesheet" href="assets/css/admintoapprove.css">

       <style>
/* Table fixed layout and column widths */
#logs-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* important to respect widths */
}

#logs-table th,
#logs-table td {
    padding: 10px;
    text-align: center;      /* center all contents */
    vertical-align: middle;  /* vertically center contents */
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: normal;     /* allow wrapping and dynamic height */
}

/* Add width for the index column */
#logs-table th:first-child,
#logs-table td:first-child {
    width: 5%;      
    min-width: 40px;
}

/* Assign column widths */
#logs-table th.account-column,
#logs-table td.account-column {
    width: 15%;
}

#logs-table th.role-column,
#logs-table td.role-column {
    width: 10%;
}

#logs-table th.action-column,
#logs-table td.action-column {
    width: 30%;
}

#logs-table th.category-column,
#logs-table td.category-column {
    width: 20%;
}

#logs-table th.date-column,
#logs-table td.date-column {
    width: 20%;
}

/* Optional: wrap long account names with ellipsis */
.user-cell span {
    display: inline-block;
    max-width: 100%;
    vertical-align: middle;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: normal; /* allow wrapping */
    word-break: break-word; /* break long words */
}

.pagination {
    margin-top: 20px;
    text-align: center;
}

.pagination a {
    display: inline-block;
    padding: 6px 12px;
    margin: 0 3px;
    border: 1px solid #ccc;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
}

.pagination a.active {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff;
}

    </style>
    
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <button class="mobile-menu-toggle" id="menuToggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <?php include "includes/sidebar.php";?>
    
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Main Content -->
        <main class="main">
            <header class="header">
                <h1 class="header-dashboard">System Logs</h1>
                
                <div class="user-menu">
                <div class="theme-toggle" id="themeToggle" style="display:none;">
                <span style="margin-right:8px;" style="display:none;">Dark Mode</span>
                <i class="fas fa-moon"></i>
            </div>
                    
          <?php include "includes/notification.php";?>

    </div>
                    
                   <?php include "includes/profile.php";?>
                </div>
            </header>
            
            <section class="table-card fade-in">
                <div class="table-header">
                    <h3 class="table-title">System Activity Logs</h3>
                    <div class="table-actions">
                        <button class="btn btn-outline" id="refreshLogs">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="logs-table" class="">
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
                        <tbody id="logs-table-body">
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px;">
                                        No logs found in the system.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $index => $log): ?>
                                <tr>
                                    <td><?php echo $offset + $index + 1; ?></td>
                                    <td>
                                        <div class="user-cell">
                                            <img src="<?php echo getUserImage($log); ?>" width="50" height="50" alt="avatar" onerror="this.src='profile/admin_1.png'">
                                            <span>
                                                <?php if (!empty($log['fullname'])): ?>
                                                    <?php echo htmlspecialchars($log['fullname']); ?>
                                                <?php else: ?>
                                                    System
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td><?php echo !empty($log['role']) ? htmlspecialchars($log['role']) : 'System'; ?></td>
                                    <td ><?php echo htmlspecialchars($log['content']); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($log['is_from'])) {
                                            // Convert 'inventory_management' => 'Inventory Management'
                                            $module_name = str_replace('_', ' ', $log['is_from']);
                                            $module_name = ucwords($module_name); 
                                            echo htmlspecialchars($module_name);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $date = new DateTime($log['created_at']);
                                        echo $date->format('M j, Y g:i A');
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" style="text-align:center; margin-top: 20px;">
                    <?php if ($total_pages > 1): ?>
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <a href="?page=<?php echo $p; ?>" 
                            class="pagination-link <?php echo $p == $page ? 'active' : ''; ?>"
                            style="margin:0 5px; padding:5px 10px; text-decoration:none; border:1px solid #ccc; border-radius:5px; <?php echo $p == $page ? 'background:#007BFF; color:white;' : ''; ?>">
                            <?php echo $p; ?>
                            </a>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>

            </section>
        </main>
    </div>

    <script>
    // Refresh logs functionality
    document.getElementById('refreshLogs').addEventListener('click', function() {
        location.reload();
    });
    

    </script>

    <?php include "includes/script-src.php";?>
</body>
</html>