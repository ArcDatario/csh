<?php
require_once 'auth_check.php';

// Redirect if already logged in
redirectToUserHomeIfLoggedIn();

// Rest of your index.php code
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Enterprises | Modern Cloth Printing</title>
   <link rel="icon" href="assets/images/tshirt.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
 <link rel="stylesheet" href="assets/css/style.css">
 <style>
        /* Ticket Search Modal Styles */
        .ticket-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .ticket-modal.show {
            display: flex !important;
        }
        .ticket-modal-content {
            background-color: var(--light);
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            animation: fadeIn 0.3s ease-out;
        }
        
        .ticket-modal-header {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .ticket-modal-header h2 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .ticket-modal-header p {
            color: var(--text);
            font-size: 14px;
        }
        
        .ticket-input-group {
            display: flex;
            margin-bottom: 20px;
        }
        
        .ticket-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 5px 0 0 5px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .ticket-input:focus {
            border-color: var(--primary);
        }
        
        .ticket-search-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0 20px;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .ticket-search-btn:hover {
            background-color: #3a7bd5;
        }
        
        /* Order Results Modal */
        .order-results-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1001;
            justify-content: center;
            align-items: center;
        }
        
        .order-results-content {
            background-color: var(--light);
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            animation: fadeIn 0.3s ease-out;
        }
        
        .order-results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        
        .order-results-header h3 {
            color: var(--primary);
            margin: 0;
        }
        
        .close-results-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--text);
        }
        
        .order-result-card {
            display: flex;
            gap: 20px;
            align-items: center;
            padding: 15px;
            border-radius: 8px;
            background-color: var(--secondary);
            margin-bottom: 15px;
        }
        
        .order-result-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .order-result-details {
            flex: 1;
        }
        
        .order-result-title {
            font-weight: bold;
            color: var(--text);
            margin-bottom: 5px;
        }
        
        .order-result-meta {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: var(--text);
        }
        
        .order-result-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .order-result-status.status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .order-result-status.status-approved {
            background-color: #e2f0d9;
            color: #27632a;
        }
        .order-result-status.status-to-pick-up {
            background-color: #fff3cd;
            color: #856404;
        }
        .order-result-status.status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .order-result-status.status-to_ship {
            background-color: #ffe5b4;
            color: #a86b00;
        }
        .order-result-status.status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .order-result-status.status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }



        /* style for the toast */
        /* Toast Notification Styles */
.toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background-color: #4a8fe7;
    color: white;
    padding: 15px 25px;
    border-radius: 5px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.toast.show {
    transform: translateY(0);
    opacity: 1;
}

.toast i {
    font-size: 20px;
}
    </style>
</head>
<body>
    <!-- Loader -->
    <div class="loader-container" id="loader">
        <div class="loader">
            <i class="fas fa-tshirt t-shirt"></i>
        </div>
        <div class="loader-text">Loading</div>
    </div>

    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="#" class="logo">
                 <img src="assets/images/icons/tshirt.png" alt="" style="height: 45px; width: 35px;">
                CSH
            </a>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <nav id="nav">
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                    <li><a href="#contact">Contact</a></li>   
                    <li><a href="#" id="openTicketModal">Ticket</a></li>                     
                    <li><a href="login">Log in</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Ticket Modal (Popup) -->
    <div class="ticket-modal" id="ticketModal">
        <div class="ticket-modal-content" id="ticketModalContent">
            <div class="ticket-modal-header">
                <h2>Track Your Order</h2>
                <p>Enter your ticket number to check your order status</p>
            </div>
            <div class="ticket-input-group">
                <input type="text" class="ticket-input" id="ticketNumberInput" placeholder="Enter ticket number">
                <button class="ticket-search-btn" id="ticketSearchBtn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <p style="text-align: center; color: var(--text); font-size: 13px;">
                Example: 692559, 629538, 457944
            </p>
        </div>
    </div>

    <!-- Order Results Modal -->
    <div class="order-results-modal" id="orderResultsModal">
        <div class="order-results-content" id="orderResultsContent">
            <div class="order-results-header">
                <h3>Order Details</h3>
                <button class="close-results-btn" id="closeResultsBtn">&times;</button>
            </div>
            <div id="orderResultsContainer">
                <!-- Order results will be displayed here -->
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-container">
            <br>
            <div class="hero-content">
                <h1>Revolutionary Cloth Printing Solutions</h1>
                <p>Transform your ideas into wearable art with our cutting-edge printing technology. From small batches to large orders, we deliver quality and creativity.</p>
                <a href="signup" class="cta-button">Get Started</a>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1529374255404-311a2a4f1fd9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" alt="Custom Printed T-shirts">
            </div>
        </div>
    </section>
    <div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <span id="toastMessage">Message sent successfully!</span>
</div>
    <!-- Services Section -->
    <section class="services" id="services">
        <div class="section-title">
            <h2>Our Printing Services</h2>
            <p>Explore our wide range of cloth printing techniques to find the perfect solution for your needs.</p>
        </div>
        <div class="services-grid">
        <?php
require 'db_connection.php'; // Include your database connection

// Fetch data from the services table
$query = "SELECT service_name, description, image FROM services"; // Adjust column names as per your table
$result = $conn->query($query);

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()): ?>
        <div class="service-card">
            <div class="service-image">
                <img src="admin/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['service_name']); ?>">
            </div>
            <div class="service-content">
                <h3><?php echo htmlspecialchars($row['service_name']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
            </div>
        </div>
    <?php endwhile;
else: ?>
    <p>No services available at the moment.</p>
<?php endif; ?>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery" id="gallery">
        <div class="section-title">
            <h2>Our Work</h2>
            <p>Browse through some of our recent projects and get inspired for your next design.</p>
        </div>
        <div class="gallery-grid">
        <?php
require 'db_connection.php'; // Include your database connection

// Fetch data from the work table
$query = "SELECT work_name, image FROM work"; // Adjust column names as per your table
$result = $conn->query($query);

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()): ?>
        <div class="gallery-item">
            <img src="admin/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['work_name']); ?>">
            <div class="gallery-overlay">
                <h3><?php echo htmlspecialchars($row['work_name']); ?></h3>
            </div>
        </div>
    <?php endwhile;
else: ?>
    <p>No work available at the moment.</p>
<?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="section-title">
            <h2>Get In Touch</h2>
            <p>Ready to start your printing project? Contact us today for a free quote.</p>
        </div>
        <div class="contact-container">
            <div class="contact-info">
                <h3>Let's Create Something Amazing</h3>
                <p>Our team is ready to help you bring your designs to life. Whether you need advice on printing techniques or want to discuss your project, we're here to help.</p>
                <div class="contact-details">
                    <div class="contact-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>1815 DRT HIGHWAY TARCAN BALIUAG BULACAN</span>
                    </div>
                    <div class="contact-detail">
                        <i class="fas fa-phone-alt"></i>
                        <span>(63) 967-5827-336</span>
                    </div>
                    <div class="contact-detail">
                        <i class="fas fa-envelope"></i>
                        <span>cshenterprises888@gmail.com</span>
                    </div>
                </div>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="contact-form">
    <form id="contactForm" action="send_email.php" method="POST">
        <div class="form-group">
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone">
        </div>
        <div class="form-group">
            <label for="message">Project Details</label>
            <textarea id="message" name="message" required></textarea>
        </div>
        <button type="submit" class="submit-btn">Send Message</button>
    </form>
</div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-col">
                <h3>CSH Enterprises</h3>
                <p>Innovative cloth printing solutions for businesses, organizations, and individuals.</p>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Services</h3>
                <ul>
                    <li><a href="#">Screen Printing</a></li>
                    <li><a href="#">DTG Printing</a></li>
                    <li><a href="#">Sublimation</a></li>
                    <li><a href="#">Embroidery</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Connect</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2025 CSH Enterprises. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
        
    </script>

 <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal logic
            const ticketModal = document.getElementById('ticketModal');
            const orderResultsModal = document.getElementById('orderResultsModal');
            const ticketSearchBtn = document.getElementById('ticketSearchBtn');
            const ticketNumberInput = document.getElementById('ticketNumberInput');
            const orderResultsContainer = document.getElementById('orderResultsContainer');
            const closeResultsBtn = document.getElementById('closeResultsBtn');

            // Show ticket modal as popup on page load
            setTimeout(() => {
                ticketModal.classList.add('show');
            }, 500);

            // Search for ticket
            ticketSearchBtn.addEventListener('click', searchTicket);
            ticketNumberInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchTicket();
                }
            });

            function searchTicket() {
                const ticketNumber = ticketNumberInput.value.trim();
                if (!ticketNumber) {
                    alert('Please enter a ticket number');
                    return;
                }
                // AJAX request to search for ticket
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'search_ticket.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        let response;
                        try {
                            response = JSON.parse(this.responseText);
                        } catch (e) {
                            alert('Invalid server response');
                            return;
                        }
                        if (response.success) {
                            // Hide ticket modal
                            ticketModal.classList.remove('show');
                            // Display results in order results modal
                            displayOrderResults(response.orders);
                            // Show order results modal
                            orderResultsModal.style.display = 'flex';
                        } else {
                            alert(response.message || 'No orders found with that ticket number');
                        }
                    } else {
                        alert('Error searching for ticket');
                    }
                };
                xhr.send('ticket=' + encodeURIComponent(ticketNumber));
            }

            function displayOrderResults(orders) {
                orderResultsContainer.innerHTML = '';
                if (!orders || orders.length === 0) {
                    orderResultsContainer.innerHTML = '<p>No orders found</p>';
                    return;
                }
                orders.forEach(order => {
                    const orderCard = document.createElement('div');
                    orderCard.className = 'order-result-card';

                    // Normalize status for class
                    let statusKey = order.status.toLowerCase().replace(/\s+/g, '-').replace('_', '-');
                    if (statusKey === 'to-pick-up') statusKey = 'to-pick-up';
                    if (statusKey === 'to-ship') statusKey = 'to_ship';

                    // List of all statuses for class assignment
                    const statusClassMap = {
                        'pending': 'status-pending',
                        'approved': 'status-approved',
                        'to-pick-up': 'status-to-pick-up',
                        'processing': 'status-processing',
                        'to_ship': 'status-to_ship',
                        'completed': 'status-completed',
                        'rejected': 'status-rejected'
                    };
                    const statusClass = statusClassMap[statusKey] || 'status-pending';

                    orderCard.innerHTML = `
                        <img src="user/${order.design_file}" alt="Design" class="order-result-image">
                        <div class="order-result-details">
                            <div class="order-result-title">${order.print_type}</div>
                            <div class="order-result-meta">
                                <span>Quantity: ${order.quantity}</span>
                            </div>
                            <div class="order-result-status ${statusClass}">${order.status}</div>
                        </div>
                    `;
                    orderResultsContainer.appendChild(orderCard);
                });
            }

            // Close results modal
            closeResultsBtn.addEventListener('click', function() {
                orderResultsModal.style.display = 'none';
            });

            // Close modals when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === ticketModal) {
                    ticketModal.classList.remove('show');
                }
                if (e.target === orderResultsModal) {
                    orderResultsModal.style.display = 'none';
                }
            });

            // Enhanced: Close modal when clicking outside modal content (works for mobile and desktop)
            document.getElementById('ticketModal').addEventListener('click', function(e) {
                const modalContent = document.getElementById('ticketModalContent');
                if (!modalContent.contains(e.target)) {
                    this.classList.remove('show');
                }
            });

            document.getElementById('orderResultsModal').addEventListener('click', function(e) {
                const modalContent = document.getElementById('orderResultsContent');
                if (!modalContent.contains(e.target)) {
                    this.style.display = 'none';
                }
            });

            // Show modal when clicking "Ticket" in nav
            const openTicketModal = document.getElementById('openTicketModal');
            if (openTicketModal) {
                openTicketModal.addEventListener('click', function(e) {
                    e.preventDefault();
                    ticketModal.classList.add('show');
                    ticketNumberInput.focus();
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('nav ul li a');
    
    function setActiveLink() {
        let index = sections.length;
        
        // Check which section is in view
        while(--index && window.scrollY + 100 < sections[index].offsetTop) {}
        
        // Remove active class from all links
        navLinks.forEach(link => link.classList.remove('active'));
        
        // Add active class to corresponding link
        if (sections[index]) {
            const id = sections[index].getAttribute('id');
            document.querySelector(`nav ul li a[href="#${id}"]`).classList.add('active');
        }
    }
    
    // Run on page load and scroll
    setActiveLink();
    window.addEventListener('scroll', setActiveLink);
    
    // Smooth scrolling for navigation
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId.startsWith('#')) {
                const targetSection = document.querySelector(targetId);
                window.scrollTo({
                    top: targetSection.offsetTop - 80,
                    behavior: 'smooth'
                });
            } else {
                window.location.href = targetId;
            }
        });
    });
});
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal logic
            const ticketModal = document.getElementById('ticketModal');
            const orderResultsModal = document.getElementById('orderResultsModal');
            const ticketSearchBtn = document.getElementById('ticketSearchBtn');
            const ticketNumberInput = document.getElementById('ticketNumberInput');
            const orderResultsContainer = document.getElementById('orderResultsContainer');
            const closeResultsBtn = document.getElementById('closeResultsBtn');

            // Show ticket modal as popup on page load
            setTimeout(() => {
                ticketModal.classList.add('show');
            }, 500);

            // Search for ticket
            ticketSearchBtn.addEventListener('click', searchTicket);
            ticketNumberInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchTicket();
                }
            });

            function searchTicket() {
                const ticketNumber = ticketNumberInput.value.trim();
                if (!ticketNumber) {
                    alert('Please enter a ticket number');
                    return;
                }
                // AJAX request to search for ticket
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'search_ticket.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        let response;
                        try {
                            response = JSON.parse(this.responseText);
                        } catch (e) {
                            alert('Invalid server response');
                            return;
                        }
                        if (response.success) {
                            // Hide ticket modal
                            ticketModal.classList.remove('show');
                            // Display results in order results modal
                            displayOrderResults(response.orders);
                            // Show order results modal
                            orderResultsModal.style.display = 'flex';
                        } else {
                            alert(response.message || 'No orders found with that ticket number');
                        }
                    } else {
                        alert('Error searching for ticket');
                    }
                };
                xhr.send('ticket=' + encodeURIComponent(ticketNumber));
            }

            function displayOrderResults(orders) {
                orderResultsContainer.innerHTML = '';
                if (!orders || orders.length === 0) {
                    orderResultsContainer.innerHTML = '<p>No orders found</p>';
                    return;
                }
                orders.forEach(order => {
                    const orderCard = document.createElement('div');
                    orderCard.className = 'order-result-card';

                    // Normalize status for class
                    let statusKey = order.status.toLowerCase().replace(/\s+/g, '-').replace('_', '-');
                    if (statusKey === 'to-pick-up') statusKey = 'to-pick-up';
                    if (statusKey === 'to-ship') statusKey = 'to_ship';

                    // List of all statuses for class assignment
                    const statusClassMap = {
                        'pending': 'status-pending',
                        'approved': 'status-approved',
                        'to-pick-up': 'status-to-pick-up',
                        'processing': 'status-processing',
                        'to_ship': 'status-to_ship',
                        'completed': 'status-completed',
                        'rejected': 'status-rejected'
                    };
                    const statusClass = statusClassMap[statusKey] || 'status-pending';

                    orderCard.innerHTML = `
                        <img src="user/${order.design_file}" alt="Design" class="order-result-image">
                        <div class="order-result-details">
                            <div class="order-result-title">${order.print_type}</div>
                            <div class="order-result-meta">
                                <span>Quantity: ${order.quantity}</span>
                            </div>
                            <div class="order-result-status ${statusClass}">${order.status}</div>
                        </div>
                    `;
                    orderResultsContainer.appendChild(orderCard);
                });
            }

            // Close results modal
            closeResultsBtn.addEventListener('click', function() {
                orderResultsModal.style.display = 'none';
            });

            // Close modals when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === ticketModal) {
                    ticketModal.classList.remove('show');
                }
                if (e.target === orderResultsModal) {
                    orderResultsModal.style.display = 'none';
                }
            });

            // Enhanced: Close modal when clicking outside modal content (works for mobile and desktop)
            document.getElementById('ticketModal').addEventListener('click', function(e) {
                const modalContent = document.getElementById('ticketModalContent');
                if (!modalContent.contains(e.target)) {
                    this.classList.remove('show');
                }
            });

            document.getElementById('orderResultsModal').addEventListener('click', function(e) {
                const modalContent = document.getElementById('orderResultsContent');
                if (!modalContent.contains(e.target)) {
                    this.style.display = 'none';
                }
            });

            // Show modal when clicking "Ticket" in nav
            const openTicketModal = document.getElementById('openTicketModal');
            if (openTicketModal) {
                openTicketModal.addEventListener('click', function(e) {
                    e.preventDefault();
                    ticketModal.classList.add('show');
                    ticketNumberInput.focus();
                });
            }
        });
    </script>
</body>
</html>