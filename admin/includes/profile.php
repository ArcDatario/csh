    <div class="profile-dropdown" id="profileDropdown">
  <div class="profile-trigger">
    <img src="profile/admin-icon.jpg" alt="Profile" class="profile-icon">
    <span class="profile-arrow">▼</span>
  </div>
  <div class="dropdown-menu">
    <a href="#" class="menu-item">
      <svg class="menu-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
      <span>Profile</span>
    </a>
    <div class="menu-divider"></div>
    <a href="#" class="menu-item logout">
      <svg class="menu-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
        <polyline points="16 17 21 12 16 7"></polyline>
        <line x1="21" y1="12" x2="9" y2="12"></line>
      </svg>
      <span>Logout</span>
    </a>
  </div>
</div>

<style>
  :root {
    --primary: #6366f1;
    --primary-hover: #4f46e5;
    --secondary: #f43f5e;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --dark: #1e293b;
    --light: #f8fafc;
    --gray: #94a3b8;
    --gray-dark: #64748b;
    --bg: #ffffff;
    --bg-secondary: #f1f5f9;
    --text: #0f172a;
    --text-secondary: #334155;
    --card-bg: #ffffff;
    --card-shadow: rgba(0, 0, 0, 0.1);
    --border: #e2e8f0;
  }

  .profile-dropdown {
    position: relative;
    display: inline-block;
    font-family: 'Inter', sans-serif;
  }

  .profile-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 9999px;
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: var(--bg-secondary);
    user-select: none;
  }

  .profile-trigger:hover {
    background-color: var(--border);
  }

  .profile-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--primary);
  }

  .profile-arrow {
    font-size: 10px;
    color: var(--text-secondary);
    transition: transform 0.2s ease;
  }

  .dropdown-menu {
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 8px;
    min-width: 200px;
    background-color: var(--card-bg);
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px var(--card-shadow), 0 2px 4px -1px var(--card-shadow);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    z-index: 100;
    overflow: hidden;
    border: 1px solid var(--border);
  }

  /* Desktop - Hover behavior */
  @media (hover: hover) and (pointer: fine) {
    .profile-dropdown:hover .dropdown-menu {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }
    .profile-dropdown:hover .profile-arrow {
      transform: rotate(180deg);
    }
  }

  /* Mobile - Active state (controlled by JS) */
  .profile-dropdown.active .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }
  .profile-dropdown.active .profile-arrow {
    transform: rotate(180deg);
  }

  .menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    color: var(--text);
    text-decoration: none;
    transition: all 0.2s ease;
  }

  .menu-item:hover {
    background-color: var(--bg-secondary);
    color: var(--primary-hover);
  }

  .menu-item:hover .menu-icon {
    color: var(--primary-hover);
  }

  .menu-icon {
    color: var(--text-secondary);
    transition: color 0.2s ease;
  }

  .menu-divider {
    height: 1px;
    background-color: var(--border);
    margin: 4px 0;
  }

  .logout {
    color: var(--danger);
  }

  .logout:hover {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--danger);
  }

  .logout .menu-icon {
    color: var(--danger);
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('profileDropdown');
    const trigger = dropdown.querySelector('.profile-trigger');
    
    // Click handler for mobile
    trigger.addEventListener('click', function(e) {
      // Check if we're on a touch device or small screen
      if (window.matchMedia('(hover: none) and (pointer: coarse)').matches || 
          window.innerWidth <= 768) {
        e.preventDefault();
        dropdown.classList.toggle('active');
      }
    });
    
    // Close when clicking outside
    document.addEventListener('click', function(e) {
      if (!dropdown.contains(e.target)) {
        dropdown.classList.remove('active');
      }
    });
    
    // Close when selecting a menu item
    const menuItems = dropdown.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
      item.addEventListener('click', function() {
        dropdown.classList.remove('active');
      });
    });
  });
</script>