<?php
session_start();

function redirectIfNotLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login');
        exit();
    }
    
    // Require database connection
    require '../db_connection.php';
    
    // Fetch user data (both address and image)
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT address, image FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Store both address and image in session
        $_SESSION['address'] = $user['address'] ?? '';
        $_SESSION['image'] = $user['image'] ?? '';
    } else {
        $_SESSION['address'] = '';
        $_SESSION['image'] = '';
    }
    
    $stmt->close();
    $conn->close();
}

// Call the function immediately
redirectIfNotLoggedIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSH Enterprises | Modern Cloth Printing</title>
   <link rel="icon" href="../assets/images/icons/shirt.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
 <link rel="stylesheet" href="../assets/css/style.css">
 <link rel="stylesheet" href="../assets/css/profile-modal.css">

 <style>
    .toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 5px;
    color: white;
    font-weight: 500;
    z-index: 10000;
    opacity: 0;
    transform: translateX(100%);
    transition: opacity 0.3s, transform 0.3s;
    max-width: 300px;
}

.toast.show {
    opacity: 1;
    transform: translateX(0);
}

.toast.success {
    background-color: #4CAF50;
    border-left: 4px solid #2E7D32;
}

.toast.error {
    background-color: #F44336;
    border-left: 4px solid #C62828;
}

.toast.info {
    background-color: #2196F3;
    border-left: 4px solid #1565C0;
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
<!-- Header -->
<header>
    <div class="header-container">
        <a href="#" class="logo">
             <img src="../csh-logo.png" alt="" style="height: 55px; width: 100%;">
        </a>

        <!-- Mobile Notification Bell (add this before the mobile menu button) -->
        <a href="#" class="mobile-notification-icon" id="mobileNotificationToggle">
            <span class="bell-icon">🔔</span>
            <span class="notification-count" id="mobileNotificationCount">0</span>
        </a>

        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
        
        <?php include "includes/navbar.php";?>
    </div>
</header>

<style>
/* Mobile Notification Icon Styles */
.mobile-notification-icon {
    display: none;
    position: relative;
    align-items: center;
    padding: 8px 12px;
    text-decoration: none;
    color: inherit;
    font-size: 20px;
}

.mobile-notification-icon .bell-icon {
    font-size: 24px;
}

.mobile-notification-icon .notification-count {
    position: absolute;
    top: 0;
    right: 5px;
    background-color: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
}

/* Show mobile notification icon on smaller screens */
@media (max-width: 768px) {
    .mobile-notification-icon {
        display: flex;
        margin-right: 10px;
    }
    
    /* Adjust header container for better spacing */
    .header-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    /* Group notification and menu button together */
    .header-container > a.mobile-notification-icon,
    .header-container > .mobile-menu-btn {
        order: 2;
    }
    
    .header-container > .logo {
        order: 1;
        flex: 1;
    }
    
    .header-container > nav {
        order: 3;
    }
}

/* Adjust notification menu positioning for mobile */
@media (max-width: 768px) {
    .notification-menu {
        position: fixed;
        top: 70px;
        right: 10px;
        left: 10px;
        width: auto;
        max-width: none;
    }
}
</style>

    <!-- Hero Section -->
    <section class="hero" id="home" style="background-image: url('../assets/images/csh-hero.jpeg');">
        <div class="hero-container" >
            <br>
            <div class="hero-content">
                <h1>CSH Enterprises Printing Solutions</h1>
                <p>Transform your ideas into wearable art with our cutting-edge printing technology. From small batches to large orders, we deliver quality and creativity.</p>
                <a href="quote" class="cta-button">Get Quote</a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="section-title">
            <h2>Our Printing Services</h2>
            <p>Explore our wide range of cloth printing techniques to find the perfect solution for your needs.</p>
        </div>
        <div class="services-grid">
       <?php
require '../db_connection.php'; // Include your database connection

// Fetch data from the services table
$query = "SELECT service_name, description, image FROM services"; // Adjust column names as per your table
$result = $conn->query($query);

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()): 
        // Clean up any escaped characters
        $service_name = stripslashes(htmlspecialchars($row['service_name']));
        $description = stripslashes(htmlspecialchars($row['description']));
        $image = htmlspecialchars($row['image']);
        ?>
        <div class="service-card">
            <div class="service-image">
                <img src="../admin/<?php echo $image; ?>" alt="<?php echo $service_name; ?>">
            </div>
            <div class="service-content">
                <h3><?php echo $service_name; ?></h3>
                <p><?php echo $description; ?></p>
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
require '../db_connection.php'; // Include your database connection

// Fetch data from the work table
$query = "SELECT work_name, image FROM work"; // Adjust column names as per your table
$result = $conn->query($query);

if ($result->num_rows > 0):
    while ($row = $result->fetch_assoc()): ?>
        <div class="gallery-item">
            <img src="../admin/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['work_name']); ?>">
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
                        <span>cshenterprises@gmail.com</span>
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
                <form id="contactForm">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" required>
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
                    <li><a href="#contact">Contact Us</a></li>
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

    <script src="../assets/js/script.js"></script>
        
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
</body>
</html>