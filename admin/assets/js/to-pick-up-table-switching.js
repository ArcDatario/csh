document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');

    // ============================
    // Tab Titles
    // ============================
    const tabTitles = {
        'to-pickup': 'To Pickup Orders',
        'on-pickup': 'On Pickup Orders',
        'to-ship': 'To Ship Orders',
        'completed': 'Completed Orders',
        'cancelled': 'Cancelled Orders'
    };

    // ============================
    // Update Title and Badge
    // ============================
    function updateTitleAndBadge(tabId) {
        const titleElement = document.getElementById('table-title');
        if (titleElement && tabCounts[tabId] !== undefined) {
            titleElement.innerHTML = tabTitles[tabId] + ' <span class="badge">' + tabCounts[tabId] + '</span>';
        }
    }

    // ============================
    // Switch Tab
    // ============================
function switchTab(tabId) {
    // 1. Reset all filter forms visually
    document.querySelectorAll('form.filter-form').forEach(form => form.reset());

    // 2. Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    // 3. Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
        content.style.display = 'none';
    });

    // 4. Activate clicked tab
    const activeButton = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
    if (activeButton) activeButton.classList.add('active');

    const activeContent = document.getElementById(`${tabId}-table`);
    if (activeContent) {
        activeContent.classList.add('active');
        activeContent.style.display = 'block';
    }

    // ✅ 4.5 Update the table title and badge
    updateTitleAndBadge(tabId);

    // 5. Reset active filters for this tab
    if (tabId === 'to-pickup') activeFiltersPickup = null;
    else if (tabId === 'on-pickup') activeFilters = null;
    else if (tabId === 'to-ship') activeFiltersToShip = null;
    else if (tabId === 'completed') activeFiltersCompleted = null;
    else if (tabId === 'cancelled') activeFiltersCancelled = null;


    // 6. Update browser URL (optional)
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);

    // Clear any old page parameters
    ['page', 'page_onpickup', 'page_pickup', 'page_to_ship', 'page_completed', 'page_cancelled']
        .forEach(p => url.searchParams.delete(p));

    // Reset current page = 1
    if (tabId === 'to-pickup') url.searchParams.set('page_pickup', 1);
    if (tabId === 'on-pickup') url.searchParams.set('page_onpickup', 1);
    if (tabId === 'to-ship') url.searchParams.set('page_to_ship', 1);
    if (tabId === 'completed') url.searchParams.set('page_completed', 1);
    if (tabId === 'cancelled') url.searchParams.set('page_cancelled', 1);

    window.history.replaceState({}, '', url);

    // 7. Save active tab
    localStorage.setItem('activeTab', tabId);

    // 8. Reload table data fresh
    if (tabId === 'cancelled' && typeof updateCancelledTable === 'function') updateCancelledTable();
    if (tabId === 'to-pickup' && typeof updatePickupTable === 'function') updatePickupTable();
    if (tabId === 'on-pickup' && typeof updateOnPickupTable === 'function') updateOnPickupTable();
    if (tabId === 'to-ship' && typeof updateToShipTable === 'function') updateToShipTable();
    if (tabId === 'completed' && typeof updateCompletedTable === 'function') updateCompletedTable();
    if (tabId === 'cancelled' && typeof updateCancelledTable === 'function') updateCancelledTable();

}


    // ============================
    // Set up click handlers
    // ============================
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
        });
    });

    // ============================
    // Check saved tab or default
    // ============================
    const savedTab = localStorage.getItem('activeTab');
    if (savedTab && ['to-pickup','on-pickup','to-ship','completed','cancelled'].includes(savedTab)) {
        switchTab(savedTab);
    } else {
        switchTab('to-pickup');
    }
});
