document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    // Define titles for each tab
    const tabTitles = {
        'to-pickup': 'To Pickup Orders',
        'on-pickup': 'On Pickup Orders',
        'to-ship': 'To Ship Orders',
        'completed': 'Completed Orders',
        'cancel': 'Cancelled Orders'
    };
    
    // Function to update the title and badge based on active tab
    function updateTitleAndBadge(tabId) {
        const titleElement = document.getElementById('table-title');
        if (titleElement && tabCounts[tabId] !== undefined) {
            titleElement.innerHTML = tabTitles[tabId] + ' <span class="badge">' + tabCounts[tabId] + '</span>';
        }
    }
    
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
        
        // Update the title with the preloaded count
        updateTitleAndBadge(tabId);
        
        // Show corresponding content and initialize appropriate table
        if (tabId === 'to-pickup') {
            document.getElementById('to-pickup-table').classList.add('active');
        } else if (tabId === 'on-pickup') {
            document.getElementById('on-pickup-table').classList.add('active');
            if (typeof updateOnPickupTable === 'function') {
                updateOnPickupTable();
            }
        } else if (tabId === 'to-ship') {
            document.getElementById('to-ship-table').classList.add('active');
            if (typeof updateToShipTable === 'function') {
                updateToShipTable();
            }
        } else if (tabId === 'completed') {
            document.getElementById('completed-table').classList.add('active');
            if (typeof updateCompletedTable === 'function') {
                updateCompletedTable();
            }
        } else if (tabId === 'cancel') {
            document.getElementById('cancelled-table').classList.add('active');
            if (typeof updateCancelledTable === 'function') {
                updateCancelledTable();
            }
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
    if (savedTab && (savedTab === 'to-pickup' || savedTab === 'on-pickup' || savedTab === 'to-ship' || savedTab === 'completed' || savedTab === 'cancel')) {
        switchTab(savedTab);
    } else {
        switchTab('to-pickup');
    }
});