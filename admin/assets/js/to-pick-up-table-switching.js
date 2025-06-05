document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    // Function to switch tabs
    function switchTab(tabId) {
        // Remove active class from all buttons and content
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        // Add active class to clicked button
        const activeButton = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }
        
        // Show corresponding content and initialize appropriate table
        if (tabId === 'to-pickup') {
            document.getElementById('to-pickup-table').classList.add('active');
            // Initialize to-pickup table if needed
        } else if (tabId === 'on-pickup') {
            document.getElementById('on-pickup-table').classList.add('active');
            updateOnPickupTable();
        } else if (tabId === 'to-ship') {
            document.getElementById('to-ship-table').classList.add('active');
            updateToShipTable();
        }
        
        // Save to localStorage
        localStorage.setItem('activeTab', tabId);
    }
    
    // Set up click handlers
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
        });
    });
    
    // Check saved tab or default to 'to-pickup'
    const savedTab = localStorage.getItem('activeTab');
    if (savedTab && (savedTab === 'to-pickup' || savedTab === 'on-pickup' || savedTab === 'to-ship')) {
        switchTab(savedTab);
    } else {
        switchTab('to-pickup');
    }
});