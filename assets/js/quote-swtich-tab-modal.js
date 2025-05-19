document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    
    // Function to activate a tab
    function activateTab(tabId) {
        // Remove active class from all buttons
        tabButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.style.transform = 'scale(1)'; // Reset any transforms
        });
        
        // Hide all containers
        document.querySelectorAll('.quotes-container').forEach(container => {
            container.style.display = 'none';
        });
        
        // Activate the selected tab
        const activeButton = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
        const activeContainer = document.getElementById(tabId);
        
        if (activeButton && activeContainer) {
            activeButton.classList.add('active');
            activeContainer.style.display = 'flex';
            
            // Save to localStorage
            localStorage.setItem('activeTab', tabId);
            
            // Smooth scroll to tabs if mobile (optional)
            if (window.innerWidth < 768) {
                activeButton.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            }
        }
    }
    
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
            const tabId = this.getAttribute('data-tab');
            if (tabId) {
                activateTab(tabId);
            }
        });
    });
    
    // Check for saved tab on page load
    const savedTab = localStorage.getItem('activeTab');
    if (savedTab) {
        activateTab(savedTab);
    } else if (tabButtons.length > 0) {
        // Activate the first tab by default if none is saved
        const firstTabId = tabButtons[0].getAttribute('data-tab');
        if (firstTabId) {
            activateTab(firstTabId);
        }
    }
});