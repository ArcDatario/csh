<nav id="nav">
    <ul>
        <li>
            <a href="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['quote.php', 'approved-order.php', 'processing-order.php', 'to-ship-order.php', 'to-pickup-order.php', 'completed-order.php'])) ? 'home#home' : '#home'; ?>">Home</a>
        </li>
        <li>
            <a href="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['quote.php', 'approved-order.php', 'processing-order.php', 'to-ship-order.php', 'to-pickup-order.php', 'completed-order.php'])) ? 'home#services' : '#services'; ?>">Services</a>
        </li>
        <li>
            <a href="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['quote.php', 'approved-order.php', 'processing-order.php', 'to-ship-order.php', 'to-pickup-order.php', 'completed-order.php'])) ? 'home#gallery' : '#gallery'; ?>">Gallery</a>
        </li>
        <li>
            <a href="<?php echo (in_array(basename($_SERVER['PHP_SELF']), ['quote.php', 'approved-order.php', 'processing-order.php', 'to-ship-order.php', 'to-pickup-order.php', 'completed-order.php'])) ? 'home#contact' : '#contact'; ?>">Contact</a>
        </li>
        <li>
            <a href="quote.php" <?php 
                $current_page = basename($_SERVER['PHP_SELF']);
                $order_pages = ['quote.php', 'approved-order.php', 'processing-order.php', 'to-ship-order.php', 'to-pickup-order.php', 'completed-order.php'];
                if (in_array($current_page, $order_pages)) echo 'class="active"'; 
            ?>>Quote</a>
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
<style>
/* Modal styles with scrollable content and fixed footer */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 90%;
    max-width: 600px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    max-height: 80vh;
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 20px;
    margin: 0;
    background-color: #f8f8f8;
    border-bottom: 1px solid #eee;
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
}

#profileForm {
    overflow-y: auto;
    padding: 0 20px;
    flex: 1;
}

.modal-footer {
    padding: 15px 20px;
    background-color: #f8f8f8;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    position: sticky;
    bottom: 0;
}

/* Password visibility toggle styles */
.password-input-container {
    position: relative;
    width: 100%;
}

.password-input-container input {
    padding-right: 40px;
    width: 100%;
    box-sizing: border-box;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    user-select: none;
    opacity: 0.7;
    transition: opacity 0.2s;
    font-size: 18px;
}

.toggle-password:hover {
    opacity: 1;
}
.minimal-cancel-btn {
    background-color: #e53e3e;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.minimal-cancel-btn:hover {
    background-color: #c53030;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
}

.minimal-cancel-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
}

.minimal-cancel-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.3);
}
</style>

<!-- Updated modal structure -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Profile Information</h2>
            <span class="close">&times;</span>
        </div>
        <form id="profileForm" enctype="multipart/form-data">
            <!-- Your existing form content here -->
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
                <div class="password-input-container">
                    <input type="password" id="newPassword" name="newPassword" autocomplete="new-password">
                    <span class="toggle-password" data-target="newPassword">👁️</span>
                </div>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm New Password:</label>
                <div class="password-input-container">
                    <input type="password" id="confirmPassword" name="confirmPassword" autocomplete="new-password">
                    <span class="toggle-password" data-target="confirmPassword">👁️</span>
                </div>
            </div>
        </form>
        <div class="modal-footer">
            <button type="button" class="minimal-cancel-btn" id="closeModalBtn">Close</button>
            <button type="button" id="updateProfileBtn" class="minimal-submit-btn">Save Changes</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const modal = document.getElementById('profileModal');
    const closeBtn = document.querySelector('.close');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const updateProfileBtn = document.getElementById('updateProfileBtn');
    
    // Close modal when clicking on X or Close button
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Update profile button functionality
    if (updateProfileBtn) {
        updateProfileBtn.addEventListener('click', function() {
            document.getElementById('profileForm').dispatchEvent(new Event('submit'));
        });
    }
    
    // Toggle password visibility
    function setupPasswordToggles() {
        const toggles = document.querySelectorAll('.toggle-password');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.textContent = '🔒';
                } else {
                    passwordInput.type = 'password';
                    this.textContent = '👁️';
                }
            });
        });
    }

    // Set up the password toggles
    setupPasswordToggles();
    
    // Additional measure to prevent auto-fill
    setTimeout(function() {
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
    }, 100);
});
</script>

<script src="../assets/js/profile.js"></script>