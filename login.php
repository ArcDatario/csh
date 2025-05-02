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
    <title>Login</title>
    <link rel="icon" href="assets/images/tshirt.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

   <style>
    :root {
    --primary-color: #4361ee;
    --secondary-color: #3f37c9;
    --light-color: #f8f9fa;
    --dark-color: #212529;
}

body {
    background: linear-gradient(135deg, #f8f9fa 0%, #a1c6ea 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.login-container {
    max-width: 500px;
    width: 100%;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.form-title {
    color: var(--primary-color);
    font-weight: 600;
    margin-bottom: 25px;
    text-align: center;
}

.form-control {
    height: 48px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
}

#forgotPasswordGroup {
    display: none;
}

#loginButton {
    height: 48px;
    border-radius: 8px;
    font-weight: 500;
    background-color: var(--primary-color);
    border: none;
}

/* Minimalist Toast */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    background: white;
}

.toast-header {
    border-bottom: none;
    background: transparent;
    padding: 12px 16px;
}

.toast-body {
    padding: 6px 16px;
}

.toast-success .toast-header {
    color: #28a745;
}

.toast-error .toast-header {
    color: #dc3545;
}

/* Email exists alert */
#emailExistAlert {
    display: none;
    background-color: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .login-container {
        padding: 20px;
    }
}
   </style>

</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="form-title">Login to Your Account</h2>
            
            <div id="emailExistAlert" class="alert alert-danger text-center mx-auto my-4 p-3" style="max-width: 400px; display: none;">
                Email Already Exist
            </div>

            <form id="loginForm">
                <div class="mb-3" id="loginGroup">
                    <label for="loginEmail" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="loginEmail" name="email" placeholder="Enter your email">
                    
                    <label for="loginPassword" class="form-label mt-3">Password</label>
                    <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Enter your password">
                    
                    <div class="d-flex justify-content-between mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                        <a href="forgot_password" >Forgot password?</a>
                    </div>
                </div>

                <div class="mb-3" id="forgotPasswordGroup">
                    <label for="forgotEmail" class="form-label">Enter your email to reset password</label>
                    <input type="email" class="form-control" id="forgotEmail" placeholder="Your email address">
                    <div class="form-text mt-1">We'll send a password reset link to this email</div>
                </div>

                <button type="submit" id="loginButton" class="btn btn-primary w-100 mt-3">
                    <span id="buttonIcon" class="me-2"><i class="fas fa-sign-in-alt"></i></span>
                    <span id="buttonText">Login</span>
                    <span id="buttonSpinner" class="spinner-border spinner-border-sm ms-2" style="display:none;"></span>
                </button>

                <div class="text-center mt-3">
                    Don't have an account? <a href="signup">Sign up</a>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
 $(document).ready(function() {
    const toast = new bootstrap.Toast(document.getElementById('toast'));
    let isForgotPassword = false;
    
    function showToast(type, message) {
        console.log(`[${type}] ${message}`);
        const toastEl = $('#toast');
        toastEl.removeClass('toast-success toast-error toast-info');
        toastEl.addClass(`toast-${type}`);
        $('#toastTitle').text(type.charAt(0).toUpperCase() + type.slice(1));
        $('#toastMessage').text(message);
        toast.show();
        setTimeout(() => toast.hide(), 4000);
    }

    function resetButtonState() {
        $('#loginButton').prop('disabled', false);
        $('#buttonSpinner').hide();
        $('#buttonIcon').show();
    }

    // Toggle between login and forgot password views
    $('#forgotPasswordLink').on('click', function(e) {
        e.preventDefault();
        isForgotPassword = true;
        $('#loginGroup').hide();
        $('#forgotPasswordGroup').show();
        $('#buttonText').text('Send Reset Link');
        $('#buttonIcon').html('<i class="fas fa-envelope"></i>');
        $('#emailExistAlert').hide();
    });

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const loginButton = $('#loginButton');
        const buttonText = $('#buttonText');
        const buttonSpinner = $('#buttonSpinner');
        const buttonIcon = $('#buttonIcon');
        
        // Show loading state
        loginButton.prop('disabled', true);
        buttonSpinner.show();
        buttonIcon.hide();

        if (!isForgotPassword) {
            // Handle login
            const email = $('#loginEmail').val().trim();
            const password = $('#loginPassword').val();
            const rememberMe = $('#rememberMe').is(':checked');
            
            // Basic validation
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showToast('error', 'Please enter a valid email address');
                return resetButtonState();
            }
            
            if (!password) {
                showToast('error', 'Please enter your password');
                return resetButtonState();
            }
            
            $.ajax({
                url: 'functions/login.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ 
                    email: email,
                    password: password,
                    remember: rememberMe ? '1' : '0'
                }),
                dataType: 'json',
                success: function(response) {
                    console.log('Login response:', response);
                    if (response.success) {
                        showToast('success', response.message || 'Login successful!');
                        setTimeout(() => {
                            window.location.href = response.redirect || 'user/home';
                        }, 1500);
                    } else {
                        showToast('error', response.message || 'Invalid email or password');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    showToast('error', 'Login failed. Please try again.');
                },
                complete: resetButtonState
            });
        } else {
            // Handle forgot password
            const email = $('#forgotEmail').val().trim();
            
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showToast('error', 'Please enter a valid email address');
                return resetButtonState();
            }
            
            buttonText.text('Sending link...');
            
            $.ajax({
                url: 'functions/forgot_password.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ email: email }),
                dataType: 'json',
                success: function(response) {
                    console.log('Forgot password response:', response);
                    if (response.success) {
                        showToast('success', response.message || 'Password reset link sent! Check your email.');
                        // Return to login view
                        setTimeout(() => {
                            isForgotPassword = false;
                            $('#forgotPasswordGroup').hide();
                            $('#loginGroup').show();
                            $('#buttonText').text('Login');
                            $('#buttonIcon').html('<i class="fas fa-sign-in-alt"></i>');
                        }, 2000);
                    } else {
                        showToast('error', response.message || 'Failed to send reset link');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    showToast('error', 'Failed to process request. Please try again.');
                },
                complete: resetButtonState
            });
        }
    });
});
    </script>
</body>
</html>