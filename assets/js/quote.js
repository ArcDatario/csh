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
        // Use the full set of options for all item types/colors
        newItem.innerHTML = `
    <div class="form-row">
        <div class="form-col">
            <select name="item_color[]" class="form-control" required>
                <option value="">Select Item & Color</option>
                <option value="Shirt (White)">Shirt (White)</option>
                <option value="Shirt (Black)">Shirt (Black)</option>
                <option value="Shirt (Red)">Shirt (Red)</option>
                <option value="Shirt (Blue)">Shirt (Blue)</option>
                <option value="Shirt (Green)">Shirt (Green)</option>
                <option value="Shirt (Yellow)">Shirt (Yellow)</option>
                <option value="Shirt (Orange)">Shirt (Orange)</option>
                <option value="Shirt (Purple)">Shirt (Purple)</option>
                <option value="Shirt (Pink)">Shirt (Pink)</option>
                <option value="Shirt (Gray)">Shirt (Gray)</option>
                <option value="Shirt (Brown)">Shirt (Brown)</option>
                <option value="Shirt (Navy)">Shirt (Navy)</option>
                <option value="Shirt (Maroon)">Shirt (Maroon)</option>
                <option value="Shirt (Teal)">Shirt (Teal)</option>
                <option value="Shirt (Olive)">Shirt (Olive)</option>
                <option value="Jacket (White)">Jacket (White)</option>
                <option value="Jacket (Black)">Jacket (Black)</option>
                <option value="Jacket (Red)">Jacket (Red)</option>
                <option value="Jacket (Blue)">Jacket (Blue)</option>
                <option value="Jacket (Green)">Jacket (Green)</option>
                <option value="Jacket (Yellow)">Jacket (Yellow)</option>
                <option value="Jacket (Orange)">Jacket (Orange)</option>
                <option value="Jacket (Purple)">Jacket (Purple)</option>
                <option value="Jacket (Pink)">Jacket (Pink)</option>
                <option value="Jacket (Gray)">Jacket (Gray)</option>
                <option value="Jacket (Brown)">Jacket (Brown)</option>
                <option value="Jacket (Navy)">Jacket (Navy)</option>
                <option value="Jacket (Maroon)">Jacket (Maroon)</option>
                <option value="Jacket (Teal)">Jacket (Teal)</option>
                <option value="Jacket (Olive)">Jacket (Olive)</option>
                <option value="Shorts (White)">Shorts (White)</option>
                <option value="Shorts (Black)">Shorts (Black)</option>
                <option value="Shorts (Red)">Shorts (Red)</option>
                <option value="Shorts (Blue)">Shorts (Blue)</option>
                <option value="Shorts (Green)">Shorts (Green)</option>
                <option value="Shorts (Yellow)">Shorts (Yellow)</option>
                <option value="Shorts (Orange)">Shorts (Orange)</option>
                <option value="Shorts (Purple)">Shorts (Purple)</option>
                <option value="Shorts (Pink)">Shorts (Pink)</option>
                <option value="Shorts (Gray)">Shorts (Gray)</option>
                <option value="Shorts (Brown)">Shorts (Brown)</option>
                <option value="Shorts (Navy)">Shorts (Navy)</option>
                <option value="Shorts (Maroon)">Shorts (Maroon)</option>
                <option value="Shorts (Teal)">Shorts (Teal)</option>
                <option value="Shorts (Olive)">Shorts (Olive)</option>
                <option value="Bag (White)">Bag (White)</option>
                <option value="Bag (Black)">Bag (Black)</option>
                <option value="Bag (Red)">Bag (Red)</option>
                <option value="Bag (Blue)">Bag (Blue)</option>
                <option value="Bag (Green)">Bag (Green)</option>
                <option value="Bag (Yellow)">Bag (Yellow)</option>
                <option value="Bag (Orange)">Bag (Orange)</option>
                <option value="Bag (Purple)">Bag (Purple)</option>
                <option value="Bag (Pink)">Bag (Pink)</option>
                <option value="Bag (Gray)">Bag (Gray)</option>
                <option value="Bag (Brown)">Bag (Brown)</option>
                <option value="Bag (Navy)">Bag (Navy)</option>
                <option value="Bag (Maroon)">Bag (Maroon)</option>
                <option value="Bag (Teal)">Bag (Teal)</option>
                <option value="Bag (Olive)">Bag (Olive)</option>
                <option value="Jersey (White)">Jersey (White)</option>
                <option value="Jersey (Black)">Jersey (Black)</option>
                <option value="Jersey (Red)">Jersey (Red)</option>
                <option value="Jersey (Blue)">Jersey (Blue)</option>
                <option value="Jersey (Green)">Jersey (Green)</option>
                <option value="Jersey (Yellow)">Jersey (Yellow)</option>
                <option value="Jersey (Orange)">Jersey (Orange)</option>
                <option value="Jersey (Purple)">Jersey (Purple)</option>
                <option value="Jersey (Pink)">Jersey (Pink)</option>
                <option value="Jersey (Gray)">Jersey (Gray)</option>
                <option value="Jersey (Brown)">Jersey (Brown)</option>
                <option value="Jersey (Navy)">Jersey (Navy)</option>
                <option value="Jersey (Maroon)">Jersey (Maroon)</option>
                <option value="Jersey (Teal)">Jersey (Teal)</option>
                <option value="Jersey (Olive)">Jersey (Olive)</option>
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