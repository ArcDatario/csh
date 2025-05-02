// Function to fetch and display services
function refreshServicesTable() {
    fetch('get_services.php')
        .then(response => response.json())
        .then(services => {
            const tableBody = document.getElementById('servicesTableBody');
            tableBody.innerHTML = ''; // Clear current rows
            
            if (services.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No services found</td></tr>';
                return;
            }
            
            services.forEach(service => {
                const row = document.createElement('tr');
                
                // Image column
                const imgCell = document.createElement('td');
                imgCell.innerHTML = `
                    <img src="${service.image}" alt="${service.service_name}" 
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                `;
                
                // Service name column
                const nameCell = document.createElement('td');
                nameCell.textContent = service.service_name;
                
                // Description column
                const descCell = document.createElement('td');
                descCell.textContent = service.description;
                
                // Action column
                const actionCell = document.createElement('td');
                actionCell.innerHTML = `
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline edit-service" data-id="${service.id}" 
                                style="color: #007bff; border-color: #007bff;">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger delete-service" data-id="${service.id}" 
                                style="background-color: #dc3545; color: #fff; border-color: #dc3545;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                `;
                
                // Append all cells to the row
                row.appendChild(imgCell);
                row.appendChild(nameCell);
                row.appendChild(descCell);
                row.appendChild(actionCell);
                
                // Append row to table
                tableBody.appendChild(row);
            });
            
            // Attach event listeners to the new buttons
            attachServiceButtonEvents();
        })
        .catch(error => {
            console.error('Error loading services:', error);
            showToast('Error', 'Failed to load services', 'error');
        });
}

// Function to show the edit service modal
function showEditServiceModal(serviceId) {
    // Since we already have all services data, we can use that instead of making another request
    fetch('get_services.php')
        .then(response => response.json())
        .then(services => {
            const service = services.find(s => s.id == serviceId);
            if (!service) {
                showToast('Error', 'Service not found', 'error');
                return;
            }

            const modalHTML = `
    <div class="modal-overlay" style="
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    ">
        <div class="modal-content" style="
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        ">
            <!-- Image Preview (centered) -->
            <div id="imagePreviewContainer" style="
                display: flex;
                justify-content: center;
                margin-bottom: 20px;
                transition: all 0.3s ease;
            ">
                <img id="imagePreview" src="${service.image}" alt="Service Image" 
                     style="
                        max-width: 150px;
                        max-height: 120px;
                        border-radius: 8px;
                        border: 2px solid #eee;
                        object-fit: cover;
                        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                        transition: all 0.3s ease;
                     "
                     onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 6px 15px rgba(0,0,0,0.15)'"
                     onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.1)'">
            </div>

            <h3 style="
                margin: 0 0 20px 0;
                color: #2c3e50;
                text-align: center;
                font-size: 1.5rem;
                font-weight: 600;
            ">Edit Service</h3>
            
            <form id="editServiceForm" enctype="multipart/form-data">
                <input type="hidden" name="id" value="${service.id}">
                     <div class="form-group" style="margin-bottom: 25px;">
                    <label style="
                        display: block;
                        margin-bottom: 8px;
                        font-weight: 500;
                        color: #34495e;
                    ">Change Image</label>
                    
                    <!-- Minimalist File Input -->
                    <div style="position: relative;">
                        <input type="file" id="editImage" name="image" 
                               style="position: absolute; width: 0.1px; height: 0.1px; opacity: 0; overflow: hidden; z-index: -1;"
                               accept="image/*">
                        <label for="editImage" id="fileInputLabel" style="
                            display: inline-block;
                            padding: 8px 15px;
                            background-color: #f8f9fa;
                            border: 1px solid #ddd;
                            border-radius: 6px;
                            cursor: pointer;
                            transition: all 0.2s ease;
                            font-size: 0.9rem;
                            color: #34495e;
                        " 
                        onmouseover="this.style.backgroundColor='#eaf2f8'; this.style.borderColor='#3498db'"
                        onmouseout="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#ddd'">
                            <i class="fas fa-image" style="margin-right: 5px;"></i> Choose Image
                        </label>
                        <span id="selectedFileName" style="
                            margin-left: 10px;
                            font-size: 0.85rem;
                            color: #7f8c8d;
                        "></span>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="editServiceName" style="
                        display: block;
                        margin-bottom: 8px;
                        font-weight: 500;
                        color: #34495e;
                    ">Service Name</label>
                    <input type="text" id="editServiceName" name="service_name" 
                           value="${escapeHtml(service.service_name)}" required
                           style="
                                width: 100%;
                                padding: 12px;
                                border: 1px solid #ddd;
                                border-radius: 6px;
                                transition: all 0.3s;
                                font-size: 1rem;
                           "
                           onfocus="this.style.borderColor='#3498db'; this.style.boxShadow='0 0 0 3px rgba(52,152,219,0.2)'"
                           onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none'">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="editDescription" style="
                        display: block;
                        margin-bottom: 8px;
                        font-weight: 500;
                        color: #34495e;
                    ">Description</label>
                    <textarea id="editDescription" name="description" 
                              style="
                                width: 100%;
                                padding: 12px;
                                border: 1px solid #ddd;
                                border-radius: 6px;
                                min-height: 100px;
                                transition: all 0.3s;
                                font-size: 1rem;
                              "
                              onfocus="this.style.borderColor='#3498db'; this.style.boxShadow='0 0 0 3px rgba(52,152,219,0.2)'"
                              onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none'">${escapeHtml(service.description)}</textarea>
                </div>
                
               
                
                <div style="
                    margin-top: 25px;
                    display: flex;
                    justify-content: flex-end;
                    gap: 12px;
                ">
                    <button type="button" id="cancelEditBtn" style="
                        padding: 10px 20px;
                        background-color: #95a5a6;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        transition: all 0.3s;
                        font-weight: 500;
                        font-size: 1rem;
                    " 
                    onmouseover="this.style.backgroundColor='#7f8c8d'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.backgroundColor='#95a5a6'; this.style.transform='translateY(0)'">
                        Cancel
                    </button>
                    <button type="submit" id="saveServiceBtn" style="
                        padding: 10px 20px;
                        background-color: #3498db;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        transition: all 0.3s;
                        font-weight: 500;
                        font-size: 1rem;
                    " 
                    onmouseover="this.style.backgroundColor='#2980b9'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.backgroundColor='#3498db'; this.style.transform='translateY(0)'">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
`;

// Insert modal into DOM
document.body.insertAdjacentHTML('beforeend', modalHTML);

// Get modal elements
const modalOverlay = document.querySelector('.modal-overlay');
const cancelBtn = document.getElementById('cancelEditBtn');
const editForm = document.getElementById('editServiceForm');
const fileInput = document.getElementById('editImage');
const fileInputLabel = document.getElementById('fileInputLabel');
const selectedFileName = document.getElementById('selectedFileName');
const imagePreview = document.getElementById('imagePreview');

// Handle file selection and preview
fileInput.addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
            
            // Add visual feedback when image loads
            imagePreview.style.borderColor = '#2ecc71';
            setTimeout(() => {
                imagePreview.style.borderColor = '#eee';
            }, 1500);
        }
        
        reader.readAsDataURL(this.files[0]);
        selectedFileName.textContent = this.files[0].name;
        
        // Add visual feedback to file input
        fileInputLabel.style.backgroundColor = '#e8f8f5';
        fileInputLabel.style.borderColor = '#2ecc71';
        fileInputLabel.innerHTML = '<i class="fas fa-check" style="margin-right: 5px;"></i> Image Selected';
        
        setTimeout(() => {
            fileInputLabel.style.backgroundColor = '#f8f9fa';
            fileInputLabel.style.borderColor = '#ddd';
            fileInputLabel.innerHTML = '<i class="fas fa-image" style="margin-right: 5px;"></i> Change Image';
        }, 1500);
    }
});

// Close modal when clicking outside
modalOverlay.addEventListener('click', (e) => {
    if (e.target === modalOverlay) {
        document.body.removeChild(modalOverlay);
    }
});

// Handle cancel button
cancelBtn.addEventListener('click', () => {
    document.body.removeChild(modalOverlay);
});


            
            // Handle form submission
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Create FormData object
                const formData = new FormData(this);
                
                // Show loading state
                const saveBtn = document.getElementById('saveServiceBtn');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                
                updateService(formData);
            });
            
            // Cancel button
            cancelBtn.addEventListener('click', () => {
                document.body.removeChild(modalOverlay);
            });
            
            // Close modal when clicking outside
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    document.body.removeChild(modalOverlay);
                }
            });
        })
        .catch(error => {
            console.error('Error fetching services:', error);
            showToast('Error', 'Failed to load service details', 'error');
        });
}

// Helper function to escape HTML to prevent XSS
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Function to update the service
function updateService(formData) {
    fetch('update-service.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Service updated successfully', 'success');
            document.querySelector('.modal-overlay')?.remove();
            refreshServicesTable();
        } else {
            showToast('Error', data.message || 'Failed to update service', 'error');
            // Re-enable save button if there was an error
            const saveBtn = document.getElementById('saveServiceBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Save Changes';
            }
        }
    })
    .catch(error => {
        console.error('Error updating service:', error);
        showToast('Error', 'Failed to update service', 'error');
        // Re-enable save button if there was an error
        const saveBtn = document.getElementById('saveServiceBtn');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Changes';
        }
    });
}

// Function to attach event listeners to action buttons
function attachServiceButtonEvents() {
    // Edit buttons
    document.querySelectorAll('.edit-service').forEach(button => {
        button.addEventListener('click', function() {
            const serviceId = this.getAttribute('data-id');
            showEditServiceModal(serviceId);
        });
    });
    
    // Delete buttons
    document.querySelectorAll('.delete-service').forEach(button => {
        button.addEventListener('click', function() {
            const serviceId = this.getAttribute('data-id');
            
            // Create modal HTML with proper styling
            const modalHTML = `
                <div class="modal-overlay" style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: rgba(0, 0, 0, 0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 1000;
                ">
                    <div class="modal-content" style="
                        background-color: white;
                        padding: 25px;
                        border-radius: 8px;
                        max-width: 400px;
                        width: 90%;
                        text-align: center;
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    ">
                        <h3 style="margin-top: 0; margin-bottom: 15px; color: #333;">Confirm Deletion</h3>
                        <p style="margin-bottom: 20px; color: #555;">Are you sure you want to delete this service?</p>
                        <div style="display: flex; justify-content: center; gap: 10px;">
                            <button id="confirmDeleteBtn" style="
                                padding: 8px 16px;
                                background-color: #dc3545;
                                color: white;
                                border: none;
                                border-radius: 4px;
                                cursor: pointer;
                            ">
                                Delete
                            </button>
                            <button id="cancelDeleteBtn" style="
                                padding: 8px 16px;
                                background-color: #6c757d;
                                color: white;
                                border: none;
                                border-radius: 4px;
                                cursor: pointer;
                            ">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Insert modal into DOM
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Get modal elements
            const modalOverlay = document.querySelector('.modal-overlay');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            const cancelBtn = document.getElementById('cancelDeleteBtn');
            
            // Confirm deletion
            confirmBtn.addEventListener('click', () => {
                deleteService(serviceId);
                document.body.removeChild(modalOverlay);
            });
            
            // Cancel deletion
            cancelBtn.addEventListener('click', () => {
                document.body.removeChild(modalOverlay);
            });
            
            // Close modal when clicking outside
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    document.body.removeChild(modalOverlay);
                }
            });
        });
    });
}

function deleteService(serviceId) {
    fetch('delete-service.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${serviceId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Service deleted successfully', 'success');
            refreshServicesTable();
        } else {
            showToast('Error', data.message || 'Failed to delete service', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting service:', error);
        showToast('Error', 'Failed to delete service', 'error');
    });
}

// Toast Function (same as yours)
function showToast(title, message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Set toast content
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${type === 'success' ? 'fa-check' : 
                          type === 'error' ? 'fa-times' : 
                          type === 'warning' ? 'fa-exclamation' : 
                          'fa-info'}"></i>
        </div>
        <div class="toast-content">
            <h4 class="toast-title">${title}</h4>
            <p class="toast-message">${message}</p>
        </div>
        <button class="toast-close">&times;</button>
    `;
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Auto remove toast after 5 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 5000);
    
    // Close button
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', refreshServicesTable);