document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');

    // Map tab IDs to their corresponding search input containers
    const searchMap = {
        'pending-orders-container': '.pending-search',
        'approved-orders-container': '.approved-search',
        'pickup-orders-container': '.topickup-search',
        'ship-orders-container': '.toship-search',
        'completed-orders-container': '.completed-search'
    };

    // Function to activate a tab and show the correct search input
    function activateTab(tabId) {
        // Remove active class from all buttons
        tabButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.style.transform = 'scale(1)';
        });

        // Hide all containers
        document.querySelectorAll('.quotes-container').forEach(container => {
            container.style.display = 'none';
        });

        // Hide all search input containers
        Object.values(searchMap).forEach(selector => {
            const el = document.querySelector(selector);
            if (el) el.style.display = 'none';
        });

        // Activate the selected tab
        const activeButton = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
        const activeContainer = document.getElementById(tabId);

        if (activeButton && activeContainer) {
            activeButton.classList.add('active');
            activeContainer.style.display = 'block';

            // Show the corresponding search input
            const searchSelector = searchMap[tabId];
            if (searchSelector) {
                const searchEl = document.querySelector(searchSelector);
                if (searchEl) searchEl.style.display = 'block';
            }

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