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





