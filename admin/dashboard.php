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

// Database connection - don't close this connection as it's needed for includes
require_once '../db_connection.php';

// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');
$lastMonth = date('m', strtotime('-1 month'));
$lastMonthYear = date('Y', strtotime('-1 month'));

// Function to format numbers with commas
function formatNumber($number) {
    return number_format($number, 0, '.', ',');
}

// Function to execute query safely
function executeQuery($conn, $query, $params = [], $types = "") {
    $stmt = $conn->prepare($query);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
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
$serviceColors = ['#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6'];

while ($row = $result->fetch_assoc()) {
    $serviceLabels[] = $row['print_type'];
    $serviceData[] = $row['count'];
}

$totalServices = array_sum($serviceData);
$servicePercentages = array_map(function($count) use ($totalServices) {
    return $totalServices > 0 ? round(($count / $totalServices) * 100) : 0;
}, $serviceData);

// Don't close the connection here as it's needed for includes
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <?php include "includes/link-css.php";?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
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
           
            <?php include "report-generation.php";?>

            <!-- Cards Grid -->
            <section class="cards-grid">
                <div class="card slide-in" style="animation-delay: 0.1s;">
                    <div class="card-header">
                        <span class="card-title">Total Revenue</span>
                        <div class="card-icon primary">
                            <i class="fas fa-peso-sign"></i>
                        </div>
                    </div>
                    <div class="card-value" id="totalRevenue">₱<?= formatNumber($currentRevenue) ?></div>
                    <div class="card-footer <?= $revenueTrendClass ?>">
                        <i class="fas <?= $revenueTrendIcon ?>"></i>
                        <span><?= $revenueChangeFormatted ?>% from last month</span>
                    </div>
                </div>
                
                <div class="card slide-in" style="animation-delay: 0.4s;">
                    <div class="card-header">
                        <span class="card-title">Expected Revenue</span>
                        <div class="card-icon danger">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="card-value" id="expectedRevenue">₱<?= formatNumber($expectedRevenue) ?></div>
                </div>
                
                <div class="card slide-in" style="animation-delay: 0.2s;">
                    <div class="card-header">
                        <span class="card-title">New Users</span>
                        <div class="card-icon success">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value" id="newUsers"><?= formatNumber($currentUsers) ?></div>
                    <div class="card-footer <?= $usersTrendClass ?>">
                        <i class="fas <?= $usersTrendIcon ?>"></i>
                        <span><?= $usersChangeFormatted ?>% from last month</span>
                    </div>
                </div>
                
                <div class="card slide-in" style="animation-delay: 0.3s;">
                    <div class="card-header">
                        <span class="card-title">Pending Orders</span>
                        <div class="card-icon warning">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="card-value" id="pendingOrders"><?= formatNumber($pendingOrders) ?></div>
                </div>
            </section>
            
            <!-- Charts -->
            <section class="charts-container">
                <div class="chart-card fade-in">
                    <div class="chart-header">
                        <h3 class="chart-title">Revenue Overview</h3>
                        <div class="chart-actions">
                            <button class="chart-btn" id="refreshRevenueChart">Refresh</button>                      
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
                            <button class="chart-btn" id="refreshServicesChart">Refresh</button>
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

    <script>
    // Initialize charts with animation
    let revenueChart, trafficChart;
    
    function initCharts() {
        // Revenue Chart (Bar Chart)
        const revenueCanvas = document.getElementById('revenueChart');
        if (revenueCanvas) {
            const revenueCtx = revenueCanvas.getContext('2d');
            revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Revenue',
                        data: <?= json_encode(array_values($monthlyRevenue)) ?>,
                        backgroundColor: '#6366f1',
                        borderColor: '#6366f1',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return `Revenue: ₱${context.parsed.y.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.1)'
                            },
                            ticks: {
                                color: '#0f172a',
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#0f172a'
                            }
                        }
                    }
                }
            });
        }
         
        // Services Chart (Doughnut Chart)
        const trafficCanvas = document.getElementById('trafficChart');
        if (trafficCanvas) {
            const trafficCtx = trafficCanvas.getContext('2d');
            trafficChart = new Chart(trafficCtx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($serviceLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($servicePercentages) ?>,
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: '#0f172a',
                                boxWidth: 12,
                                padding: 16,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw}%`;
                                }
                            }
                        },
                        datalabels: {
                            display: false
                        }
                    },
                    cutout: '70%'
                },
                plugins: [ChartDataLabels]
            });
        }
    }
    
    // Function to animate number changes
    function animateValue(id, start, end, prefix = '', suffix = '', duration = 1000) {
        const obj = document.getElementById(id);
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            obj.innerHTML = prefix + value.toLocaleString() + suffix;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
    
    // Function to fetch updated dashboard data
    function fetchDashboardData() {
        fetch('get_dashboard_data.php')
            .then(response => response.json())
            .then(data => {
                // Update cards with animation
                const currentRevenue = parseInt(document.getElementById('totalRevenue').textContent.replace(/[^0-9]/g, ''));
                if (currentRevenue !== data.currentRevenue) {
                    animateValue('totalRevenue', currentRevenue, data.currentRevenue, '₱');
                }
                
                const expectedRevenue = parseInt(document.getElementById('expectedRevenue').textContent.replace(/[^0-9]/g, ''));
                if (expectedRevenue !== data.expectedRevenue) {
                    animateValue('expectedRevenue', expectedRevenue, data.expectedRevenue, '₱');
                }
                
                const newUsers = parseInt(document.getElementById('newUsers').textContent.replace(/[^0-9]/g, ''));
                if (newUsers !== data.currentUsers) {
                    animateValue('newUsers', newUsers, data.currentUsers);
                }
                
                const pendingOrders = parseInt(document.getElementById('pendingOrders').textContent.replace(/[^0-9]/g, ''));
                if (pendingOrders !== data.pendingOrders) {
                    animateValue('pendingOrders', pendingOrders, data.pendingOrders);
                }
                
                // Update trend indicators
                document.querySelector('#totalRevenue + .card-footer').className = `card-footer ${data.revenueTrendClass}`;
                document.querySelector('#totalRevenue + .card-footer i').className = `fas ${data.revenueTrendIcon}`;
                document.querySelector('#totalRevenue + .card-footer span').textContent = `${data.revenueChangeFormatted}% from last month`;
                
                document.querySelector('#newUsers + .card-footer').className = `card-footer ${data.usersTrendClass}`;
                document.querySelector('#newUsers + .card-footer i').className = `fas ${data.usersTrendIcon}`;
                document.querySelector('#newUsers + .card-footer span').textContent = `${data.usersChangeFormatted}% from last month`;
                
                // Update charts
                revenueChart.data.datasets[0].data = data.monthlyRevenue;
                revenueChart.update();
                
                trafficChart.data.labels = data.serviceLabels;
                trafficChart.data.datasets[0].data = data.servicePercentages;
                trafficChart.update();
            })
            .catch(error => console.error('Error fetching dashboard data:', error));
    }
    
    // Initialize charts when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        
        // Set up periodic data refresh (every 30 seconds)
        setInterval(fetchDashboardData, 5000);
        
        // Manual refresh buttons
        document.getElementById('refreshRevenueChart').addEventListener('click', fetchDashboardData);
        document.getElementById('refreshServicesChart').addEventListener('click', fetchDashboardData);
    });
    
    // Make charts responsive on window resize
    window.addEventListener('resize', function() {
        if (revenueChart) {
            revenueChart.resize();
        }
        if (trafficChart) {
            trafficChart.resize();
        }
    });
    </script>

    <?php include "includes/script-src.php";?>
</body>
</html>