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

// Initialize quote modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const addQuoteBtn = document.getElementById('addQuoteBtn');
    const quoteModal = document.getElementById('quoteModal');
    const closeModal = document.getElementById('closeModal');
    const quoteForm = document.getElementById('quoteForm');
    const quantityInput = document.getElementById('quantity');
    const submitBtn = quoteForm.querySelector('.submit-btn');
    
    addQuoteBtn.addEventListener('click', function() {
        quoteModal.classList.add('active');
    });
    
    closeModal.addEventListener('click', function() {
        quoteModal.classList.remove('active');
    });
    
    // Add real-time validation for quantity input
    quantityInput.addEventListener('input', function() {
        const quantity = parseInt(this.value) || 0;
        
        if (quantity > 0 && quantity < 500) {
            this.style.borderColor = '#ff6b6b';
            this.style.boxShadow = '0 0 0 2px rgba(255, 107, 107, 0.2)';
        } else {
            this.style.borderColor = '';
            this.style.boxShadow = '';
        }
    });
    
    // Form submission handler
    quoteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get submit button and show loading state
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Validate quantity (minimum 500)
        const quantity = parseInt(quantityInput.value) || 0;
        if (quantity < 500) {
            showToast('Minimum quantity is 500. Please increase your order quantity.', 'error');
            
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Highlight the quantity field
            quantityInput.style.borderColor = '#ff6b6b';
            quantityInput.style.boxShadow = '0 0 0 2px rgba(255, 107, 107, 0.2)';
            quantityInput.focus();
            
            return false;
        }
        
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
                
                // Reset quantity field styling
                quantityInput.style.borderColor = '';
                quantityInput.style.boxShadow = '';
                
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