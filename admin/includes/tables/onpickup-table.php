<div id="on-pickup-table" class="table-responsive tab-content">
    <table id="onpickup-table">
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>Design</th>
                <th>Print Type</th>
                <th>Quantity</th>
                <th>Date</th>
                <th>Attempt</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="onpickup-table-body">
            <!-- Content will be loaded via JavaScript -->
        </tbody>
    </table>
</div>

<script>
// Function to fetch and update the table
function updateOnPickupTable() {
    fetch('api/get_onpickup_orders.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('onpickup-table-body').innerHTML = data;
            attachViewButtonListeners(); // Reattach event listeners after update
        })
        .catch(error => {
            console.error('Error fetching on-pickup orders:', error);
        });
}

// Function to attach event listeners to view buttons
function attachViewButtonListeners() {
    document.querySelectorAll('.view-on-pickup-modal').forEach(button => {
        button.addEventListener('click', handleOnPickupViewButtonClick);
    });
}

// View button click handler for on-pickup orders
function handleOnPickupViewButtonClick() {
    const id = this.getAttribute('data-id');
    const userId = this.getAttribute('data-user-id');
    const ticket = this.getAttribute('data-ticket');
    const name = this.getAttribute('data-name');
    const mobile = this.getAttribute('data-mobile');
    const address = this.getAttribute('data-address');
    const email = this.getAttribute('data-email');
    const attempt = this.closest('tr').querySelector('td:nth-child(6)').textContent.trim();
    
    // Store data in modal
    document.getElementById('onPickupModal').setAttribute('data-current-id', id);
    document.getElementById('onpickup-modal-id').value = id;
    document.getElementById('onpickup-modal-user-id').value = userId;
    document.getElementById('onpickup-modal-email').value = email;
    document.getElementById('onpickup-modal-ticket').value = ticket;
    document.getElementById('onpickup-modal-attempt').value = attempt;
    
    // Populate modal fields
    document.getElementById('onpickup-modal-ticket').textContent = ticket;
    document.getElementById('onpickup-modal-attempt').textContent = attempt;
    document.getElementById('onpickup-modal-name').textContent = name;
    document.getElementById('onpickup-modal-mobile').textContent = mobile || 'N/A';
    document.getElementById('onpickup-modal-address').textContent = address || 'N/A';
    document.getElementById('onpickup-modal-last-attempt').textContent = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Show modal
    document.getElementById('onPickupModal').style.display = 'block';
}

// Initial load and set interval for updates
document.addEventListener('DOMContentLoaded', function() {
    updateOnPickupTable();
    setInterval(updateOnPickupTable, 3000); // Update every 30 seconds
});
</script>