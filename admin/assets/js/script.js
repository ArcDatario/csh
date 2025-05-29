document.addEventListener('DOMContentLoaded', function() {
    // Get current page from URL (works with .php, .html, or no extension)
    const path = window.location.pathname;
    let currentPage = path.split('/').pop();
    
    // Remove file extension if present
    currentPage = currentPage.replace(/\.(php|html)$/, '');
    
    // Handle index/home page cases
    if (currentPage === '' || currentPage === 'index') {
        currentPage = 'dashboard';
    }
    
    // Remove active class from all links first
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Find matching nav link and set active
    const activeLink = document.querySelector(`[data-page="${currentPage}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
        
        // Store in localStorage for persistence (optional)
        localStorage.setItem('lastActivePage', currentPage);
    }
    // Fallback to localStorage if direct URL match fails
    else {
        const lastActive = localStorage.getItem('lastActivePage');
        if (lastActive) {
            const fallbackLink = document.querySelector(`[data-page="${lastActive}"]`);
            if (fallbackLink) {
                fallbackLink.classList.add('active');
            }
        }
    }
});





 // Theme Toggle
 const themeToggle = document.getElementById('themeToggle');
 const html = document.documentElement;
 
 // Check for saved theme preference (default to light if none)
 const savedTheme = localStorage.getItem('theme') || 'light';
 html.setAttribute('data-theme', savedTheme);
 
 // Initialize the toggle button state
 const icon = themeToggle.querySelector('i');
 const text = themeToggle.querySelector('span');
 icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
 text.textContent = savedTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
 
 // Initialize charts with saved theme (ADDED THIS CRUCIAL LINE)
 document.addEventListener('DOMContentLoaded', () => {
     updateChartsForTheme(savedTheme);
 });
 
 themeToggle.addEventListener('click', () => {
     const currentTheme = html.getAttribute('data-theme');
     const newTheme = currentTheme === 'light' ? 'dark' : 'light';
     
     // Update theme
     html.setAttribute('data-theme', newTheme);
     localStorage.setItem('theme', newTheme);
     
     // Update UI
     icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
     text.textContent = newTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
     
     // Show toast
     showToast(`${newTheme === 'dark' ? 'Dark' : 'Light'} mode activated`, 
             `Switched to ${newTheme} mode`, 
             newTheme === 'dark' ? 'success' : 'info');
     
     // Update charts
     updateChartsForTheme(newTheme);
 });
 
 // Chart update function (unchanged but included for completeness)
 function updateChartsForTheme(theme) {
     const textColor = theme === 'dark' ? '#f8fafc' : '#0f172a';
     const gridColor = theme === 'dark' ? 'rgba(148, 163, 184, 0.1)' : 'rgba(148, 163, 184, 0.1)';
 
     if (revenueChart) {
         revenueChart.options.scales.x.grid.color = gridColor;
         revenueChart.options.scales.y.grid.color = gridColor;
         revenueChart.options.scales.x.ticks.color = textColor;
         revenueChart.options.scales.y.ticks.color = textColor;
         revenueChart.update();
     }
 
     if (trafficChart) {
         trafficChart.options.plugins.legend.labels.color = textColor;
         trafficChart.update();
     }
 }
         
      
         // Card hover animations
         const cards = document.querySelectorAll('.card');
         cards.forEach(card => {
             card.addEventListener('mouseenter', () => {
                 card.style.transform = 'translateY(-5px)';
                 card.style.boxShadow = '0 10px 15px var(--card-shadow)';
             });
             
             card.addEventListener('mouseleave', () => {
                 card.style.transform = '';
                 card.style.boxShadow = '';
             });
         });


 // this js is for menu toggle
 
 const menuToggle = document.getElementById('menuToggle'); // The button
 const sidebar = document.getElementById('sidebar'); // The sidebar
 const sidebarOverlay = document.getElementById('sidebarOverlay'); // The overlay

 // Toggle sidebar and overlay visibility
 menuToggle.addEventListener('click', () => {
     sidebar.classList.toggle('active'); // Add/remove the 'active' class to show/hide the sidebar
     sidebarOverlay.classList.toggle('active'); // Add/remove the 'active' class to show/hide the overlay

     // Change the icon between bars and times
     const icon = menuToggle.querySelector('i');
     if (sidebar.classList.contains('active')) {
         icon.classList.remove('fa-bars');
         icon.classList.add('fa-times');
     } else {
         icon.classList.remove('fa-times');
         icon.classList.add('fa-bars');
     }
 });

 // Close sidebar when clicking the overlay
 sidebarOverlay.addEventListener('click', () => {
     sidebar.classList.remove('active'); // Hide the sidebar
     sidebarOverlay.classList.remove('active'); // Hide the overlay

     // Reset the icon to bars
     const icon = menuToggle.querySelector('i');
     icon.classList.remove('fa-times');
     icon.classList.add('fa-bars');
 });

 // Ensure sidebar is visible on desktop resize
 window.addEventListener('resize', () => {
     if (window.innerWidth >= 768) {
         sidebar.classList.remove('active'); // Ensure the sidebar is always visible on desktop
         sidebarOverlay.classList.remove('active'); // Hide the overlay on desktop
     }
 });



 //thi js is for the notification

 const notificationBell = document.querySelector('.notification-bell');
 const notificationDropdown = document.querySelector('.notification-dropdown');
 const notificationBadge = document.querySelector('.notification-badge');
 const markAllReadBtn = document.querySelector('.mark-all-read');
 const notificationItems = document.querySelectorAll('.notification-item');
 
 // Toggle dropdown
 notificationBell.addEventListener('click', function(e) {
     e.stopPropagation(); // Prevent immediate closing
     notificationDropdown.classList.toggle('active');
 });
 
 // Mark all as read
 markAllReadBtn.addEventListener('click', function() {
     notificationItems.forEach(item => {
         item.classList.remove('unread');
     });
     notificationBadge.textContent = '0';
     showToast('Notifications marked as read', 'All notifications have been marked as read', 'success');
 });
 
 // Close dropdown when clicking outside
 document.addEventListener('click', function(e) {
     if (!notificationBell.contains(e.target)) {
         notificationDropdown.classList.remove('active');
     }
 });
 
 // Mark as read when clicking individual notifications
 notificationItems.forEach(item => {
     item.addEventListener('click', function() {
         if (this.classList.contains('unread')) {
             this.classList.remove('unread');
             // Update badge count
             const unreadCount = document.querySelectorAll('.notification-item.unread').length;
             notificationBadge.textContent = unreadCount;
         }
     });
 });





