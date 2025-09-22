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
        <div class="pagination">
    <?php 
    // Build base URL with all parameters except page
    $base_url = "?tab=" . urlencode($active_tab);
    if (!empty($filter_print)) $base_url .= "&print_type=" . urlencode($filter_print);
    if (!empty($filter_start)) $base_url .= "&start_date=" . urlencode($filter_start);
    if (!empty($filter_end)) $base_url .= "&end_date=" . urlencode($filter_end);
    if (!empty($filter_search)) $base_url .= "&search=" . urlencode($filter_search);
    ?>
    
    <?php if ($page > 1): ?>
        <a href="<?= $base_url ?>&page=<?= $page - 1 ?>" class="btn btn-outline">&laquo; Prev</a>
    <?php endif; ?>

    <!-- Always show first page -->
    <a href="<?= $base_url ?>&page=1" class="btn <?= $page == 1 ? 'btn-primary' : 'btn-outline' ?>">1</a>

    <!-- Dots -->
    <?php if ($page > 3): ?>
        <span class="dots">...</span>
    <?php endif; ?>

    <!-- Pages around current -->
    <?php for ($i = max(2, $page - 2); $i <= min($total_pages - 1, $page + 2); $i++): ?>
        <a href="<?= $base_url ?>&page=<?= $i ?>" class="btn <?= $i == $page ? 'btn-primary' : 'btn-outline' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <!-- Dots -->
    <?php if ($page < $total_pages - 2): ?>
        <span class="dots">...</span>
    <?php endif; ?>

    <!-- Always show last page -->
    <?php if ($total_pages > 1): ?>
        <a href="<?= $base_url ?>&page=<?= $total_pages ?>" class="btn <?= $page == $total_pages ? 'btn-primary' : 'btn-outline' ?>">
            <?= $total_pages ?>
        </a>
    <?php endif; ?>

    <?php if ($page < $total_pages): ?>
        <a href="<?= $base_url ?>&page=<?= $page + 1 ?>" class="btn btn-outline">Next &raquo;</a>
    <?php endif; ?>
</div>
</div>

<script>
// Function to fetch and update the table
function updateOnPickupTable() {
         // Get all filter values from the form
        const printType = document.querySelector('select[name="print_type"]').value;
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        const search = document.querySelector('input[name="search"]').value;
        const params = new URLSearchParams(new FormData(document.querySelector('.filter-form')));
        fetch('api/get_onpickup_orders.php?' + params.toString())
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

    // Populate shirt colors & quantities
    const shirtItemsContainer = document.getElementById('onpickup-modal-shirt-items');
    shirtItemsContainer.innerHTML = ''; // Clear previous content

    const itemsData = this.getAttribute('data-items');
    if (itemsData) {
        try {
            const shirtItems = JSON.parse(itemsData);
            if (shirtItems.length > 0) {
                shirtItems.forEach(item => {
        const div = document.createElement("div");
        div.classList.add("shirt-item");
        div.innerHTML = `
          <span class="shirt-color">${item.shirt_color}</span>
          <span class="shirt-qty">${item.quantity}</span>
        `;
        shirtItemsContainer.appendChild(div); // ✅ keep same container variable
      });
            } else {
                shirtItemsContainer.textContent = 'N/A';
            }
        } catch (e) {
            console.error('Failed to parse shirt items JSON', e);
            shirtItemsContainer.textContent = 'N/A';
        }
    } else {
        shirtItemsContainer.textContent = 'N/A';
    }
    
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