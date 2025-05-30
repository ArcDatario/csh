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