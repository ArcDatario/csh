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
        
        <!-- Notification Bell -->
        <li class="notification-dropdown desktop-notification">
            <a href="#" class="notification-icon" id="notificationToggle">
                <span class="bell-icon">🔔</span>
                <span class="notification-count" id="notificationCount">0</span>
            </a>
            <div class="notification-menu" id="notificationMenu">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <button id="markAllRead">Mark all as read</button>
                </div>
                <div class="notification-list" id="notificationList">
                    <!-- Notifications will be loaded here via AJAX -->
                    <div class="loading-notifications">Loading notifications...</div>
                </div>
                <!-- <div class="notification-footer">
                    <a href="notifications.php">View All Notifications</a>
                </div> -->
            </div>
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
/* Notification Styles */
.notification-dropdown {
    position: relative;
}

.notification-icon {
    position: relative;
    display: flex;
    align-items: center;
    padding: 8px 12px;
    text-decoration: none;
    color: inherit;
}

.bell-icon {
    font-size: 20px;
}

.notification-count {
    position: absolute;
    top: -5px;
    right: 0;
    background-color: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
}

.notification-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    width: 350px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    z-index: 1000;
    max-height: 400px;
    overflow: hidden;
}

.notification-menu.show {
    display: block;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid #eee;
    background-color: #f8f9fa;
}

.notification-header h3 {
    margin: 0;
    font-size: 16px;
    color: #333;
}

#markAllRead {
    background: none;
    border: none;
    color: #007bff;
    cursor: pointer;
    font-size: 12px;
    text-decoration: underline;
}

#markAllRead:hover {
    color: #0056b3;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f0f7ff;
    border-left: 3px solid #007bff;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-content {
    font-size: 14px;
    color: #333;
    margin-bottom: 5px;
    line-height: 1.4;
}

.notification-time {
    font-size: 12px;
    color: #666;
}

.no-notifications {
    padding: 20px;
    text-align: center;
    color: #666;
    font-style: italic;
}

.loading-notifications {
    padding: 20px;
    text-align: center;
    color: #666;
}

.notification-footer {
    padding: 12px 15px;
    border-top: 1px solid #eee;
    text-align: center;
    background-color: #f8f9fa;
}

.notification-footer a {
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
}

.notification-footer a:hover {
    text-decoration: underline;
}

/* Hide desktop notification icon/count on small screens (mobile) but keep the menu in DOM
   so the mobile notification toggle (outside this nav) can open it. */
@media (max-width: 768px) {
    /* Hide only the desktop icon and count so the menu remains accessible to the mobile toggle */
    .desktop-notification > .notification-icon {
        display: none !important;
    }

    /* Ensure the notification menu isn't hidden by layout and can be positioned for mobile */
    .desktop-notification {
        display: block; /* keep the list item present */
    }
}

/* Modal styles with scrollable content and fixed footer */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    position: relative;
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    flex: 1;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}

.modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background-color: #f8f9fa;
}

/* Form styles */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}

.password-input-container {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    background: none;
    border: none;
    font-size: 16px;
}

.minimal-file-input {
    display: inline-block;
    padding: 8px 16px;
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
}

.minimal-file-input input {
    display: none;
}

.image-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 10px;
    border: 2px solid #ddd;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.minimal-submit-btn {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.minimal-cancel-btn {
    background-color: #6c757d;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.minimal-submit-btn:hover {
    background-color: #0056b3;
}

.minimal-cancel-btn:hover {
    background-color: #545b62;
}
</style>

<!-- Updated modal structure -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Profile Information</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
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
        </div>
        <div class="modal-footer">
            <button type="button" class="minimal-cancel-btn" id="closeModalBtn">Close</button>
            <button type="button" id="updateProfileBtn" class="minimal-submit-btn">Save Changes</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Notification functionality
    const notificationToggle = document.getElementById('notificationToggle');
    const mobileNotificationToggle = document.getElementById('mobileNotificationToggle');
    const notificationMenu = document.getElementById('notificationMenu');
    const notificationList = document.getElementById('notificationList');
    const notificationCount = document.getElementById('notificationCount');
    const mobileNotificationCount = document.getElementById('mobileNotificationCount');
    const markAllReadBtn = document.getElementById('markAllRead');

    // Load notifications when page loads
    loadNotifications();

    // Toggle notification menu for desktop
    if (notificationToggle) {
        notificationToggle.addEventListener('click', function(e) {
            e.preventDefault();
            notificationMenu.classList.toggle('show');
            if (notificationMenu.classList.contains('show')) {
                loadNotifications();
            }
        });
    }

    // Toggle notification menu for mobile
    if (mobileNotificationToggle) {
        mobileNotificationToggle.addEventListener('click', function(e) {
            e.preventDefault();
            notificationMenu.classList.toggle('show');
            if (notificationMenu.classList.contains('show')) {
                loadNotifications();
            }
        });
    }

    // Close notification menu when clicking outside
    document.addEventListener('click', function(e) {
        const isDesktopNotification = notificationToggle && notificationToggle.contains(e.target);
        const isMobileNotification = mobileNotificationToggle && mobileNotificationToggle.contains(e.target);
        const isNotificationMenu = notificationMenu && notificationMenu.contains(e.target);
        
        if (!isDesktopNotification && !isMobileNotification && !isNotificationMenu) {
            notificationMenu.classList.remove('show');
        }
    });

    // Mark all as read
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            markAllNotificationsAsRead();
        });
    }

    // Function to load notifications via AJAX
    function loadNotifications() {
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayNotifications(data.notifications);
                    updateNotificationCount(data.unread_count);
                } else {
                    console.error('Error loading notifications:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (notificationList) {
                    notificationList.innerHTML = '<div class="no-notifications">Error loading notifications</div>';
                }
            });
    }

    // Function to display notifications
    function displayNotifications(notifications) {
        if (!notificationList) return;
        
        if (notifications.length === 0) {
            notificationList.innerHTML = '<div class="no-notifications">No notifications</div>';
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const isUnread = notification.is_viewed_user === 'no';
            html += `
                <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                    <div class="notification-content">${notification.content}</div>
                    <div class="notification-time">${formatTime(notification.created_at)}</div>
                </div>
            `;
        });
        notificationList.innerHTML = html;

        // Add click event to mark as read
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.getAttribute('data-id');
                markNotificationAsRead(notificationId);
                this.classList.remove('unread');
                updateNotificationCount();
            });
        });
    }

    // Function to mark a single notification as read
    function markNotificationAsRead(notificationId) {
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `notification_id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Error marking notification as read');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Function to mark all notifications as read
    function markAllNotificationsAsRead() {
        fetch('mark_all_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.remove('unread');
                });
                updateNotificationCount(0);
            } else {
                console.error('Error marking all notifications as read');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Function to update notification count
    function updateNotificationCount(count = null) {
        if (count !== null) {
            // Update both desktop and mobile notification counts
            if (notificationCount) {
                notificationCount.textContent = count;
                notificationCount.style.display = count > 0 ? 'block' : 'none';
            }
            if (mobileNotificationCount) {
                mobileNotificationCount.textContent = count;
                mobileNotificationCount.style.display = count > 0 ? 'block' : 'none';
            }
        } else {
            // Recalculate count from DOM
            const unreadCount = document.querySelectorAll('.notification-item.unread').length;
            if (notificationCount) {
                notificationCount.textContent = unreadCount;
                notificationCount.style.display = unreadCount > 0 ? 'block' : 'none';
            }
            if (mobileNotificationCount) {
                mobileNotificationCount.textContent = unreadCount;
                mobileNotificationCount.style.display = unreadCount > 0 ? 'block' : 'none';
            }
        }
    }

    // Function to format time
    function formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
        
        return date.toLocaleDateString();
    }

    // Auto-refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);

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
        const newPassword = document.getElementById('newPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        if (newPassword) newPassword.value = '';
        if (confirmPassword) confirmPassword.value = '';
    }, 100);
});
</script>

<script src="../assets/js/profile.js"></script>