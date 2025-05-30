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
                <div class="theme-toggle" id="themeToggle">
                <span style="margin-right:8px;">Dark Mode</span>
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
                    <div class="card-footer negative">
                        <i class="fas fa-arrow-down"></i>
                        <span>2.1% from last month</span>
                    </div>
                </div>
                
                <div class="card slide-in" style="animation-delay: 0.4s;">
                    <div class="card-header">
                        <span class="card-title">Bounce Rate</span>
                        <div class="card-icon danger">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="card-value">24.8%</div>
                    <div class="card-footer positive">
                        <i class="fas fa-arrow-down"></i>
                        <span>3.7% from last month</span>
                    </div>
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
            
            
            <!-- <section class="table-card fade-in">
                <div class="table-header">
                    <h3 class="table-title">Recent Orders</h3>
                    <div class="table-actions">
                        <button class="btn btn-outline">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <button class="btn btn-primary" id="addOrderBtn">
                            <i class="fas fa-plus"></i>
                            <span>Add Order</span>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#ORD-0001</td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm">JD</div>
                                        <span>Juan Dela Cruz</span>
                                    </div>
                                </td>
                                <td>May 15, 2023</td>
                                <td>₱12,245.00</td>
                                <td><span class="status status-success">Completed</span></td>
                                <td><button class="btn btn-outline">View</button></td>
                            </tr>
                            <tr>
                                <td>#ORD-0002</td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm">MS</div>
                                        <span>Maria Santos</span>
                                    </div>
                                </td>
                                <td>May 14, 2023</td>
                                <td>₱9,189.50</td>
                                <td><span class="status status-success">Completed</span></td>
                                <td><button class="btn btn-outline">View</button></td>
                            </tr>
                            <tr>
                                <td>#ORD-0003</td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm">RJ</div>
                                        <span>Reyes Javier</span>
                                    </div>
                                </td>
                                <td>May 14, 2023</td>
                                <td>₱16,320.75</td>
                                <td><span class="status status-warning">Processing</span></td>
                                <td><button class="btn btn-outline">View</button></td>
                            </tr>
                            <tr>
                                <td>#ORD-0004</td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm">AB</div>
                                        <span>Andrea Bautista</span>
                                    </div>
                                </td>
                                <td>May 13, 2023</td>
                                <td>₱7,145.99</td>
                                <td><span class="status status-danger">Cancelled</span></td>
                                <td><button class="btn btn-outline">View</button></td>
                            </tr>
                            <tr>
                                <td>#ORD-0005</td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm">LM</div>
                                        <span>Luis Manalo</span>
                                    </div>
                                </td>
                                <td>May 12, 2023</td>
                                <td>₱13,775.25</td>
                                <td><span class="status status-success">Completed</span></td>
                                <td><button class="btn btn-outline">View</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section> -->
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
<script>
    // Modal
     // Modal
     const orderModal = document.getElementById('orderModal');
         const addOrderBtn = document.getElementById('addOrderBtn');
         const closeModal = document.getElementById('closeModal');
         const cancelOrder = document.getElementById('cancelOrder');
         
         addOrderBtn.addEventListener('click', () => {
             orderModal.classList.add('active');
         });
         
         closeModal.addEventListener('click', () => {
             orderModal.classList.remove('active');
         });
         
         cancelOrder.addEventListener('click', () => {
             orderModal.classList.remove('active');
         });
         
         // Close modal when clicking outside
         orderModal.addEventListener('click', (e) => {
             if (e.target === orderModal) {
                 orderModal.classList.remove('active');
             }
         });
         
         // Save order
         const saveOrder = document.getElementById('saveOrder');
         const orderForm = document.getElementById('orderForm');
         
         saveOrder.addEventListener('click', (e) => {
             e.preventDefault();
             
             if (orderForm.checkValidity()) {
                 // In a real app, you would save the order here
                 // For demo purposes, we'll just show a toast
                 showToast('Order Added', 'The new order has been added successfully', 'success');
                 
                 // Close modal
                 orderModal.classList.remove('active');
                 
                 // Reset form
                 orderForm.reset();
             } else {
                 orderForm.reportValidity();
             }
         });
         
         // Toast Function
         function showToast(title, message, type = 'info') {
             const toastContainer = document.getElementById('toastContainer');
             
             // Create toast
             const toast = document.createElement('div');
             toast.className = `toast toast-${type}`;
             
             // Set toast content
             toast.innerHTML = `
                 <div class="toast-icon">
                     <i class="fas ${type === 'success' ? 'fa-check' : 
                                       type === 'error' ? 'fa-times' : 
                                       type === 'warning' ? 'fa-exclamation' : 
                                       'fa-info'}"></i>
                 </div>
                 <div class="toast-content">
                     <h4 class="toast-title">${title}</h4>
                     <p class="toast-message">${message}</p>
                 </div>
                 <button class="toast-close">&times;</button>
             `;
             
             // Add toast to container
             toastContainer.appendChild(toast);
             
             // Show toast
             setTimeout(() => {
                 toast.classList.add('show');
             }, 100);
             
             // Auto remove toast after 5 seconds
             setTimeout(() => {
                 toast.classList.remove('show');
                 setTimeout(() => {
                     toast.remove();
                 }, 300);
             }, 5000);
             
             // Close button
             const closeBtn = toast.querySelector('.toast-close');
             closeBtn.addEventListener('click', () => {
                 toast.classList.remove('show');
                 setTimeout(() => {
                     toast.remove();
                 }, 300);
             });
         }
         
         // Demo toasts on page load
         setTimeout(() => {
             showToast('Welcome Back!', 'You have 3 new notifications', 'info');
         }, 1000);
</script>


<?php include "includes/script-src.php";?>
</body>
</html>