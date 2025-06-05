// Loader
window.addEventListener('load', function() {
    setTimeout(function() {
        const loader = document.getElementById('loader');
        loader.style.opacity = '0';
        setTimeout(function() {
            loader.style.display = 'none';
        }, 400);
    }, 800);
});

// Mobile Menu Toggle
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const nav = document.getElementById('nav');

mobileMenuBtn.addEventListener('click', function() {
    nav.classList.toggle('active');
});

// Smooth Scrolling for Anchor Links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (nav.classList.contains('active')) {
            nav.classList.remove('active');
        }

        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Form Submission
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Show loading state on button
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success toast
                    toastMessage.textContent = data.message;
                    toast.classList.add('show');
                    
                    // Reset form
                    contactForm.reset();
                    
                    // Hide toast after 3 seconds
                    setTimeout(() => {
                        toast.classList.remove('show');
                    }, 3000);
                } else {
                    // Show error message
                    alert(data.message || 'Error sending message');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the message');
            })
            .finally(() => {
                // Reset button state
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });
    }
});

// Animation on Scroll
const animateOnScroll = function() {
    const elements = document.querySelectorAll('.service-card, .gallery-item, .contact-form');
    
    elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.3;
        
        if (elementPosition < screenPosition) {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }
    });
};

// Set initial state for animated elements
document.querySelectorAll('.service-card, .gallery-item, .contact-form').forEach(element => {
    element.style.opacity = '0';
    element.style.transform = 'translateY(20px)';
    element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
});

window.addEventListener('scroll', animateOnScroll);
// Trigger once on page load in case elements are already in view
animateOnScroll();



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



// this javascript is for the quote modal

