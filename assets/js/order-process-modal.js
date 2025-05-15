// Open order process modal
function openOrderProcessModal(
    ticketNumber, 
    quotePlacedDate, 
    approvedDate, 
    isApproved, 
    unitPrice, 
    subtotal, 
    quantity, 
    isUserApproved,
    isForPickup,
    pickupDate,
    isForProcessing,
    processingDate,
    isDelivered,
    deliveredDate
) {
    const modal = document.getElementById('orderProcessModal');
    const stepsContainer = document.getElementById('orderProcessSteps');
    const title = document.getElementById('orderProcessTitle');
    
    // Set title with ticket number
    title.textContent = 'Ticket #' + ticketNumber + ' Process Details';
    title.className = 'order-process-title';
    
    // Clear previous steps
    stepsContainer.innerHTML = '';
    
    // Always show the first step (Quote Placed)
    const steps = [
        {
            title: "Quote Placed",
            description: "Your order request has been received",
            date: quotePlacedDate,
            completed: true
        }
    ];
    
    // If admin approved, show pricing details + user confirmation buttons
    if (isApproved) {
        steps.push({
            title: "Admin Approved",
            description: `
                <div class="order-summary-details">
                    <p>Unit Price: ₱${unitPrice.toFixed(2)}</p>
                    <p>Quantity: ${quantity}</p>
                    <p class="order-subtotal">Subtotal: ₱${subtotal.toFixed(2)}</p>
                </div>
            `,
            date: approvedDate,
            completed: true,
            needsUserApproval: (isUserApproved === null)
        });
        
        // Add remaining steps if user has approved
        if (isUserApproved === true) {
            // Pick up step
            if (isForPickup === 'true') {
                steps.push({
                    title: "Pick up",
                    description: "Your items will be picked up at your location",
                    date: pickupDate,
                    completed: pickupDate !== 'Pending'
                });
            }
            
            // Processing step
            if (isForProcessing === 'true') {
                steps.push({
                    title: "Processing",
                    description: "Items are in the printing process",
                    date: processingDate,
                    completed: processingDate !== 'Pending'
                });
            }
            
            // Delivered step
            if (isDelivered === 'true') {
                steps.push({
                    title: "Delivered",
                    description: "Items have been delivered",
                    date: deliveredDate,
                    completed: deliveredDate !== 'Pending'
                });
            }
        }
    } 
    // If pending admin approval, show only "Quote Placed" + "Admin Review"
    else {
        steps.push({
            title: "Admin Review",
            description: "Your order is being reviewed by our team",
            date: approvedDate || "Pending",
            completed: false
        });
    }
    
    // Render steps
    steps.forEach((step, index) => {
        const stepElement = document.createElement('div');
        stepElement.className = `order-step ${step.completed ? 'order-step-completed' : ''}`;
        
        stepElement.innerHTML = `
            <div class="order-step-number">${index + 1}</div>
            <div class="order-step-connector"></div>
            <div class="order-step-content">
                <div class="order-step-title">${step.title}</div>
                <div class="order-step-description">${step.description}</div>
                <div class="order-step-date">${step.date}</div>
            </div>
        `;
        
        // Show user confirmation buttons if admin approved but user hasn't confirmed yet
        if (step.needsUserApproval) {
            stepElement.innerHTML += `
                  <div class="order-approval-actions">
                    <div class="order-approval-buttons">
                        <button class="order-agree-btn" onclick="userConfirmOrder('${ticketNumber}', true)">
                            <i class="fas fa-check-circle"></i> Agree
                        </button>
                        <button class="order-cancel-btn" onclick="userConfirmOrder('${ticketNumber}', false)">
                            <i class="fas fa-times-circle"></i> Reject
                        </button>
                    </div>
                </div>
            `;
        }
        
        stepsContainer.appendChild(stepElement);
    });
    
    // Show the modal
    modal.style.display = 'flex';
    document.body.classList.add('order-modal-open');
}

// User confirms/rejects the approved pricing
function userConfirmOrder(ticketNumber, isConfirmed) {
    // Send AJAX request to update order status
    fetch('update_is_user_approved.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            ticketNumber: ticketNumber,
            isConfirmed: isConfirmed 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create a confirmation modal dynamically
            const confirmationModal = document.createElement('div');
            confirmationModal.className = 'agree-confirmation-modal';
            confirmationModal.innerHTML = `
                <div class="agree-confirmation-modal-content">
                    <h3>${isConfirmed ? "Quote Confirmed" : "Order Rejected"}</h3>
                    <p>${isConfirmed ? "Items will be picked up at your location" : "Your order has been rejected."}</p>
                    <div class="modal-buttons">
                        <button id="closeAgreeConfirmationModal" class="modal-close-btn">Agree & Continue</button>
                        <button id="cancelAgreeConfirmationModal" class="modal-cancel-btn">Cancel</button>
                    </div>
                </div>
            `;

            document.body.appendChild(confirmationModal);

            // Show the modal
            setTimeout(() => {
                confirmationModal.classList.add('show');
            }, 10);

            // Close modal on "OK" button click
            document.getElementById('closeAgreeConfirmationModal').addEventListener('click', () => {
                confirmationModal.classList.remove('show');
                setTimeout(() => {
                    confirmationModal.remove();
                }, 300);

                // Call the toast notification
                if (isConfirmed) {
                    showToast('Agree item will be picked up at your location');
                    setTimeout(() => {
                        location.reload(); // Reload the page after a short delay
                    }, 1500);
                } else {
                    showToast('Order has been rejected', 'error');
                }

                // Close the order process modal
                closeOrderProcessModal();
            });

            // Close modal on "Cancel" button click
            document.getElementById('cancelAgreeConfirmationModal').addEventListener('click', () => {
                confirmationModal.classList.remove('show');
                setTimeout(() => {
                    confirmationModal.remove();
                }, 300);
            });
        } else {
            // Show an error toast
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        // Show an error toast for network issues
        showToast('An error occurred. Please try again.', 'error');
    });
}

function closeOrderProcessModal() {
    document.getElementById('orderProcessModal').style.display = 'none';
    document.body.classList.remove('order-modal-open');
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('orderProcessModal')) {
        closeOrderProcessModal();
    }
});