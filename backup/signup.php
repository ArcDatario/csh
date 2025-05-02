<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="icon" href="assets/images/tshirt.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="verification-container">
            <h2 class="form-title">Email Verification</h2>

            <form id="signupForm" >
                <div class="mb-3" id="emailGroup">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email">
                    <div class="form-text mt-1">We'll send a verification code to this email</div>
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

    <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>

    <script src="assets/js/auth-script-signup.js"></script>
    
    </script>
    <script>
   
</script>
</body>
</html>