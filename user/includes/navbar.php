<nav id="nav">
    <ul>
        <li>
            <a href="<?php echo (basename($_SERVER['PHP_SELF']) == 'quote.php') ? 'home#home' : '#home'; ?>">Home</a>
        </li>
        <li>
            <a href="<?php echo (basename($_SERVER['PHP_SELF']) == 'quote.php') ? 'home#services' : '#services'; ?>">Services</a>
        </li>
        <li>
            <a href="<?php echo (basename($_SERVER['PHP_SELF']) == 'quote.php') ? 'home#gallery' : '#gallery'; ?>">Gallery</a>
        </li>
        <li>
            <a href="<?php echo (basename($_SERVER['PHP_SELF']) == 'quote.php') ? 'home#contact' : '#contact'; ?>">Contact</a>
        </li>
        <li>
            <a href="quote.php" <?php if (basename($_SERVER['PHP_SELF']) == 'quote.php') echo 'class="active"'; ?>>Quote</a>
        </li>
       <li class="profile-dropdown">
    <a href="#" class="profile-icon" id="profileToggle">
        <img src="functions/profile/<?php echo htmlspecialchars($_SESSION['image'] ?? 'icon.png'); ?>" alt="Profile" height="32" width="32">
    </a>
    <ul class="dropdown-menu" id="dropdownMenu">
        <li><a class="profile" id="profile">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</li>
    </ul>
</nav>

<!-- Add this modal at the bottom of navbar.php -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Profile Information</h2>
        <form id="profileForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="profileImage">Profile Picture:</label>
                <div class="image-preview">
                   <img id="imagePreview" src="functions/profile/<?php echo htmlspecialchars($_SESSION['image'] ?? 'icon.png'); ?>" alt="Profile Preview">
                </div>
              <label class="minimal-file-input">
  <input type="file" id="profileImage" name="profileImage" accept="image/*">
  <span>Upload Image</span>
</label>
            </div>
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <div class="verified-field">
                    <input type="email" id="email" name="email" required readonly>
                    <button type="button" id="changeEmailBtn" class="change-btn">Change Email</button>
                </div>
                <div id="newEmailSection" style="display:none;">
                    <input type="email" id="newEmail" placeholder="New Email">
                    <button type="button" id="sendEmailCodeBtn" class="verify-btn">Send Verification Code</button>
                </div>
                <div id="emailVerifySection" style="display:none;">
                    <p>Enter 6-digit verification code:</p>
                    <div class="code-input">
                        <input type="text" maxlength="1" class="code-box" data-index="1">
                        <input type="text" maxlength="1" class="code-box" data-index="2">
                        <input type="text" maxlength="1" class="code-box" data-index="3">
                        <input type="text" maxlength="1" class="code-box" data-index="4">
                        <input type="text" maxlength="1" class="code-box" data-index="5">
                        <input type="text" maxlength="1" class="code-box" data-index="6">
                    </div>
                    <button type="button" id="submitEmailVerify" class="verify-btn">Verify Email</button>
                    <button type="button" id="cancelEmailChange" class="cancel-btn">Cancel</button>
                </div>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <div class="verified-field">
                    <input type="text" id="phone" name="phone" required readonly>
                    <button type="button" id="changePhoneBtn" class="change-btn">Change Phone</button>
                </div>
                <div id="newPhoneSection" style="display:none;">
                    <input type="text" id="newPhone" placeholder="New Phone Number">
                    <button type="button" id="sendPhoneCodeBtn" class="verify-btn">Send Verification Code</button>
                </div>
                <div id="phoneVerifySection" style="display:none;">
                    <p>Enter 6-digit verification code:</p>
                    <div class="code-input">
                        <input type="text" maxlength="1" class="code-box" data-index="1">
                        <input type="text" maxlength="1" class="code-box" data-index="2">
                        <input type="text" maxlength="1" class="code-box" data-index="3">
                        <input type="text" maxlength="1" class="code-box" data-index="4">
                        <input type="text" maxlength="1" class="code-box" data-index="5">
                        <input type="text" maxlength="1" class="code-box" data-index="6">
                    </div>
                    <button type="button" id="submitPhoneVerify" class="verify-btn">Verify Phone</button>
                    <button type="button" id="cancelPhoneChange" class="cancel-btn">Cancel</button>
                </div>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="newPassword">New Password (leave blank to keep unchanged):</label>
                <input type="password" id="newPassword" name="newPassword">
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm New Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword">
            </div>
            <div class="button-container">
  <button type="submit" id="updateProfileBtn" class="minimal-submit-btn">Save Changes</button>
</div>
        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    // Profile modal functionality
    const modal = document.getElementById('profileModal');
    const profileBtn = document.getElementById('profile');
    const closeBtn = document.querySelector('.close');

    let originalEmail = '';
    let originalPhone = '';
    let emailVerified = false;
    let phoneVerified = false;
    
    profileBtn.onclick = function() {
        fetchUserData();
        modal.style.display = 'block';
    }
    
    closeBtn.onclick = function() {
        modal.style.display = 'none';
        resetVerificationSections();
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
            resetVerificationSections();
        }
    }
    
    // Image preview
    document.getElementById('profileImage').addEventListener('change', function(e) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('imagePreview').src = event.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    });
    
    // Change email/phone buttons
    document.getElementById('changeEmailBtn').addEventListener('click', function() {
        document.getElementById('newEmailSection').style.display = 'block';
        document.getElementById('changeEmailBtn').style.display = 'none';
    });
    
    document.getElementById('changePhoneBtn').addEventListener('click', function() {
        document.getElementById('newPhoneSection').style.display = 'block';
        document.getElementById('changePhoneBtn').style.display = 'none';
    });
    
    // Cancel buttons
    document.getElementById('cancelEmailChange').addEventListener('click', function() {
        resetEmailVerification();
    });
    
    document.getElementById('cancelPhoneChange').addEventListener('click', function() {
        resetPhoneVerification();
    });
    
    // Send verification code buttons
    document.getElementById('sendEmailCodeBtn').addEventListener('click', function() {
        const newEmail = document.getElementById('newEmail').value;
        if (!newEmail || !validateEmail(newEmail)) {
            showToast('Please enter a valid email address', 'error');
            return;
        }
        
        sendVerificationCode('email', newEmail);
        document.getElementById('emailVerifySection').style.display = 'block';
        document.getElementById('newEmailSection').style.display = 'none';
    });
    
    document.getElementById('sendPhoneCodeBtn').addEventListener('click', function() {
        const newPhone = document.getElementById('newPhone').value;
        if (!newPhone || !validatePhone(newPhone)) {
            showToast('Please enter a valid phone number', 'error');
            return;
        }
        
        sendVerificationCode('sms', newPhone);
        document.getElementById('phoneVerifySection').style.display = 'block';
        document.getElementById('newPhoneSection').style.display = 'none';
    });
    
    // Submit verification
    document.getElementById('submitEmailVerify').addEventListener('click', function() {
        const code = getVerificationCode('email');
        verifyCode(code, 'email');
    });
    
    document.getElementById('submitPhoneVerify').addEventListener('click', function() {
        const code = getVerificationCode('phone');
        verifyCode(code, 'phone');
    });
    
    // Code input handling
    document.querySelectorAll('.code-box').forEach(box => {
        box.addEventListener('input', function() {
            if (this.value.length === 1) {
                const nextIndex = parseInt(this.dataset.index) + 1;
                const nextBox = document.querySelector(`.code-box[data-index="${nextIndex}"]`);
                if (nextBox) nextBox.focus();
            }
        });
        
        box.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value.length === 0) {
                const prevIndex = parseInt(this.dataset.index) - 1;
                const prevBox = document.querySelector(`.code-box[data-index="${prevIndex}"]`);
                if (prevBox) prevBox.focus();
            }
        });
    });
    
    // Form submission
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });
    
    function resetVerificationSections() {
        resetEmailVerification();
        resetPhoneVerification();
    }
    
    function resetEmailVerification() {
        document.getElementById('newEmailSection').style.display = 'none';
        document.getElementById('emailVerifySection').style.display = 'none';
        document.getElementById('changeEmailBtn').style.display = 'block';
        document.getElementById('newEmail').value = '';
        clearCodeInputs('email');
    }
    
    function resetPhoneVerification() {
        document.getElementById('newPhoneSection').style.display = 'none';
        document.getElementById('phoneVerifySection').style.display = 'none';
        document.getElementById('changePhoneBtn').style.display = 'block';
        document.getElementById('newPhone').value = '';
        clearCodeInputs('phone');
    }
    
    function clearCodeInputs(type) {
        const boxes = document.querySelectorAll(`#${type}VerifySection .code-box`);
        boxes.forEach(box => box.value = '');
    }
    
    function getVerificationCode(type) {
        let code = '';
        const boxes = document.querySelectorAll(`#${type}VerifySection .code-box`);
        boxes.forEach(box => code += box.value);
        return code;
    }
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function validatePhone(phone) {
        const re = /^[0-9]{10,12}$/;
        return re.test(phone);
    }
    
    function fetchUserData() {
        fetch('functions/get_profile.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('name').value = data.user.name || '';
                    document.getElementById('email').value = data.user.email || '';
                    document.getElementById('phone').value = data.user.phone_number || '';
                    document.getElementById('address').value = data.user.address || '';

                    originalEmail = data.user.email;
                    originalPhone = data.user.phone_number;
                    emailVerified = data.verified.email || false;
                    phoneVerified = data.verified.phone || false;

                    // Always set the preview image, fallback to default if not set
                    let imagePath = 'functions/profile/' + (data.user.image ? data.user.image : 'icon.png');
                    document.getElementById('imagePreview').src = imagePath;

                    // Update verification status
                    if (emailVerified) {
                        document.getElementById('email').classList.add('verified');
                    } else {
                        document.getElementById('email').classList.remove('verified');
                    }

                    if (phoneVerified) {
                        document.getElementById('phone').classList.add('verified');
                    } else {
                        document.getElementById('phone').classList.remove('verified');
                    }
                } else {
                    showToast('Failed to fetch user data: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while fetching user data', 'error');
            });
    }
    
    function sendVerificationCode(method, value) {
        const formData = new FormData();
        formData.append('method', method);
        
        if (method === 'email') {
            formData.append('email', value);
        } else {
            formData.append('phone', value);
        }
        
        fetch('functions/send_verification.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
            } else {
                showToast('Error: ' + data.message, 'error');
                if (method === 'email') {
                    resetEmailVerification();
                } else {
                    resetPhoneVerification();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while sending verification code', 'error');
            if (method === 'email') {
                resetEmailVerification();
            } else {
                resetPhoneVerification();
            }
        });
    }
    
    function verifyCode(code, type) {
        if (code.length !== 6) {
            showToast('Please enter a complete 6-digit code', 'error');
            return;
        }
        
        fetch('functions/verify_code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'code=' + encodeURIComponent(code) + '&type=' + type
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Verification successful!', 'success');
                if (type === 'email') {
                    const newEmail = document.getElementById('newEmail').value;
                    document.getElementById('email').value = newEmail;
                    document.getElementById('email').classList.add('verified');
                    originalEmail = newEmail;
                    emailVerified = true;
                    resetEmailVerification();
                } else {
                    const newPhone = document.getElementById('newPhone').value;
                    document.getElementById('phone').value = newPhone;
                    document.getElementById('phone').classList.add('verified');
                    originalPhone = newPhone;
                    phoneVerified = true;
                    resetPhoneVerification();
                }
            } else {
                showToast(data.message, 'error');
                clearCodeInputs(type);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while verifying code', 'error');
            clearCodeInputs(type);
        });
    }
    
  function updateProfile() {
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);
    
    // Add current password if new password is provided
    const newPassword = document.getElementById('newPassword').value;
    if (newPassword) {
        const currentPassword = prompt('Please enter your current password to confirm changes:');
        if (!currentPassword) return;
        formData.append('currentPassword', currentPassword);
    }
    
    fetch('functions/update_profile.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin' // Important for sessions
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update profile image in navbar if changed
            if (data.newImage) {
                const profileImages = document.querySelectorAll('.profile-icon img, #imagePreview');
                profileImages.forEach(img => {
                    img.src = 'functions/profile/' + data.newImage + '?t=' + new Date().getTime();
                });
                // Force refresh by adding timestamp
                document.getElementById('imagePreview').src = 'functions/profile/' + data.newImage + '?t=' + new Date().getTime();
            }
            
            showToast('Profile updated successfully!', 'success');
            modal.style.display = 'none';
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while updating profile', 'error');
    });
}
});
</script>
<script>
    // Mobile toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const profileToggle = document.getElementById('profileToggle');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    // Toggle menu on click (mobile)
    profileToggle.addEventListener('click', function(e) {
        e.preventDefault();
        this.closest('.profile-dropdown').classList.toggle('active');
    });
    
    // Close when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.profile-dropdown')) {
            document.querySelectorAll('.profile-dropdown').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
});
</script>