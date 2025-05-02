<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link rel="icon" href="assets/images/tshirt.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
        .code-input {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .code-input input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        .code-input input:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .code-input input.filled {
            border-color: #0d6efd;
        }
        
        /* Minimalist Toast Styles */
        #toast {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 250px;
            border: 1px solid #e0e0e0;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .toast-header {
            background-color: white;
            border-bottom: 1px solid #f0f0f0;
            padding: 8px 16px;
        }
        .toast-body {
            padding: 12px 16px;
            color: #333;
        }
        .toast-icon {
            margin-right: 8px;
            font-size: 1.2rem;
        }
        .toast-success .toast-icon { color: #28a745; }
        .toast-danger .toast-icon { color: #dc3545; }
        .toast-info .toast-icon { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="form-title">Reset Your Password</h2>
            
            <div id="emailNotExistAlert" class="alert alert-danger text-center mx-auto my-4 p-3" style="max-width: 400px; display: none;">
                Email Not Found
            </div>

            <form id="passwordResetForm">
                <!-- Step 1: Email Input -->
                <div class="mb-3" id="emailStep">
                    <label for="resetEmail" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="resetEmail" name="email" placeholder="Enter your email">
                    <div class="form-text mt-1">We'll send a verification code to this email</div>
                </div>

                <!-- Step 2: Verification Code -->
                <div class="mb-3" id="codeStep" style="display: none;">
                    <label class="form-label">Verification Code</label>
                    <div class="code-input">
                        <input type="text" maxlength="1" class="form-control code-digit" data-index="1" autocomplete="off">
                        <input type="text" maxlength="1" class="form-control code-digit" data-index="2" autocomplete="off">
                        <input type="text" maxlength="1" class="form-control code-digit" data-index="3" autocomplete="off">
                        <input type="text" maxlength="1" class="form-control code-digit" data-index="4" autocomplete="off">
                        <input type="text" maxlength="1" class="form-control code-digit" data-index="5" autocomplete="off">
                        <input type="text" maxlength="1" class="form-control code-digit" data-index="6" autocomplete="off">
                    </div>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary w-100" type="button" id="resendCodeBtn">Resend Code</button>
                    </div>
                    <div class="form-text mt-1">Check your email for the 6-digit code</div>
                </div>

                <!-- Step 3: New Password -->
                <div class="mb-3" id="passwordStep" style="display: none;">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="newPassword" placeholder="Enter new password">
                    
                    <label for="confirmPassword" class="form-label mt-3">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
                    <div class="form-text mt-1">Password must be at least 8 characters</div>
                </div>

                <button type="submit" id="resetButton" class="btn btn-primary w-100 mt-3">
                    <span id="buttonIcon" class="me-2"><i class="fas fa-envelope"></i></span>
                    <span id="buttonText">Send Verification Code</span>
                    <span id="buttonSpinner" class="spinner-border spinner-border-sm ms-2" style="display:none;"></span>
                </button>

                <div class="text-center mt-3">
                    Remember your password? <a href="login">Login</a>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">
                <span id="toastIcon" class="toast-icon"></span>
            </strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
$(document).ready(function() {
    const toast = new bootstrap.Toast(document.getElementById('toast'));
    let currentStep = 1;
    let resetEmail = '';

    function showToast(type, message) {
        console.log(`[${type}] ${message}`);
        const toastEl = $('#toast');
        toastEl.removeClass('toast-success toast-danger toast-info');
        toastEl.addClass(`toast-${type}`);
        
        // Set icon based on type
        let iconClass = '';
        switch(type) {
            case 'success':
                iconClass = 'fas fa-check-circle';
                break;
            case 'danger':
                iconClass = 'fas fa-exclamation-circle';
                break;
            case 'info':
                iconClass = 'fas fa-info-circle';
                break;
        }
        
        $('#toastIcon').attr('class', `toast-icon ${iconClass}`);
        $('#toastMessage').text(message);
        toast.show();
    }

    function resetButtonState() {
        $('#resetButton').prop('disabled', false);
        $('#buttonSpinner').hide();
        $('#buttonIcon').show();
    }

    function updateFormStep(step) {
        currentStep = step;
        
        // Hide all steps first
        $('#emailStep, #codeStep, #passwordStep').hide();
        
        // Show only the current step
        if (step === 1) {
            $('#emailStep').show();
            $('#buttonText').text('Send Verification Code');
            $('#buttonIcon').html('<i class="fas fa-envelope"></i>');
        } else if (step === 2) {
            $('#codeStep').show();
            $('#buttonText').text('Verify Code');
            $('#buttonIcon').html('<i class="fas fa-check-circle"></i>');
            // Focus on first code input when step is shown
            $('.code-digit[data-index="1"]').focus();
        } else if (step === 3) {
            $('#passwordStep').show();
            $('#buttonText').text('Reset Password');
            $('#buttonIcon').html('<i class="fas fa-key"></i>');
        }
    }

    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function getVerificationCode() {
        let code = '';
        $('.code-digit').each(function() {
            code += $(this).val();
        });
        return code;
    }

    function validateCode(code) {
        return code.length === 6 && /^\d+$/.test(code);
    }

    function validatePassword(password) {
        return password.length >= 8;
    }

    // Handle code input navigation and auto-focus
    $(document).on('input', '.code-digit', function() {
        const index = parseInt($(this).data('index'));
        const value = $(this).val();
        
        if (value.length === 1) {
            $(this).addClass('filled');
            if (index < 6) {
                $(`.code-digit[data-index="${index + 1}"]`).focus();
            }
        } else {
            $(this).removeClass('filled');
        }
    });

    // Handle backspace navigation
    $(document).on('keydown', '.code-digit', function(e) {
        const index = parseInt($(this).data('index'));
        
        if (e.key === 'Backspace' && $(this).val().length === 0 && index > 1) {
            $(`.code-digit[data-index="${index - 1}"]`).focus();
        }
    });

    $('#passwordResetForm').on('submit', function(e) {
        e.preventDefault();
        
        const $resetButton = $('#resetButton');
        const $buttonText = $('#buttonText');
        const $buttonSpinner = $('#buttonSpinner');
        
        // Show loading state
        $resetButton.prop('disabled', true);
        $buttonSpinner.show();
        $('#buttonIcon').hide();

        const handleError = (error) => {
            console.error('Error:', error);
            let errorMsg = 'An error occurred. Please try again.';
            
            if (error.responseJSON && error.responseJSON.message) {
                errorMsg = error.responseJSON.message;
            } else if (error.statusText) {
                errorMsg = error.statusText;
            }
            
            showToast('danger', errorMsg);
            resetButtonState();
        };

        if (currentStep === 1) {
            // Step 1: Send verification code
            const email = $('#resetEmail').val().trim();
            
            if (!validateEmail(email)) {
                showToast('danger', 'Please enter a valid email address');
                return resetButtonState();
            }
            
            $buttonText.text('Sending code...');
            
            $.ajax({
                url: 'functions/send_reset_code.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ email: email }),
                dataType: 'json',
                success: (response) => {
                    if (response && response.success) {
                        showToast('success', response.message || 'Verification code sent! Check your email.');
                        resetEmail = email;
                        updateFormStep(2);
                        $('#emailNotExistAlert').hide();
                    } else {
                        const errorMsg = response && response.message ? response.message : 'Failed to send verification code';
                        showToast('danger', errorMsg);
                        $('#emailNotExistAlert').show();
                    }
                },
                error: handleError,
                complete: resetButtonState
            });
        } else if (currentStep === 2) {
            // Step 2: Verify code
            const code = getVerificationCode();
            
            if (!validateCode(code)) {
                showToast('danger', 'Please enter a valid 6-digit code');
                return resetButtonState();
            }
            
            $buttonText.text('Verifying...');
            
            $.ajax({
                url: 'functions/verify_reset_code.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ 
                    email: resetEmail,
                    code: code
                }),
                dataType: 'json',
                success: (response) => {
                    if (response && response.success) {
                        showToast('success', response.message || 'Code verified successfully!');
                        updateFormStep(3);
                    } else {
                        const errorMsg = response && response.message ? response.message : 'Invalid verification code';
                        showToast('danger', errorMsg);
                    }
                },
                error: handleError,
                complete: resetButtonState
            });
        } else if (currentStep === 3) {
            // Step 3: Reset password
            const newPassword = $('#newPassword').val();
            const confirmPassword = $('#confirmPassword').val();
            
            if (!validatePassword(newPassword)) {
                showToast('danger', 'Password must be at least 8 characters');
                return resetButtonState();
            }
            
            if (newPassword !== confirmPassword) {
                showToast('danger', 'Passwords do not match');
                return resetButtonState();
            }
            
            $buttonText.text('Updating...');
            
            $.ajax({
                url: 'functions/update_password.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ 
                    email: resetEmail,
                    password: newPassword
                }),
                dataType: 'json',
                success: (response) => {
                    if (response && response.success) {
                        showToast('success', response.message || 'Password updated successfully!');
                        setTimeout(() => {
                            window.location.href = 'login';
                        }, 1500);
                    } else {
                        const errorMsg = response && response.message ? response.message : 'Failed to update password';
                        showToast('danger', errorMsg);
                    }
                },
                error: handleError,
                complete: resetButtonState
            });
        }
    });

    // Resend code button
    $('#resendCodeBtn').on('click', function() {
        if (!resetEmail) return;
        
        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');
        
        $.ajax({
            url: 'functions/send_reset_code.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ email: resetEmail }),
            dataType: 'json',
            success: (response) => {
                if (response && response.success) {
                    showToast('success', 'New verification code sent!');
                    // Clear the code inputs
                    $('.code-digit').val('').removeClass('filled');
                    $('.code-digit[data-index="1"]').focus();
                } else {
                    const errorMsg = response && response.message ? response.message : 'Failed to resend code';
                    showToast('danger', errorMsg);
                }
            },
            error: (xhr) => {
                const errorMsg = xhr.responseJSON?.message || 'Failed to resend code';
                showToast('danger', errorMsg);
            },
            complete: () => {
                $btn.prop('disabled', false).text('Resend Code');
            }
        });
    });

    // Back to login button
    $('a[href="login"]').on('click', function(e) {
        e.preventDefault();
        window.location.href = 'login';
    });
});
    </script>
</body>
</html>