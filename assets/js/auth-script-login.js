// Password Toggle
const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');

togglePassword.addEventListener('click', function() {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.classList.toggle('fa-eye-slash');
});

// Form Submission
const loginForm = document.getElementById('loginForm');
const loginButton = document.getElementById('loginButton');

loginForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading state
    loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
    loginButton.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        // Success - redirect to dashboard
        window.location.href = 'user/home';
    }, 1500);
});

// Input focus effects
const inputs = document.querySelectorAll('input');
inputs.forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.querySelector('label').style.color = 'var(--primary)';
    });
    
    input.addEventListener('blur', function() {
        this.parentElement.querySelector('label').style.color = '';
    });
});

window.addEventListener('load', function() {
setTimeout(function() {
const loader = document.getElementById('loader');
loader.style.opacity = '0';
setTimeout(function() {
    loader.style.display = 'none';
}, 400);
}, 800);
});


//this is for the toast

const toastButton = document.getElementById('ToastButton');
const toastContainer = document.getElementById('toastContainer');

const toastTypes = [
    { type: 'success', icon: 'fa-check', text: 'Action completed' },
    { type: 'error', icon: 'fa-times', text: 'Error occurred' },
    { type: 'warning', icon: 'fa-exclamation', text: 'Warning notice' },
    { type: 'info', icon: 'fa-info', text: 'Information update' }
];

function showToast(type, message) {
    const toast = document.createElement('div');
    const toastType = toastTypes.find(t => t.type === type) || toastTypes[3];
    
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas ${toastType.icon} toast-icon"></i>
        <span>${message}</span>
        <button class="toast-close">&times;</button>
    `;
    
    toastContainer.prepend(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Close button
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.remove();
    });
    
    // Auto-dismiss after 3.5 seconds
    setTimeout(() => toast.remove(), 3500);
}

// Demo button - cycles through toast types
toastButton.addEventListener('click', () => {
    const randomType = toastTypes[Math.floor(Math.random() * toastTypes.length)];
    showToast(randomType.type, randomType.text);
});
