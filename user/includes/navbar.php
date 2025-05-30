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

<script src="../assets/js/profile.js"></script>