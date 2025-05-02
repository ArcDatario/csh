<aside class="sidebar" id="sidebar">
    <div class="logo">
        <i class="fa-solid fa-chart-simple"></i>
        <h2>CSH</h2>
    </div>
    <div class="nav-links">
        <?php if ($_SESSION['admin_role'] !== 'Field Manager' && $_SESSION['admin_role'] !== 'Designer' && $_SESSION['admin_role'] !== 'Secretary'): ?>
            <!-- Full menu for other roles -->
            <a href="dashboard" class="nav-link" data-page="dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
          
<?php
    // Determine the correct link and data-page based on role
    $ordersLink = "orders";
    $ordersPage = "orders";
    if ($_SESSION['admin_role'] === 'Field Manager') {
        $ordersLink = "orders";
        $ordersPage = "orders";
    } elseif ($_SESSION['admin_role'] === 'General Manager' || $_SESSION['admin_role'] === 'Owner') {
        $ordersLink = "admintoapprove";
        $ordersPage = "admintoapprove";
    } elseif ($_SESSION['admin_role'] === 'Designer') {
        $ordersLink = "orders";
        $ordersPage = "orders";
    }


    // You may need to adjust how you get the current page, e.g., from $_GET or a routing variable
    $currentPage = basename($_SERVER['PHP_SELF'], ".php"); // e.g., "orders" or "admintoapprove"
    $isActive = ($currentPage === $ordersPage) ? 'active' : '';
?>
<a href="<?php echo $ordersLink; ?>" class="nav-link <?php echo $isActive; ?>" data-page="<?php echo $ordersPage; ?>">
    <i class="fas fa-shopping-cart"></i>
    <span>Orders</span>
</a>

            <a href="inventory" class="nav-link" data-page="inventory">
                <i class="fas fa-box"></i>
                <span>Inventory</span>
            </a>
            <a href="customers" class="nav-link" data-page="customers">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
            <a href="admin" class="nav-link" data-page="admin">
                <i class="fas fa-user-shield"></i>
                <span>Admin</span>
            </a>
            
            <div class="nav-section">
                <h3 class="nav-section-title">Pages</h3>
            </div>
            
            <a href="services" class="nav-link" data-page="services">
                <i class="fas fa-concierge-bell"></i>
                <span>Services</span>
            </a>
            <a href="our-work" class="nav-link" data-page="our-work">
                <i class="fas fa-briefcase"></i>
                <span>Our Work</span>
            </a>
            
        <?php elseif ($_SESSION['admin_role'] === 'Field Manager'): ?>
            <!-- Field Manager Menu (only Inventory) -->
            <a href="inventory" class="nav-link" data-page="inventory">
                <i class="fas fa-box"></i>
                <span>Inventory</span>
            </a>
            
        <?php elseif ($_SESSION['admin_role'] === 'Designer'): ?>
            <!-- Designer Menu (only Orders) -->
            <a href="orders" class="nav-link" data-page="orders">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
            
        <?php elseif ($_SESSION['admin_role'] === 'Secretary'): ?>
            <!-- Secretary Menu (Dashboard, Inventory, Admin) -->
            <a href="dashboard" class="nav-link" data-page="dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="inventory" class="nav-link" data-page="inventory">
                <i class="fas fa-box"></i>
                <span>Inventory</span>
            </a>
            <a href="admin" class="nav-link" data-page="admin">
                <i class="fas fa-user-shield"></i>
                <span>Admin</span>
            </a>
        <?php endif; ?>
        
        <!-- Logout (shown to all roles) -->
        <div class="nav-section">
            <h3 class="nav-section-title">Logout</h3>
        </div>
        <a href="logout" class="nav-link" data-page="logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>