<div class="profile-dropdown" id="profileDropdown">
  <div class="profile-trigger">
    <img src="profile/admin-icon.jpg" alt="Profile" class="profile-icon" id="profileIcon">
    <span class="profile-arrow">▼</span>
  </div>
  <div class="dropdown-menu">
    <a href="#" class="menu-item" id="profileLink">
      <svg class="menu-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
      <span class="profile-nav">Profile</span>
    </a>
    <div class="menu-divider"></div>
    <a class="menu-item logout">
      <svg class="menu-icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
        <polyline points="16 17 21 12 16 7"></polyline>
        <line x1="21" y1="12" x2="9" y2="12"></line>
      </svg>
      <span>Logout</span>
    </a>
  </div>
</div>

<!-- Profile Modal -->
<div class="profile-modal" id="profileModal">
  <div class="profile-modal-content">
    <span class="profile-close-modal">&times;</span>
    <h2>Profile Settings</h2>
    <form id="profileForm" enctype="multipart/form-data" autocomplete="off">
      <input type="hidden" id="admin_id" name="admin_id" value="<?php echo $_SESSION['admin_id'] ?? ($_SESSION['user_id'] ?? ''); ?>">
      <div class="profile-form-group">
        <label for="profileImage">Profile Image</label>
        <div class="profile-image-upload">
          <img id="profileImagePreview" src="profile/admin-icon.jpg" alt="Current Profile Image">
          <input type="file" id="profileImage" name="profileImage" accept="image/*">
          <label for="profileImage" class="profile-upload-btn">Change Image</label>
        </div>
      </div>
      <div class="profile-form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="profile-form-group">
        <label for="currentPassword">Current Password (leave blank to keep)</label>
        <div class="password-input-container">
          <input type="password" id="currentPassword" name="currentPassword" autocomplete="new-password">
          <span class="toggle-password" data-target="currentPassword">👁️</span>
        </div>
      </div>
      <div class="profile-form-group">
        <label for="newPassword">New Password</label>
        <div class="password-input-container">
          <input type="password" id="newPassword" name="newPassword" autocomplete="new-password">
          <span class="toggle-password" data-target="newPassword">👁️</span>
        </div>
      </div>
      <div class="profile-form-group">
        <label for="confirmPassword">Confirm New Password</label>
        <div class="password-input-container">
          <input type="password" id="confirmPassword" name="confirmPassword" autocomplete="new-password">
          <span class="toggle-password" data-target="confirmPassword">👁️</span>
        </div>
      </div>
      <button type="submit" class="profile-save-btn">Save Changes</button>
      <div id="profileMessage" class="profile-message"></div>
    </form>
  </div>
</div>

<style>
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
}

.toggle-password:hover {
  opacity: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const defaultImage = 'profile/admin-icon.jpg';
  
  // Function to update profile image in all places
  function updateProfileImage(imagePath) {
    const actualImage = imagePath ? 'profile/' + imagePath : defaultImage;
    document.getElementById('profileImagePreview').src = actualImage;
    document.getElementById('profileIcon').src = actualImage;
  }

  // Function to fetch and display admin data
  async function fetchAdminData() {
    try {
      const response = await fetch('get_admin_data.php');
      const data = await response.json();
      
      if (data.success) {
        // Update form fields
        document.getElementById('username').value = data.username;
        document.getElementById('admin_id').value = data.admin_id;
        
        // Update profile images
        updateProfileImage(data.image);
      } else {
        showMessage('Error: ' + data.message, 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showMessage('An error occurred while loading profile data', 'error');
    }
  }

  // Function to show messages
  function showMessage(message, type = 'success') {
    const messageElement = document.getElementById('profileMessage');
    messageElement.textContent = message;
    messageElement.className = `profile-message ${type}`;
    setTimeout(() => {
      messageElement.textContent = '';
      messageElement.className = 'profile-message';
    }, 5000);
  }

  // Control body scrolling when modal is open
  function toggleBodyScroll(enable) {
    document.body.style.overflow = enable ? '' : 'hidden';
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

  // Open modal when profile link is clicked
  const profileLink = document.getElementById('profileLink');
  if (profileLink) {
    profileLink.addEventListener('click', async function(e) {
      e.preventDefault();
      await fetchAdminData();
      document.getElementById('profileModal').style.display = 'block';
      toggleBodyScroll(false);
    });
  }

  // Close modal when X is clicked
  const closeModal = document.querySelector('.profile-close-modal');
  if (closeModal) {
    closeModal.addEventListener('click', function() {
      document.getElementById('profileModal').style.display = 'none';
      toggleBodyScroll(true);
    });
  }

  // Close modal when clicking outside
  window.addEventListener('click', function(e) {
    if (e.target === document.getElementById('profileModal')) {
      document.getElementById('profileModal').style.display = 'none';
      toggleBodyScroll(true);
    }
  });

  // Preview image before upload
  const profileImageInput = document.getElementById('profileImage');
  if (profileImageInput) {
    profileImageInput.addEventListener('change', function(e) {
      if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(event) {
          document.getElementById('profileImagePreview').src = event.target.result;
        };
        reader.readAsDataURL(e.target.files[0]);
      }
    });
  }

  // Handle form submission
  const profileForm = document.getElementById('profileForm');
  if (profileForm) {
    profileForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      
      fetch('update_profile.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showMessage('Profile updated successfully!');
          
          // Update UI elements
          if (data.newUsername) {
            document.getElementById('username').value = data.newUsername;
          }
          
          // Update image in all places
          if (data.newImage !== undefined) {
            updateProfileImage(data.newImage);
          }
          
          // Close modal after 2 seconds
          setTimeout(() => {
            document.getElementById('profileModal').style.display = 'none';
            toggleBodyScroll(true);
          }, 2000);
        } else {
          showMessage('Error: ' + data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while updating profile', 'error');
      });
    });
  }

  // Load initial profile data and set up password toggles
  fetchAdminData();
  setupPasswordToggles();
});
</script>

<style>
/* Profile Modal Styles */
.profile-modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  overflow-y: auto;
  padding: 20px 0;
}

.profile-modal-content {
  background-color: #fff;
  margin: 20px auto;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  width: 90%;
  max-width: 500px;
  position: relative;
  box-sizing: border-box;
}

.profile-close-modal {
  position: absolute;
  right: 20px;
  top: 15px;
  font-size: 24px;
  cursor: pointer;
  color: #666;
  z-index: 1;
}

.profile-close-modal:hover {
  color: #333;
}

/* Profile Form Styles */
.profile-form-group {
  margin-bottom: 20px;
}

.profile-form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #444;
}

.profile-form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  box-sizing: border-box;
}

.profile-image-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

#profileImagePreview {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #eee;
}

.profile-upload-btn {
  background: #f0f0f0;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.2s;
}

.profile-upload-btn:hover {
  background: #e0e0e0;
}

#profileImage {
  display: none;
}

.profile-save-btn {
  background: #4CAF50;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  width: 100%;
  transition: background 0.2s;
}

.profile-save-btn:hover {
  background: #45a049;
}

.profile-message {
  margin-top: 15px;
  padding: 10px;
  border-radius: 4px;
  text-align: center;
}

.profile-message.success {
  background-color: #dff0d8;
  color: #3c763d;
}

.profile-message.error {
  background-color: #f2dede;
  color: #a94442;
}

/* Responsive adjustments */
@media (max-height: 600px) {
  .profile-modal {
    padding: 10px 0;
  }
  
  .profile-modal-content {
    margin: 10px auto;
  }
  
  .profile-form-group {
    margin-bottom: 15px;
  }
}

@media (max-width: 480px) {
  .profile-modal-content {
    width: 95%;
    padding: 15px;
  }
  
  #profileImagePreview {
    width: 80px;
    height: 80px;
  }
  
  .profile-form-group input {
    padding: 8px;
  }
  
  .profile-save-btn {
    padding: 8px 15px;
    font-size: 15px;
  }
}
</style>