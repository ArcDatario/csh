document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    
    tabButtons.forEach(button => {
        // Add click animation
        button.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        button.addEventListener('mouseup', function() {
            this.style.transform = 'scale(1)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        // Tab switching functionality
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.style.transform = 'scale(1)'; // Reset any transforms
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Hide all containers
            document.querySelectorAll('.quotes-container').forEach(container => {
                container.style.display = 'none';
            });
            
            // Show the selected container
            const tabId = this.getAttribute('data-tab');
            if (tabId && document.getElementById(tabId)) {
                document.getElementById(tabId).style.display = 'flex';
                
                // Smooth scroll to tabs if mobile (optional)
                if (window.innerWidth < 768) {
                    this.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                        inline: 'center'
                    });
                }
            }
        });
    });
});