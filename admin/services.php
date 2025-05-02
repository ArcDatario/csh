<?php
require_once 'auth_check.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Dashboard</title>
    <link rel="icon" href="assets/images/analysis.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
         .image-preview {
        margin-bottom: 20px;
        text-align: center;
        border-radius: 8px;
        overflow: hidden;
        max-height: 200px;
        background: transparent;
        
    }
    
    .image-preview img {
        max-width: 70%;
        max-height: 100px;
        object-fit: contain;
    }
    
    /* Modern File Input Styles */
    .modern-file-input {
        position: relative;
        margin-bottom: 15px;
    }
    
    .modern-file-input input[type="file"] {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
    }
    
    .modern-file-input label {
        display: flex;
        align-items: center;
        width: 100%;
        cursor: pointer;
    }
    
    .file-input-label {
        flex-grow: 1;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px 0 0 4px;
        background: #f8f9fa;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .file-input-btn {
        padding: 8px 16px;
        background: #e9ecef;
        border: 1px solid #ddd;
        border-left: none;
        border-radius: 0 4px 4px 0;
        font-weight: 500;
    }
    
    .modern-file-input:hover .file-input-btn {
        background: #dee2e6;
    }

    </style>

</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <button class="mobile-menu-toggle" id="menuToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

        <?php include "includes/sidebar.php";?>
    
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Main Content -->
        <main class="main">
            <header class="header">
                <h1 class="header-dashboard">Dashboard</h1>
                
                <div class="user-menu">
                <div class="theme-toggle" id="themeToggle">
                <span style="margin-right:8px;">Dark Mode</span>
                <i class="fas fa-moon"></i>
            </div>
                    
          <?php include "includes/notification.php";?>

    </div>
                    
                    <div class="user-avatar">Csh</div>
                </div>
            </header>
            
          
            
            <!-- Charts -->
            
            
            <!-- Table -->
            <section class="table-card fade-in">
                <div class="table-header">
                    <h3 class="table-title">Recent Orders</h3>
                    <div class="table-actions">
                        <button class="btn btn-outline">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                        </button>
                        <button class="btn btn-primary" id="addServiceBtn">
                            <i class="fas fa-plus"></i>
                            <span>Add Service</span>
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Service</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="servicesTableBody">
                            
                           
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    
    <!-- Modal -->
    <div class="modal" id="ServiceModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Service</h3>
            <button class="modal-close" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Image preview will appear here -->
            <div class="image-preview" id="imagePreviewContainer" style="display: none;">
                <img id="imagePreview" src="" alt="Service Image Preview">
            </div>
            
            <form id="serviceForm" action="add_service.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="serviceImage" class="form-label">Service Image</label>
                    <div class="modern-file-input">
                        <label for="serviceImage">
                            <span class="file-input-label">Choose an image file</span>
                            <span class="file-input-btn">Browse</span>
                        </label>
                        <input type="file" id="serviceImage" name="serviceImage" accept="image/*" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="serviceName" class="form-label">Service Name</label>
                    <input type="text" id="serviceName" name="serviceName" class="form-control" placeholder="Enter service name" required>
                </div>
                <div class="form-group">
                    <label for="serviceDescription" class="form-label">Description</label>
                    <textarea id="serviceDescription" name="serviceDescription" class="form-control" placeholder="Enter service description" rows="3" required></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancelService">Cancel</button>
            <button class="btn btn-primary" id="saveService">Save Service</button>
        </div>
    </div>
</div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>



<script src="assets/js/script.js"></script>


<script>
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
</script>

<script>
   const serviceModal = document.getElementById('ServiceModal');
const addServiceBtn = document.getElementById('addServiceBtn');
const closeModal = document.getElementById('closeModal');
const cancelService = document.getElementById('cancelService');
const saveService = document.getElementById('saveService');
const serviceForm = document.getElementById('serviceForm');
const imageInput = document.getElementById('serviceImage');
const imagePreviewContainer = document.getElementById('imagePreviewContainer');
const imagePreview = document.getElementById('imagePreview');

// Show modal
addServiceBtn.addEventListener('click', () => {
    serviceModal.classList.add('active');
});

// Close modal
closeModal.addEventListener('click', () => {
    serviceModal.classList.remove('active');
});

cancelService.addEventListener('click', () => {
    serviceModal.classList.remove('active');
});

// Close modal when clicking outside
serviceModal.addEventListener('click', (e) => {
    if (e.target === serviceModal) {
        serviceModal.classList.remove('active');
    }
});

// Image preview functionality
imageInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreviewContainer.style.display = 'block';
        }
        
        reader.readAsDataURL(file);
    } else {
        imagePreview.src = '';
        imagePreviewContainer.style.display = 'none';
    }
});

// Save service - using AJAX to submit the form
saveService.addEventListener('click', (e) => {
    e.preventDefault();
    
    if (serviceForm.checkValidity()) {
        const formData = new FormData(serviceForm);
        
        fetch('add-service.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Service Added', 'The new service has been added successfully', 'success');
                serviceModal.classList.remove('active');
                serviceForm.reset();
                imagePreviewContainer.style.display = 'none';
                
                // Refresh the services table
                refreshServicesTable();
            } else {
                showToast('Error', data.message || 'Failed to add service', 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'An error occurred while adding the service', 'error');
            console.error('Error:', error);
        });
    } else {
        serviceForm.reportValidity();
    }
});


</script>






<script>
      document.getElementById('serviceImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewContainer = document.getElementById('imagePreviewContainer');
        const preview = document.getElementById('imagePreview');
        
        if (file && file.type.match('image.*')) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.style.display = 'block';
            }
            
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            previewContainer.style.display = 'none';
        }
    });
</script>

<script src="assets/js/services.js"></script>
</body>
</html>