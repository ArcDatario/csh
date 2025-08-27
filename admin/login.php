<?php
require_once 'auth_check.php';

if (isLoggedIn()) {
    header('Location: dashboard');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (login($username, $password, $remember)) {
        header('Location: dashboard');
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Secure Access</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #10b981;
            --dark: #1e293b;
            --light: #f8            --border: #e2e8f0;
            --error: #ef4444;
            --success: #10b981;
            --text: #334155;
            --text-light: #64748b;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            background-image: 
                radial-gradient(at 80% 0%, hsla(189, 100%, 56%, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, hsla(355, 100%, 93%, 0.1) 0px, transparent 50%);
            transition: var(--transition);
        }

        .login-container {
            width: 100%;
            max-width: 28rem;
            background-color: var(--card);
            border-radius: 1.5rem;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border);
            transform: translateY(0);
            transition: var(--transition), transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .login-container:hover {
            transform: translateY(-0.25rem);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .login-header {
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            position: relative;
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: white;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }

        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .logo {
            width: 3.5rem;
            height: 3.5rem;
            background-color: white;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: var(--shadow);
        }

        .logo i {
            color: var(--primary);
            font-size: 1.75rem;
        }

        .theme-toggle {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .theme-toggle:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: rotate(30deg);
        }

        .login-form {
            padding: 2rem;
            position: relative;
            margin-top: -1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-light);
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            font-size: 1rem;
            background-color: var(--card);
            color: var(--text);
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        .form-control:hover {
            border-color: var(--primary);
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-light);
            transition: var(--transition);
            background: none;
            border: none;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .toggle-password:hover {
            color: var(--primary);
            background-color: rgba(99, 102, 241, 0.1);
        }

        .remember-forgot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .remember-me label {
            font-size: 0.875rem;
            color: var(--text-light);
            cursor: pointer;
        }

        .forgot-password {
            font-size: 0.875rem;
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3), 0 2px 4px -1px rgba(99, 102, 241, 0.1);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3), 0 4px 6px -2px rgba(99, 102, 241, 0.1);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .error-message {
            color: var(--error);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
            padding: 1rem;
            background-color: rgba(239, 68, 68, 0.1);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
            transform: translate3d(0, 0, 0);
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: var(--text-light);
        }

        .footer-text a {
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        /* Floating animation for header */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .logo {
            animation: float 6s ease-in-out infinite;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
                border-radius: 1rem;
            }
            
            .login-form {
                padding: 1.5rem;
            }
            
            .login-header {
                padding: 1.5rem 1rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
                        <div class="logo">
                <i class="fas fa-lock"></i>
            </div>
            <h1>Admin Portal</h1>
            <p>Secure access to your dashboard</p>
        </div>
        
        <form class="login-form" id="loginForm" method="POST">
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                    <button type="button" class="toggle-password" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
               <!-- <a href="#" class="forgot-password">Forgot password?</a>  -->
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
            
            <p class="footer-text">
                By continuing, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
            </p>
        </form>
    </div>

   <script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = togglePassword.querySelector('i');
            icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            
            // Add animation to toggle button
            togglePassword.style.transform = 'scale(0.9)';
            setTimeout(() => {
                togglePassword.style.transform = '';
            }, 200);
        });
    }
    
    // Form submission with subtle feedback
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            const submitButton = loginForm.querySelector('button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating';
            submitButton.disabled = true;
        });
    }
    
    // Add hover effect to form inputs
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', () => {
            input.parentElement.style.transform = '';
        });
    });
</script>
</body>
</html>