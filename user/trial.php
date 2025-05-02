<?php 
require '../auth_check.php';
redirectIfNotLoggedIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Enterprises | Modern Cloth Printing</title>
    <link rel="icon" href="../assets/images/tshirt.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/quote.css">
    <style>
     /* Toast Notification Styles */
.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 4px;
    background-color: #f8f9fa;
    color: #212529;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    font-size: 14px;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: 1000;
    border-left: 4px solid #28a745;
}

.toast.show {
    opacity: 1;
    transform: translateY(0);
}

.toast.error {
    border-left-color: #dc3545;
}
    </style>
</head>
<body>
    <!-- Loader -->
    <div class="loader-container" id="loader" style="margin-right:10% !important;">
        <div class="loader" style="margin-right:10% !important;">
            <i class="fas fa-tshirt t-shirt"></i>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="#" class="logo">
                 <img src="../assets/images/icons/tshirt.png" alt="" style="height: 45px; width: 35px;">
                CSH
            </a>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <nav id="nav">
                <ul>
                    <li><a href="home">Home</a></li>
                    <li><a href="services">Services</a></li>
                    <li><a href="gallery">Gallery</a></li>
                    <li><a href="contact">Contact</a></li>
                    <li><a href="quote" class="active">Quote</a></li>                     
                    <li><a href="logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="quotes-container">
            <!-- Sample Quote Card 1 -->
            <div class="quote-card animate__animated animate__fadeInUp">
               
    <img src="tshirt.png" alt="Design" class="card-image">
    <div class="card-content">
        <span class="card-status status-processing">Processing</span>
        <h3 class="card-title">Screen Printing</h3>
        <div class="card-details">
            <div class="card-detail">
                <span class="detail-label">Quantity</span>
                <span class="detail-value">150 pcs</span>
            </div>
            <div class="card-detail">
                <span class="detail-label">Print Type</span>
                <span class="detail-value">Direct to Film</span>
            </div>
        </div>
        <div class="card-actions">
            <button class="view-details-btn" onclick="openProcessModal('quote1')">
                <i class="fas fa-eye"></i> View
            </button>
            <span class="quote-date">Jun 15, 2023</span>
        </div>
    </div>
</div>
            
            <!-- Sample Quote Card 2 -->
            <div class="quote-card animate__animated animate__fadeInUp">
            <span class="card-status status-pending">Pending</span>
                <img src="tshirt.png" alt="Design" class="card-image">
                <div class="card-content">
                    <h3 class="card-title">Screen Printing</h3>
                    <div class="card-details">
                        <div class="card-detail">
                            <span class="detail-label">Quantity</span>
                            <span class="detail-value">200 pcs</span>
                        </div>
                        <div class="card-detail">
                            <span class="detail-label">Ticket #</span>
                            <span class="detail-value">ORD_02314</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="view-details-btn" onclick="openProcessModal('quote2')">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <span class="quote-date">Jun 18, 2023</span>
                    </div>
                </div>
            </div>
            
            <!-- Sample Quote Card 3 -->
            <div class="quote-card animate__animated animate__fadeInUp">
                <span class="card-status status-shipping">To Ship</span>
                <img src="tshirt.png" alt="Design" class="card-image">
                <div class="card-content">
                    <h3 class="card-title">Direct to Film</h3>
                    <div class="card-details">
                        <div class="card-detail">
                            <span class="detail-label">Quantity</span>
                            <span class="detail-value">500 pcs</span>
                        </div>
                        <div class="card-detail">
                            <span class="detail-label">Ticket #</span>
                            <span class="detail-value">ORD_02314</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="view-details-btn" onclick="openProcessModal('quote3')">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <span class="quote-date">Jun 10, 2023</span>
                    </div>
                </div>
            </div>
            
            <!-- Sample Quote Card 4 -->
            <div class="quote-card animate__animated animate__fadeInUp">
            
                <img src="tshirt.png" alt="Design" class="card-image">
                <span class="card-status status-completed">Completed</span>
                <div class="card-content">
                    <h3 class="card-title">Sports Team</h3>
                    <div class="card-details">
                        <div class="card-detail">
                            <span class="detail-label">Quantity</span>
                            <span class="detail-value">75 pcs</span>
                        </div>
                        <div class="card-detail">
                            <span class="detail-label">Print Type</span>
                            <span class="detail-value">Emboss Print</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="view-details-btn" onclick="openProcessModal('quote4')">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <span class="quote-date">May 28, 2023</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Process Steps Modal -->
    <div class="process-modal" id="processModal">
        <div class="process-content">
            <span class="close-process" onclick="closeProcessModal()">&times;</span>
            <h2 id="processTitle">Order Process Details</h2>
            
            <div class="process-steps" id="processSteps">
                <!-- Steps will be dynamically inserted here -->
            </div>
        </div>
    </div>

    <!-- Add Quote Button -->
    <button class="add-quote-btn pulse" id="addQuoteBtn">
        <i class="fas fa-plus-circle"></i> Add Quote
    </button>

    <!-- Quote Modal -->
    <div class="quote-modal" id="quoteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Quote</h2>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <form id="quoteForm" enctype="multipart/form-data" method="post">
    <div class="form-group">
        <label for="designFile">Upload Design</label>
        <div class="file-input-container">
            <div class="file-input-btn">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Click to upload design file</span>
                <input type="file" id="designFile" name="designFile" class="file-input" accept="image/*,.pdf,.ai,.psd" required>
            </div>
            <div id="file-name" class="file-name-display"></div>
        </div>
    </div>
    
    <div class="form-group" style="display: flex; gap: 10px;">
        <div style="flex: 7;">
            <label for="printType">Print Type</label>
            <select id="printType" name="printType" class="form-control" style="width: 100%;" required>
                <option value="">Select print type</option>
                <option value="Direct to Film Print">Direct to Film Print</option>
                <option value="Screen Printing">Screen Printing</option>
                <option value="Emboss Print">Emboss Print</option>
                <option value="Hi-Density Print">Hi-Density Print</option>
                <option value="Glitters Print">Glitters Print</option>
                <option value="Silk Screen Print">Silk Screen Print</option>
            </select>
        </div>
        <div style="flex: 3;">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" class="form-control" min="1" placeholder="Quantity" style="width: 100%; background-color:transparent;" required>
        </div>
    </div>
    
    
    <div class="form-group">
        <label for="note">Note</label>
        <textarea id="note" name="note" class="form-control note-input" rows="2" placeholder="Enter any additional notes or instructions" style="background-color:transparent;"></textarea>
    </div>
    
    <button type="submit" class="submit-btn">
        <i class="fas fa-paper-plane"></i> Submit Quote
    </button>
</form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        // Process steps data
        const processSteps = {
            quote1: [
                {title: "Design Approved", description: "Your design has been reviewed and approved for production", date: "Jun 15, 2023", completed: true},
                {title: "Material Preparation", description: "Fabric and printing materials are being prepared", date: "Jun 16, 2023", completed: true},
                {title: "Printing Process", description: "Your items are currently being printed", date: "Jun 17, 2023", completed: true},
                {title: "Quality Check", description: "Printed items undergoing quality control", date: "Expected Jun 19, 2023", completed: false},
                {title: "Packaging", description: "Items will be carefully packaged", date: "Expected Jun 20, 2023", completed: false},
                {title: "Shipping", description: "Your order will be shipped to you", date: "Expected Jun 21, 2023", completed: false}
            ],
            quote2: [
                {title: "Design Received", description: "We've received your design and are reviewing it", date: "Jun 18, 2023", completed: true},
                {title: "Design Approval", description: "Awaiting your approval of the design proof", date: "Expected Jun 19, 2023", completed: false},
                {title: "Material Preparation", description: "Materials will be prepared after approval", date: "Expected Jun 20, 2023", completed: false},
                {title: "Printing Process", description: "Your items will be printed", date: "Expected Jun 21-22, 2023", completed: false},
                {title: "Quality Check", description: "Printed items will undergo quality control", date: "Expected Jun 23, 2023", completed: false},
                {title: "Packaging & Shipping", description: "Items will be packaged and shipped", date: "Expected Jun 24, 2023", completed: false}
            ],
            quote3: [
                {title: "Design Approved", description: "Your design has been approved", date: "Jun 10, 2023", completed: true},
                {title: "Material Preparation", description: "All materials prepared", date: "Jun 11, 2023", completed: true},
                {title: "Printing Process", description: "Printing completed successfully", date: "Jun 12-14, 2023", completed: true},
                {title: "Quality Check", description: "Quality control passed", date: "Jun 15, 2023", completed: true},
                {title: "Packaging", description: "Items packaged securely", date: "Jun 16, 2023", completed: true},
                {title: "Shipping", description: "Shipped with tracking #TRK123456", date: "Jun 17, 2023", completed: true}
            ],
            quote4: [
                {title: "Design Approved", description: "Design approved after one revision", date: "May 28, 2023", completed: true},
                {title: "Material Preparation", description: "Special emboss materials prepared", date: "May 29, 2023", completed: true},
                {title: "Printing Process", description: "Emboss printing completed", date: "May 30-31, 2023", completed: true},
                {title: "Quality Check", description: "Quality control passed", date: "Jun 1, 2023", completed: true},
                {title: "Packaging", description: "Items packaged with care", date: "Jun 2, 2023", completed: true},
                {title: "Delivery", description: "Delivered to your address", date: "Jun 3, 2023", completed: true}
            ]
        };
        
        // Open process modal
        function openProcessModal(quoteId) {
            const modal = document.getElementById('processModal');
            const stepsContainer = document.getElementById('processSteps');
            const title = document.getElementById('processTitle');
            
            // Set title based on quote
            if (quoteId === 'quote1') title.textContent = 'Summer Vibes Collection - Process';
            else if (quoteId === 'quote2') title.textContent = 'Corporate Event - Process';
            else if (quoteId === 'quote3') title.textContent = 'Music Festival - Process';
            else if (quoteId === 'quote4') title.textContent = 'Sports Team - Process';
            
            // Clear previous steps
            stepsContainer.innerHTML = '';
            
            // Add steps
            processSteps[quoteId].forEach((step, index) => {
                const stepElement = document.createElement('div');
                stepElement.className = `step ${step.completed ? 'completed-step' : ''}`;
                
                stepElement.innerHTML = `
                    <div class="step-number">${index + 1}</div>
                    <div class="step-connector"></div>
                    <div class="step-content">
                        <div class="step-title">${step.title}</div>
                        <div class="step-description">${step.description}</div>
                        <div class="step-date">${step.date}</div>
                    </div>
                `;
                
                stepsContainer.appendChild(stepElement);
            });
            
            modal.style.display = 'flex';
        }
        
        // Close process modal
        function closeProcessModal() {
            document.getElementById('processModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
    const modal = document.getElementById('processModal');
    const quoteModal = document.getElementById('quoteModal');
    
    if (event.target === modal) {
        closeProcessModal();
    }
    if (event.target === quoteModal) {
        quoteModal.classList.remove('active');
    }
}

// Initialize quote modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const addQuoteBtn = document.getElementById('addQuoteBtn');
    const quoteModal = document.getElementById('quoteModal');
    const closeModal = document.getElementById('closeModal');
    const quoteForm = document.getElementById('quoteForm');
    
    addQuoteBtn.addEventListener('click', function() {
        quoteModal.classList.add('active');
    });
    
    closeModal.addEventListener('click', function() {
        quoteModal.classList.remove('active');
    });
    
    // Form submission handler
    quoteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
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
    </script>


</body>
</html>