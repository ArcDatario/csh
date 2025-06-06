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
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    

   <?php include "includes/link-css.php";?>

   
    
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
                <h1 class="header-dashboard">Dashboard</h1>
                
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
            
            <!-- Cards Grid -->
            <section class="cards-grid">
                <div class="card slide-in" style="animation-delay: 0.1s;">
                    <div class="card-header">
                        <span class="card-title">Total Revenue</span>
                        <div class="card-icon primary">
                            <i class="fas fa-peso-sign"></i>
                        </div>
                    </div>
                    <div class="card-value">₱24,780</div>
                    <div class="card-footer positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>12.5% from last month</span>
                    </div>
                </div>
                <div class="card slide-in" style="animation-delay: 0.4s;">
                    <div class="card-header">
                        <span class="card-title">Expected Revenue</span>
                        <div class="card-icon danger">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="card-value">24.8%</div>
                    
                </div>
                
                <div class="card slide-in" style="animation-delay: 0.2s;">
                    <div class="card-header">
                        <span class="card-title">New Users</span>
                        <div class="card-icon success">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value">1,245</div>
                    <div class="card-footer positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>8.3% from last month</span>
                    </div>
                </div>
                
                <div class="card slide-in" style="animation-delay: 0.3s;">
                    <div class="card-header">
                        <span class="card-title">Pending Orders</span>
                        <div class="card-icon warning">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="card-value">56</div>
                   
                </div>
                
                
            </section>
            
            <!-- Charts -->
            <section class="charts-container">
                <div class="chart-card fade-in">
                    <div class="chart-header">
                        <h3 class="chart-title">Revenue Overview</h3>
                        <div class="chart-actions">
                            <button class="chart-btn active">Week</button>
                            <button class="chart-btn">Month</button>
                            <button class="chart-btn">Year</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card fade-in">
                    <div class="chart-header">
                        <h3 class="chart-title">Best Services</h3>
                        <div class="chart-actions">
                            <button class="chart-btn active">Week</button>
                            <button class="chart-btn">Month</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="trafficChart"></canvas>
                    </div>
                </div>
            </section>
            
        </main>
    </div>
    
    <!-- Modal -->
    <div class="modal" id="orderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Order</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="orderForm">
                    <div class="form-group">
                        <label for="customerName" class="form-label">Customer Name</label>
                        <input type="text" id="customerName" class="form-control" placeholder="Enter customer name" required>
                    </div>
                    <div class="form-group">
                        <label for="orderDate" class="form-label">Order Date</label>
                        <input type="date" id="orderDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="orderAmount" class="form-label">Amount</label>
                        <input type="number" id="orderAmount" class="form-control" placeholder="Enter amount" required>
                    </div>
                    <div class="form-group">
                        <label for="orderStatus" class="form-label">Status</label>
                        <select id="orderStatus" class="form-control" required>
                            <option value="">Select status</option>
                            <option value="Completed">Completed</option>
                            <option value="Processing">Processing</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelOrder">Cancel</button>
                <button class="btn btn-primary" id="saveOrder">Save Order</button>
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

   

<script src="assets/js/charts.js"></script>



<?php include "includes/script-src.php";?>
</body>
</html>