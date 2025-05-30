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
    

   <?php include "includes/link-css.php";?>
    
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
                    
                   <?php include "includes/profile.php";?>
                </div>
            </header>
            
          
            
            <!-- Charts -->
            
            
            <!-- Table -->
            <section class="table-card fade-in">
    <div class="table-header">
        <h3 class="table-title">Recent Works</h3>
        <div class="table-actions">
            <button class="btn btn-outline">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
            <button class="btn btn-primary" id="addWorkBtn">
                <i class="fas fa-plus"></i>
                <span>Add Work</span>
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Work Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="worksTableBody">
                <!-- Works will be loaded here -->
            </tbody>
        </table>
    </div>
</section>
        </main>
    </div>
    
    <!-- Modal -->
    <div class="modal" id="WorkModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Work</h3>
            <button class="modal-close" id="closeModal">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Image preview will appear here -->
            <div class="image-preview" id="imagePreviewContainer" style="display: none;">
                <img id="imagePreview" src="" alt="Work Image Preview">
            </div>
            
            <form id="workForm" action="add_work.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="workImage" class="form-label">Work Image</label>
                    <div class="modern-file-input">
                        <label for="workImage">
                            <span class="file-input-label">Choose an image file</span>
                            <span class="file-input-btn">Browse</span>
                        </label>
                        <input type="file" id="workImage" name="workImage" accept="image/*" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="workName" class="form-label">Work Name</label>
                    <input type="text" id="workName" name="workName" class="form-control" placeholder="Enter work name" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancelWork">Cancel</button>
            <button class="btn btn-primary" id="saveWork">Save Work</button>
        </div>
    </div>
</div>
</div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>






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








<script>
const workModal = document.getElementById('WorkModal');
const addWorkBtn = document.getElementById('addWorkBtn');
const closeModal = document.getElementById('closeModal');
const cancelWork = document.getElementById('cancelWork');
const saveWork = document.getElementById('saveWork');
const workForm = document.getElementById('workForm');
const imageInput = document.getElementById('workImage');
const imagePreviewContainer = document.getElementById('imagePreviewContainer');
const imagePreview = document.getElementById('imagePreview');

// Show modal
addWorkBtn.addEventListener('click', () => {
    workModal.classList.add('active');
});

// Close modal
closeModal.addEventListener('click', () => {
    workModal.classList.remove('active');
});

cancelWork.addEventListener('click', () => {
    workModal.classList.remove('active');
});

// Close modal when clicking outside
workModal.addEventListener('click', (e) => {
    if (e.target === workModal) {
        workModal.classList.remove('active');
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

// Save work - using AJAX to submit the form
saveWork.addEventListener('click', (e) => {
    e.preventDefault();
    
    if (workForm.checkValidity()) {
        const formData = new FormData(workForm);
        
        fetch('add_work.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Work Added', 'The new work has been added successfully', 'success');
                workModal.classList.remove('active');
                workForm.reset();
                imagePreviewContainer.style.display = 'none';
                
                // Refresh the works table
                refreshWorksTable();
            } else {
                showToast('Error', data.message || 'Failed to add work', 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'An error occurred while adding the work', 'error');
            console.error('Error:', error);
        });
    } else {
        workForm.reportValidity();
    }
});

// Function to refresh works table
function refreshWorksTable() {
    fetch('get_works.php')
        .then(response => response.json())
        .then(works => {
            const tableBody = document.getElementById('worksTableBody');
            tableBody.innerHTML = ''; // Clear current rows
            
            if (works.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="3" class="text-center">No works found</td></tr>';
                return;
            }
            
            works.forEach(work => {
                const row = document.createElement('tr');
                
                // Image column
                const imgCell = document.createElement('td');
                imgCell.innerHTML = `
                    <img src="${work.image}" alt="${work.work_name}" 
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                `;
                
                // Work name column
                const nameCell = document.createElement('td');
                nameCell.textContent = work.work_name;
                
                // Action column
                const actionCell = document.createElement('td');
                actionCell.innerHTML = `
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-outline edit-work" data-id="${work.id}" 
                                style="color: #007bff; border-color: #007bff;">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger delete-work" data-id="${work.id}" 
                                style="background-color: #dc3545; color: #fff; border-color: #dc3545;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                `;
                
                // Append all cells to the row
                row.appendChild(imgCell);
                row.appendChild(nameCell);
                row.appendChild(actionCell);
                
                // Append row to table
                tableBody.appendChild(row);
            });
            
            // Attach event listeners to the new buttons
            attachWorkButtonEvents();
        })
        .catch(error => {
            console.error('Error loading works:', error);
            showToast('Error', 'Failed to load works', 'error');
        });
}

function showEditWorkModal(workId) {
    fetch('get_works.php')
        .then(response => response.json())
        .then(works => {
            const work = works.find(w => w.id == workId);
            if (!work) {
                showToast('Error', 'Work not found', 'error');
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
                <img id="imagePreview" src="${work.image}" alt="Work Image" 
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
            ">Edit Work</h3>
            
            <form id="editWorkForm" enctype="multipart/form-data">
                <input type="hidden" name="id" value="${work.id}">
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
                    <label for="editWorkName" style="
                        display: block;
                        margin-bottom: 8px;
                        font-weight: 500;
                        color: #34495e;
                    ">Work Name</label>
                    <input type="text" id="editWorkName" name="work_name" 
                           value="${escapeHtml(work.work_name)}" required
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
                    <button type="submit" id="saveWorkBtn" style="
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
            const editForm = document.getElementById('editWorkForm');
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
                const saveBtn = document.getElementById('saveWorkBtn');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                
                updateWork(formData);
            });
        })
        .catch(error => {
            console.error('Error fetching works:', error);
            showToast('Error', 'Failed to load work details', 'error');
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

// Function to update the work
function updateWork(formData) {
    fetch('update_work.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Work updated successfully', 'success');
            document.querySelector('.modal-overlay')?.remove();
            refreshWorksTable();
        } else {
            showToast('Error', data.message || 'Failed to update work', 'error');
            // Re-enable save button if there was an error
            const saveBtn = document.getElementById('saveWorkBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Save Changes';
            }
        }
    })
    .catch(error => {
        console.error('Error updating work:', error);
        showToast('Error', 'Failed to update work', 'error');
        // Re-enable save button if there was an error
        const saveBtn = document.getElementById('saveWorkBtn');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save Changes';
        }
    });
}

// Function to attach event listeners to action buttons
function attachWorkButtonEvents() {
    // Edit buttons
    document.querySelectorAll('.edit-work').forEach(button => {
        button.addEventListener('click', function() {
            const workId = this.getAttribute('data-id');
            showEditWorkModal(workId);
        });
    });
    
    // Delete buttons
    document.querySelectorAll('.delete-work').forEach(button => {
        button.addEventListener('click', function() {
            const workId = this.getAttribute('data-id');
            
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
                        <p style="margin-bottom: 20px; color: #555;">Are you sure you want to delete this work?</p>
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
                deleteWork(workId);
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

function deleteWork(workId) {
    fetch('delete_work.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${workId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Work deleted successfully', 'success');
            refreshWorksTable();
        } else {
            showToast('Error', data.message || 'Failed to delete work', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting work:', error);
        showToast('Error', 'Failed to delete work', 'error');
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', refreshWorksTable);


</script>

<?php include "includes/script-src.php";?>
</body>
</html>