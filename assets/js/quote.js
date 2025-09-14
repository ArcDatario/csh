// Function to show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Hide after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

document.getElementById('designFile').addEventListener('change', function(e) {
    const fileInputBtn = this.closest('.file-input-btn');
    const uploadText = fileInputBtn.querySelector('.upload-text');
    const fileNameDisplay = document.getElementById('file-name');
    
    if (this.files.length > 0) {
        // Show uploaded state
        fileInputBtn.classList.add('uploaded');
        uploadText.textContent = 'File uploaded!';
        fileNameDisplay.textContent = this.files[0].name;
    } else {
        // Reset to initial state
        fileInputBtn.classList.remove('uploaded');
        uploadText.textContent = 'Click to upload design file';
        fileNameDisplay.textContent = '';
    }
});

// Global variable for total quantity
let totalQuantity = 0;

// Function to update total quantity
function updateTotalQuantity() {
    totalQuantity = 0;
    document.querySelectorAll('.shirt-quantity').forEach(input => {
        const value = parseInt(input.value) || 0;
        totalQuantity += value;
    });
    document.getElementById('totalQuantity').textContent = totalQuantity;
}

// Function to toggle remove buttons
function toggleRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-item-btn');
    // Show remove buttons only if there's more than one item
    removeButtons.forEach((btn, index) => {
        btn.style.display = removeButtons.length > 1 ? 'block' : 'none';
    });
}

// Initialize quote modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const addQuoteBtn = document.getElementById('addQuoteBtn');
    const quoteModal = document.getElementById('quoteModal');
    const closeModal = document.getElementById('closeModal');
    const quoteForm = document.getElementById('quoteForm');
    const submitBtn = quoteForm.querySelector('.submit-btn');
    
    addQuoteBtn.addEventListener('click', function() {
        quoteModal.classList.add('active');
    });
    
    closeModal.addEventListener('click', function() {
        quoteModal.classList.remove('active');
    });
    
    // Initialize the first shirt item
    updateTotalQuantity();
    toggleRemoveButtons();
    
    // Add shirt item functionality
    document.getElementById('addShirtItem').addEventListener('click', function() {
        const container = document.getElementById('shirtItemsContainer');
        const newItem = document.createElement('div');
        newItem.className = 'shirt-item-row';
       // In the addShirtItem event listener, replace the input with select
newItem.innerHTML = `
    <div class="form-row">
        <div class="form-col">
            <select name="shirt_color[]" class="form-control" required>
                <option value="">Select Color</option>
                <option value="White">White</option>
                <option value="Black">Black</option>
                <option value="Red">Red</option>
                <option value="Blue">Blue</option>
                <option value="Green">Green</option>
                <option value="Yellow">Yellow</option>
                <option value="Orange">Orange</option>
                <option value="Purple">Purple</option>
                <option value="Pink">Pink</option>
                <option value="Gray">Gray</option>
                <option value="Brown">Brown</option>
                <option value="Navy">Navy</option>
                <option value="Maroon">Maroon</option>
                <option value="Teal">Teal</option>
                <option value="Olive">Olive</option>
                <option value="Other">Other (Specify in notes)</option>
            </select>
        </div>
        <div class="form-col">
            <input type="number" name="shirt_quantity[]" class="form-control shirt-quantity" min="1" placeholder="Qty" required>
        </div>
        <div class="form-col" style="flex: 0 0 auto;">
            <button type="button" class="remove-item-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
`;
        container.appendChild(newItem);
        
        // Add event listener to the new quantity input
        const quantityInput = newItem.querySelector('.shirt-quantity');
        quantityInput.addEventListener('input', updateTotalQuantity);
        
        // Add event listener to the remove button
        const removeBtn = newItem.querySelector('.remove-item-btn');
        removeBtn.addEventListener('click', function() {
            newItem.remove();
            updateTotalQuantity();
            toggleRemoveButtons();
        });
        
        updateTotalQuantity();
        toggleRemoveButtons();
    });
    
    // Add event listeners to existing quantity inputs
    document.querySelectorAll('.shirt-quantity').forEach(input => {
        input.addEventListener('input', updateTotalQuantity);
    });
    
    // Add event listeners to existing remove buttons
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.shirt-item-row').remove();
            updateTotalQuantity();
            toggleRemoveButtons();
        });
    });
    
    // Form submission handler
    quoteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get submit button and show loading state
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Validate total quantity (minimum 500)
        if (totalQuantity < 500) {
            showToast('Minimum total quantity is 500. Please increase your order quantity.', 'error');
            
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Highlight the total quantity display
            document.getElementById('totalQuantity').style.color = '#ff6b6b';
            
            return false;
        }
        
        // Create a hidden input for total quantity
        let totalInput = document.querySelector('input[name="total_quantity"]');
        if (!totalInput) {
            totalInput = document.createElement('input');
            totalInput.type = 'hidden';
            totalInput.name = 'total_quantity';
            this.appendChild(totalInput);
        }
        totalInput.value = totalQuantity;
        
        const formData = new FormData(this);
        
        fetch('submit_quote.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Quote submitted successfully!');
                quoteModal.classList.remove('active');
                quoteForm.reset();
                
                // Reset total quantity display
                document.getElementById('totalQuantity').textContent = '0';
                document.getElementById('totalQuantity').style.color = '';
                
                setTimeout(() => {
                    location.reload(); // Refresh the page after a short delay
                }, 1500); // Delay to allow the toast to be visible
            } else {
                showToast('Error: ' + data.message, 'error');
                
                // Reset button state on error
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
            
            // Reset button state on error
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Hide loader when page is loaded
    setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
    }, 1000);
});