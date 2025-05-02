$(document).ready(function() {
    const toast = new bootstrap.Toast(document.getElementById('toast'));
    let currentStep = 1; // 1: email, 2: code, 3: password
    
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

    // Handle 6-digit code input boxes
    $('.code-input').on('input', function() {
        const index = parseInt($(this).data('index'));
        const value = $(this).val();
        
        if (value.length === 1 && index < 6) {
            $(`.code-input[data-index="${index + 1}"]`).focus();
        }
        updateVerificationCode();
    });
    
    // Allow backspace to move to previous box
    $('.code-input').on('keydown', function(e) {
        if (e.key === 'Backspace' && $(this).val().length === 0 && $(this).data('index') > 1) {
            $(`.code-input[data-index="${$(this).data('index') - 1}"]`).focus();
        }
    });
    
    function updateVerificationCode() {
        let code = '';
        $('.code-input').each(function() {
            code += $(this).val();
        });
        $('#verificationCode').val(code);
    }
    
    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strengthBar = $('#passwordStrength');
        let strength = 0;
        
        if (password.length > 0) strength += 20;
        if (password.length >= 8) strength += 30;
        if (/[A-Z]/.test(password)) strength += 15;
        if (/[0-9]/.test(password)) strength += 15;
        if (/[^A-Za-z0-9]/.test(password)) strength += 20;
        
        strength = Math.min(strength, 100);
        strengthBar.css('width', strength + '%');
        
        if (strength < 40) {
            strengthBar.css('background-color', '#dc3545');
        } else if (strength < 70) {
            strengthBar.css('background-color', '#fd7e14');
        } else {
            strengthBar.css('background-color', '#28a745');
        }
    });

    $('#signupForm').on('submit', function(e) {
        e.preventDefault();
        
        const signupButton = $('#signupButton');
        const buttonText = $('#buttonText');
        const buttonSpinner = $('#buttonSpinner');
        const buttonIcon = $('#buttonIcon');
        const email = $('#email').val().trim();
        const username = $('#username').val().trim(); // Added username input
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();
        const verificationCode = $('#verificationCode').val();
        
        // Show loading state
        signupButton.prop('disabled', true);
        buttonSpinner.show();
        buttonIcon.hide();

        if (currentStep === 1) {
            // Email validation
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showToast('error', 'Please enter a valid email address');
                return resetButtonState();
            }
            
           
            
            // First check if email exists
            $.ajax({
                url: 'functions/check_email.php',  // You'll need to create this endpoint
                type: 'POST',
                data: { email: email },
                dataType: 'json',
                success: function(response) {
                    if (response.exists) {
                        showToast('error', 'Email already registered');
                        resetButtonState();
                    } else {
                        // Email doesn't exist, proceed with sending verification
                        sendVerificationCode(email);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    showToast('error', 'Error checking email. Please try again.');
                    resetButtonState();
                }
            });
            
        } else if (currentStep === 2) {
            // Code verification
            if (!verificationCode || verificationCode.length !== 6 || !/^\d+$/.test(verificationCode)) {
                showToast('error', 'Please enter a valid 6-digit code');
                return resetButtonState();
            }
            
            buttonText.text('Verifying...');
            
            $.ajax({
                url: 'functions/verify_code.php',
                type: 'POST',
                data: { 
                    email: email,
                    code: verificationCode 
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Verification response:', response);
                    if (response.success) {
                        showToast('success', 'Email verified successfully!');
                        $('#codeGroup').hide();
                        $('#passwordGroup').show();
                        buttonText.text('Complete Registration');
                        $('#password').focus();
                        currentStep = 3;
                    } else {
                        showToast('error', response.message || 'Invalid verification code');
                        $('.code-input').val('').removeClass('is-invalid');
                        $('.code-input[data-index="1"]').focus();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    showToast('error', 'Verification failed. Please try again.');
                },
                complete: resetButtonState
            });
            
        } else if (currentStep === 3) {
            // Username validation
            if (!username || username.length < 3) {
                showToast('error', 'Username must be at least 3 characters');
                return resetButtonState();
            }

            // Password validation
            if (password.length < 8) {
                showToast('error', 'Password must be at least 8 characters');
                return resetButtonState();
            }
            
            if (password !== confirmPassword) {
                showToast('error', 'Passwords do not match');
                return resetButtonState();
            }
            
            buttonText.text('Creating account...');
            
            // Registration
            $.ajax({
                url: 'functions/register_user.php',
                type: 'POST',
                data: { 
                    email: email,
                    username: username, // Include username in the request
                    password: password
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Registration response:', response);
                    if (response.success) {
                        showToast('success', 'Account created successfully!');
                        setTimeout(() => {
                            window.location.href = 'user/home';
                        }, 1500);
                    } else {
                        showToast('error', response.message || 'Registration failed');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    showToast('error', 'Registration failed. Please try again.');
                },
                complete: resetButtonState
            });
        }

        function resetButtonState() {
            signupButton.prop('disabled', false);
            buttonSpinner.hide();
            buttonIcon.show();
        }
        
        function sendVerificationCode(email) {
            buttonText.text('Sending code...');
            
            $.ajax({
                url: 'functions/send_verification.php',
                type: 'POST',
                data: { email: email },
                dataType: 'json',
                success: function(response) {
                    console.log('Verification response:', response);
                    if (response.success) {
                        showToast('success', 'Verification code sent! Check your email.');
                        $('#emailGroup').hide();
                        $('#codeGroup').show();
                        buttonText.text('Verify');
                        $('.code-input[data-index="1"]').focus();
                        currentStep = 2;
                    } else {
                        showToast('error', response.message || 'Failed to send code');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    showToast('error', 'Network error. Please try again.');
                },
                complete: resetButtonState
            });
        }
    });

    // Resend code functionality
    $(document).on('click', '#resendCode', function(e) {
        e.preventDefault();
        const email = $('#email').val().trim();
        const resendLink = $(this);
        
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showToast('error', 'Please enter a valid email first');
            return;
        }
        
        resendLink.parent().html('<span class="text-muted">Sending new code...</span>');
        
        $.ajax({
            url: 'functions/send_verification.php',
            type: 'POST',
            data: { email: email, resend: true },
            dataType: 'json',
            success: function(response) {
                console.log('Resend response:', response);
                if (response.success) {
                    showToast('success', 'New verification code sent!');
                    $('.code-input').val('');
                    $('.code-input[data-index="1"]').focus();
                } else {
                    showToast('error', response.message || 'Failed to resend code');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                showToast('error', 'Failed to resend code. Please try again.');
            },
            complete: function() {
                setTimeout(() => {
                    resendLink.parent().html('Didn\'t receive code? <a href="#" id="resendCode">Resend</a>');
                }, 1500);
            }
        });
    });
});