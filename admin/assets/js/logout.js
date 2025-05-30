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