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
    // Secretary handled below
    $currentPage = basename($_SERVER['PHP_SELF'], ".php");
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
            <!-- Secretary Menu (Dashboard, Orders, Inventory, Admin) -->
            <a href="dashboard" class="nav-link" data-page="dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="to-pick-up-orders" class="nav-link<?php echo (basename($_SERVER['PHP_SELF'], ".php") === 'to-pick-up-orders') ? ' active' : ''; ?>" data-page="to-pick-up-orders">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
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
<!-- Confirmation Modal -->
<div id="logoutConfirmation" class="confirmation-modal">
  <div class="confirmation-modal-content">
    <div class="confirmation-modal-header">
      <h3>Confirm Logout</h3>
    </div>
    <div class="confirmation-modal-body">
    
      <p>Are you sure you want to logout?</p>
    </div>
    <div class="confirmation-modal-footer">
      <button class="confirmation-modal-btn cancel-btn">Cancel</button>
      <button class="confirmation-modal-btn confirm-btn" id="confirmLogoutAction">
        <span class="btn-text">Logout</span>
        <span class="confirmation-loading-spinner hidden">
          <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
            <path d="M12 3a9 9 0 1 0 9 9"></path>
          </svg>
        </span>
      </button>
    </div>
  </div>
</div>

<style>
  /* Confirmation Modal Styles */
  .confirmation-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  .confirmation-modal.active {
    display: flex;
    opacity: 1;
  }

  .confirmation-modal-content {
    background-color: var(--bg);
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-20px);
    transition: transform 0.3s ease;
    overflow: hidden;
    border: 1px solid var(--border);
  }

  .confirmation-modal.active .confirmation-modal-content {
    transform: translateY(0);
  }

  .confirmation-modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
  }

  .confirmation-modal-header h3 {
    margin: 0;
    color: var(--text);
    font-size: 1.2rem;
    font-weight: 600;
  }

  .confirmation-modal-body {
    padding: 24px 20px;
    text-align: center;
  }

  .confirmation-modal-icon {
    margin-bottom: 16px;
  }

  .confirmation-modal-body p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 1rem;
    line-height: 1.5;
  }

  .confirmation-modal-footer {
    display: flex;
    padding: 16px;
    border-top: 1px solid var(--border);
    justify-content: flex-end;
    gap: 12px;
  }

  .confirmation-modal-btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
  }

  .cancel-btn {
    background-color: var(--bg-secondary);
    color: var(--text-secondary);
  }

  .cancel-btn:hover {
    background-color: var(--border);
  }

  .confirm-btn {
    background-color: var(--danger);
    color: white;
    position: relative;
  }

  .confirm-btn:hover {
    background-color: #dc2626;
  }

  .confirmation-loading-spinner {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
  }

  .hidden {
    display: none;
  }

  .btn-text {
    transition: opacity 0.2s ease;
  }

  .confirm-btn.loading .btn-text {
    opacity: 0;
  }

  .confirm-btn.loading .confirmation-loading-spinner {
    display: block;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const logoutLink = document.querySelector('a[data-page="logout"]');
    const confirmationModal = document.getElementById('logoutConfirmation');
    const cancelBtn = confirmationModal.querySelector('.cancel-btn');
    const confirmBtn = document.getElementById('confirmLogoutAction');
    
    // Open modal when logout link is clicked
    logoutLink.addEventListener('click', function(e) {
      e.preventDefault();
      confirmationModal.classList.add('active');
    });
    
    // Close modal when cancel button is clicked
    cancelBtn.addEventListener('click', function() {
      confirmationModal.classList.remove('active');
    });
    
    // Handle logout confirmation
    confirmBtn.addEventListener('click', function() {
      // Show loading state
      this.classList.add('loading');
      
      // Simulate logout process (replace with actual logout API call)
      setTimeout(function() {
        // Hide loading state
        confirmBtn.classList.remove('loading');
        
        // Close modal
        confirmationModal.classList.remove('active');
        
        // Redirect to logout page (replace with your logout URL)
        window.location.href = 'logout';
      }, 1500);
    });
    
    // Close modal when clicking outside
    confirmationModal.addEventListener('click', function(e) {
      if (e.target === confirmationModal) {
        confirmationModal.classList.remove('active');
      }
    });
  });
</script>