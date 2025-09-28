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
    <!-- Pagination for On Pickup -->
    <div class="pagination"></div>
</div>

<script>

// ============================
// 2. ACTION HANDLERS
// ============================
function handleReattempt()   { handleOnPickupAction('reattempt', reattemptBtn); }
function handleFailed()      { handleOnPickupAction('failed', failedBtn); }
function handleReject() {
    if (!confirm('Are you sure you want to reject this order? This action cannot be undone.')) return;
    handleOnPickupAction('reject', rejectBtn);
}
function handlePickedUp()    { handleOnPickupAction('pickedup', pickedUpBtn); }

function handleOnPickupAction(action, buttonEl) {
    const id      = onPickupModal.getAttribute('data-current-id');
    const userId  = document.getElementById('onpickup-modal-user-id').value;
    const email   = document.getElementById('onpickup-modal-email').value;
    const ticket  = document.getElementById('onpickup-modal-ticket').value;
    const attempt = document.getElementById('onpickup-modal-attempt').value;

    const originalText = buttonEl.textContent;
    buttonEl.disabled = true;
    buttonEl.textContent = 'Processing...';

    fetch('functions/onpickup_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, id, user_id: userId, email, ticket, attempt })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            onPickupModal.style.display = 'none';
            updateOnPickupTable();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showToast('Error', 'An error occurred', 'error');
    })
    .finally(() => {
        buttonEl.disabled = false;
        buttonEl.textContent = originalText;
    });
}

// ============================
// 3. MODAL CLOSE HANDLERS
// ============================
function closeOnPickupModal() { onPickupModal.style.display = 'none'; }
function handleWindowClick(event) {
    if (event.target === onPickupModal) closeOnPickupModal();
}

// ============================
// 4. EVENT LISTENER ATTACHMENT
// ============================
function attachOnPickupEventListeners() {
    // View buttons
    document.querySelectorAll('.view-on-pickup-modal').forEach(button => {
        button.addEventListener('click', handleOnPickupViewButtonClick);
    });

    // Modal close
    onPickupModalClose.addEventListener('click', closeOnPickupModal);
    closeOnPickupBtn.addEventListener('click', closeOnPickupModal);
    window.addEventListener('click', handleWindowClick);

    // Action buttons
    reattemptBtn.addEventListener('click', handleReattempt);
    failedBtn.addEventListener('click', handleFailed);
    rejectBtn.addEventListener('click', handleReject);
    pickedUpBtn.addEventListener('click', handlePickedUp);
}

// ============================
// 5. INITIALIZE
// ============================
document.addEventListener('DOMContentLoaded', () => {
    attachOnPickupEventListeners();

});
</script>
