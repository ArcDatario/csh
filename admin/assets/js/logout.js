document.addEventListener('DOMContentLoaded', function() {
    // Get all logout links (both in profile dropdown and navigation)
    const logoutLinks = document.querySelectorAll('.logout, a[data-page="logout"]');
    const confirmationModal = document.getElementById('logoutConfirmation');
    const cancelBtn = confirmationModal.querySelector('.cancel-btn');
    const confirmBtn = document.getElementById('confirmLogoutAction');
    
    // Add click event to all logout links
    logoutLinks.forEach(logoutLink => {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            confirmationModal.classList.add('active');
        });
    });
    
    // Close modal when cancel button is clicked
    cancelBtn.addEventListener('click', function() {
        confirmationModal.classList.remove('active');
    });
    
    // Handle logout confirmation
    confirmBtn.addEventListener('click', function() {
        // Show loading state
        this.classList.add('loading');
        this.textContent = 'Logging out...';
        
        // Perform actual logout (replace with your logout endpoint)
        fetch('logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        })
        .then(response => {
            if (response.ok) {
                // Redirect to login page after successful logout
                window.location.href = 'login';
            } else {
                throw new Error('Logout failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Reset button state
            confirmBtn.classList.remove('loading');
            confirmBtn.textContent = 'Logout';
            // Show error message (you can add this to your modal)
            alert('Logout failed. Please try again.');
        });
    });
    
    // Close modal when clicking outside
    confirmationModal.addEventListener('click', function(e) {
        if (e.target === confirmationModal) {
            confirmationModal.classList.remove('active');
        }
    });
});