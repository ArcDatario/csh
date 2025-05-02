
<?php
// No PHP logic at the top, as this is a frontend file
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email/Phone Verification</title>
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
        .verification-container {
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
        #codeGroup, #passwordGroup {
            display: none;
        }
        .code-inputs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }
        .code-input {
            width: 100%;
            max-width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .password-strength {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            background: #dc3545;
            transition: width 0.3s;
        }
        .resend-text {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        #signupButton {
            height: 48px;
            border-radius: 8px;
            font-weight: 500;
            background-color: var(--primary-color);
            border: none;
        }
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
        .toast-info .toast-header {
            color: #17a2b8;
        }
        @media (max-width: 576px) {
            .verification-container {
                padding: 20px;
            }
            .code-input {
                height: 50px;
                font-size: 20px;
                max-width: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <h2 class="form-title">Email/Phone Verification</h2>
            <form id="signupForm">
                <div class="mb-3" id="emailPhoneGroup">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control mb-2" id="email" name="email" placeholder="Enter your email">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="09XXXXXXXXX">
                    <div class="form-text mt-1">We'll send a verification code to your email or phone</div>
                </div>
                <div class="mb-3" id="codeGroup">
                    <label class="form-label">Verification Code</label>
                    <div class="code-inputs">
                        <input type="text" class="code-input" maxlength="1" data-index="1" inputmode="numeric">
                        <input type="text" class="code-input" maxlength="1" data-index="2" inputmode="numeric">
                        <input type="text" class="code-input" maxlength="1" data-index="3" inputmode="numeric">
                        <input type="text" class="code-input" maxlength="1" data-index="4" inputmode="numeric">
                        <input type="text" class="code-input" maxlength="1" data-index="5" inputmode="numeric">
                        <input type="text" class="code-input" maxlength="1" data-index="6" inputmode="numeric">
                    </div>
                    <input type="hidden" id="verificationCode" name="verificationCode">
                    <div class="resend-text">
                        Didn't receive code? <a href="#" id="resendCode">Resend</a>
                    </div>
                </div>
                <div class="mb-3" id="passwordGroup">
    <label for="username" class="form-label">Username</label>
    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username">

    <label for="address" class="form-label mt-3">Address</label>
    <textarea class="form-control" id="address" name="address" placeholder="Enter your address"></textarea>

    <label for="password" class="form-label mt-3">Create Password</label>
    <input type="password" class="form-control" id="password" name="password" placeholder="At least 8 characters">
    <div class="password-strength mt-2">
        <div class="password-strength-bar" id="passwordStrength"></div>
    </div>
    <label for="confirmPassword" class="form-label mt-3">Confirm Password</label>
    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Re-enter your password">
</div>
                <button type="submit" id="signupButton" class="btn btn-primary w-100 mt-3">
                    <span id="buttonIcon" class="me-2"><i class="fas fa-sign-in-alt"></i></span>
                    <span id="buttonText">Next</span>
                    <span id="buttonSpinner" class="spinner-border spinner-border-sm ms-2" style="display:none;"></span>
                </button>
                <div class="text-center mt-3">
                    Already have an account? <a href="login">Login</a>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal for choosing delivery method -->
    <div class="modal fade" id="chooseMethodModal" tabindex="-1" aria-labelledby="chooseMethodModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="chooseMethodModalLabel">Choose Delivery Method</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <p id="chooseMethodText">Where would you like to receive your verification code?</p>
            <button type="button" class="btn btn-primary w-100 mb-2" id="sendViaEmail"><i class="fas fa-envelope me-2"></i>Email</button>
            <button type="button" class="btn btn-success w-100" id="sendViaSMS"><i class="fas fa-sms me-2"></i>SMS</button>
          </div>
        </div>
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
        $(document).ready(function () {
    // Hide code/password groups initially
    $('#codeGroup').hide();
    $('#passwordGroup').hide();

    // Show modal on first Next
    $('#signupForm').on('submit', function (e) {
        e.preventDefault();
        if ($('#emailPhoneGroup').is(':visible')) {
            // Validate email or phone
            let email = $('#email').val().trim();
            let phone = $('#phone').val().trim();
            if (!email && !phone) {
                showToast('Error', 'Please enter your email or phone number.', 'error');
                return;
            }
            if (email && !validateEmail(email)) {
                showToast('Error', 'Please enter a valid email address.', 'error');
                return;
            }
            // More flexible phone validation
            if (phone && !/^(\+?63|0)9\d{9}$/.test(phone)) {
                showToast('Error', 'Please enter a valid phone number (09XXXXXXXXX or +639XXXXXXXXX).', 'error');
                return;
            }
            
            // Enable/disable modal buttons based on input
            if (email && phone) {
                $('#sendViaEmail').prop('disabled', false);
                $('#sendViaSMS').prop('disabled', false);
                $('#chooseMethodText').text('Where would you like to receive your verification code?');
            } else if (email) {
                $('#sendViaEmail').prop('disabled', false);
                $('#sendViaSMS').prop('disabled', true);
                $('#chooseMethodText').text('A verification code will be sent to your email.');
            } else if (phone) {
                $('#sendViaEmail').prop('disabled', true);
                $('#sendViaSMS').prop('disabled', false);
                $('#chooseMethodText').text('A verification code will be sent via SMS to your phone.');
            }
            $('#chooseMethodModal').modal('show');
        } else if ($('#codeGroup').is(':visible')) {
            // Collect code and verify
            let code = '';
            $('.code-input').each(function () {
                code += $(this).val();
            });
            if (code.length !== 6) {
                showToast('Error', 'Please enter the 6-digit verification code.', 'error');
                return;
            }
            $('#verificationCode').val(code);
            
            // Verify the code via AJAX
            verifyCode(code);
        } else if ($('#passwordGroup').is(':visible')) {
            // Final registration step
            registerUser();
        }
    });

    // Modal button handlers
    $('#sendViaEmail').on('click', function () {
        sendVerification('email');
        $('#chooseMethodModal').modal('hide');
    });
    
    $('#sendViaSMS').on('click', function () {
        sendVerification('sms');
        $('#chooseMethodModal').modal('hide');
    });

    // Resend code
    $('#resendCode').on('click', function (e) {
        e.preventDefault();
        let method = $('#codeGroup').data('method') || 'email';
        sendVerification(method, true);
    });

    // Code input auto-focus
    $('.code-input').on('input', function () {
        let $this = $(this);
        if ($this.val().length === 1) {
            $this.next('.code-input').focus();
        }
    }).on('keydown', function (e) {
        if (e.key === 'Backspace' && !$(this).val()) {
            $(this).prev('.code-input').focus();
        }
    });

    // Password strength bar
    $('#password').on('input', function () {
        let val = $(this).val();
        let strength = getPasswordStrength(val);
        let $bar = $('#passwordStrength');
        $bar.css('width', strength + '%');
        $bar.css('background', strength < 50 ? '#dc3545' : (strength < 80 ? '#ffc107' : '#28a745'));
    });

    function sendVerification(method, resend = false) {
        let email = $('#email').val().trim();
        let phone = $('#phone').val().trim();
        
        // Normalize phone number before sending
        if (phone) {
            phone = phone.replace(/^\+/, '').replace(/^0/, '63');
        }
        
        $('#signupButton #buttonSpinner').show();
        $('#signupButton').prop('disabled', true);
        
        $.ajax({
            url: 'send_verification.php',
            method: 'POST',
            data: {
                email: email,
                phone: phone,
                method: method,
                resend: resend ? 1 : undefined
            },
            dataType: 'json',
            success: function (resp) {
                $('#signupButton #buttonSpinner').hide();
                $('#signupButton').prop('disabled', false);
                if (resp.success) {
                    showToast('Success', resp.message, 'success');
                    $('#emailPhoneGroup').hide();
                    $('#codeGroup').show().data('method', method);
                    $('#signupButton #buttonText').text('Verify Code');
                    // Auto-focus first code input
                    $('.code-input').first().focus();
                } else {
                    showToast('Error', resp.message, 'error');
                    // Show the email/phone group again if failed
                    $('#emailPhoneGroup').show();
                    $('#codeGroup').hide();
                }
            },
            error: function (xhr, status, error) {
    $('#signupButton #buttonSpinner').hide();
    $('#signupButton').prop('disabled', false);
    showToast('Error', 'Failed to send verification code. Please try again. ' + error, 'error');
}
        });
    }

    function verifyCode(code) {
        $('#signupButton #buttonSpinner').show();
        $('#signupButton').prop('disabled', true);
        
        $.ajax({
            url: 'verify_code.php', // You'll need to create this endpoint
            method: 'POST',
            data: { code: code },
            dataType: 'json',
            success: function (resp) {
                $('#signupButton #buttonSpinner').hide();
                $('#signupButton').prop('disabled', false);
                if (resp.success) {
                    showToast('Success', 'Code verified successfully!', 'success');
                    $('#codeGroup').hide();
                    $('#passwordGroup').show();
                    $('#signupButton #buttonText').text('Sign Up');
                } else {
                    showToast('Error', resp.message || 'Invalid verification code', 'error');
                    // Clear code inputs
                    $('.code-input').val('');
                    $('.code-input').first().focus();
                }
            },
            error: function () {
                $('#signupButton #buttonSpinner').hide();
                $('#signupButton').prop('disabled', false);
                showToast('Error', 'Failed to verify code. Please try again.', 'error');
            }
        });
    }

    function registerUser() {
    let email = $('#email').val().trim();
    let phone = $('#phone').val().trim();
    let username = $('#username').val().trim();
    let address = $('#address').val().trim();
    let password = $('#password').val();
    let confirmPassword = $('#confirmPassword').val();

    if (password !== confirmPassword) {
        showToast('Error', 'Passwords do not match', 'error');
        return;
    }
    if (!email) {
        showToast('Error', 'Email is required', 'error');
        return;
    }
    if (!phone) {
        showToast('Error', 'Phone number is required', 'error');
        return;
    }
    if (!username) {
        showToast('Error', 'Username is required', 'error');
        return;
    }
    if (!address) {
        showToast('Error', 'Address is required', 'error');
        return;
    }
    if (password.length < 8) {
        showToast('Error', 'Password must be at least 8 characters', 'error');
        return;
    }

    $('#signupButton #buttonSpinner').show();
    $('#signupButton').prop('disabled', true);

    $.ajax({
        url: 'functions/register_user.php',
        method: 'POST',
        data: {
            email: email,
            phone: phone,
            username: username,
            password: password,
            address: address
        },
        dataType: 'json',
        success: function (resp) {
            $('#signupButton #buttonSpinner').hide();
            $('#signupButton').prop('disabled', false);
            if (resp.success) {
                showToast('Success', 'Registration successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = 'user/home';
                }, 2000);
            } else {
                showToast('Error', resp.message || 'Registration failed', 'error');
            }
        },
        error: function () {
            $('#signupButton #buttonSpinner').hide();
            $('#signupButton').prop('disabled', false);
            showToast('Error', 'Registration failed. Please try again.', 'error');
        }
    });
}

    function showToast(title, message, type) {
        $('#toastTitle').text(title);
        $('#toastMessage').text(message);
        $('#toast').removeClass('toast-success toast-error toast-info');
        if (type === 'success') $('#toast').addClass('toast-success');
        else if (type === 'error') $('#toast').addClass('toast-error');
        else $('#toast').addClass('toast-info');
        let toast = new bootstrap.Toast(document.getElementById('toast'));
        toast.show();
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function getPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength += 30;
        if (/[A-Z]/.test(password)) strength += 20;
        if (/[a-z]/.test(password)) strength += 20;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[^A-Za-z0-9]/.test(password)) strength += 15;
        return Math.min(strength, 100);
    }
});
    </script>
</body>
</html>
