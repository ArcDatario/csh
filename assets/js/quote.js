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
    
    addQuoteBtn.addEventListener('click', function() {
        quoteModal.classList.add('active');
    });
    
    closeModal.addEventListener('click', function() {
        quoteModal.classList.remove('active');
    });
    
    // Form submission handler
    quoteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate quantity (minimum 500)
        const quantity = parseInt(quantityInput.value);
        if (quantity < 500) {
            showToast('Minimum quantity is 500. Please increase your order quantity.', 'error');
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
                setTimeout(() => {
                    location.reload(); // Refresh the page after a short delay
                }, 1500); // Delay to allow the toast to be visible
            } else {
                showToast('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
        });
    });
    
    // Hide loader when page is loaded
    setTimeout(function() {
        document.getElementById('loader').style.display = 'none';
    }, 1000);
});