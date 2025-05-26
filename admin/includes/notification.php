<?php
require_once '../db_connection.php';

// Get current user's role
$role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : '';

// Determine which notification field to check based on role
$notificationField = '';
switch($role) {
    case 'Owner':
        $notificationField = 'notify_owner';
        $viewedField = 'is_viewed_owner';
        break;
    case 'General Manager':
        $notificationField = 'notify_manager';
        $viewedField = 'is_viewed_manager';
        break;
    case 'Field Manager':
        $notificationField = 'notify_field';
        $viewedField = 'is_viewed_field_manager';
        break;
    case 'Designer':
        $notificationField = 'notify_designer';
        $viewedField = 'is_viewed_designer';
        break;
    case 'Secretary':
        $notificationField = 'notify_secretary';
        $viewedField = 'is_viewed_secretary';
        break;
    default:
        $notificationField = '';
        $viewedField = '';
}

// Get unread notifications count
$unreadCount = 0;
if ($notificationField) {
    $countQuery = "SELECT COUNT(*) as count FROM notification WHERE $notificationField = 'yes' AND ($viewedField = '' OR $viewedField IS NULL OR $viewedField = 'no')";
    $countResult = $conn->query($countQuery);
    if ($countResult) {
        $unreadCount = $countResult->fetch_assoc()['count'];
    }
}
?>

<div class="notification-bell">
    <i class="fas fa-bell"></i>
    <?php if ($unreadCount > 0): ?>
        <span class="notification-badge"><?php echo $unreadCount; ?></span>
    <?php endif; ?>
    
    <div class="notification-dropdown">
        <div class="notification-header">
            <h4 class="notification-title">Notifications</h4>
            <?php if ($notificationField): ?>
                <button class="mark-all-read">Mark all as read</button>
            <?php endif; ?>
        </div>
        <div class="notification-list">
            <?php
            if ($notificationField) {
                // Get notifications for this user's role
                $query = "SELECT * FROM notification 
                          WHERE $notificationField = 'yes'
                          ORDER BY created_at DESC LIMIT 10";
                $result = $conn->query($query);
                
                if ($result->num_rows > 0) {
                    while ($notification = $result->fetch_assoc()) {
                        $isUnread = empty($notification[$viewedField]) || $notification[$viewedField] == 'no';
                        $iconClass = 'info';
                        $icon = 'fa-info-circle';
                        
                        // Determine icon based on content
                        if (strpos($notification['content'], 'approved') !== false) {
                            $iconClass = 'success';
                            $icon = 'fa-check-circle';
                        } elseif (strpos($notification['content'], 'alert') !== false) {
                            $iconClass = 'warning';
                            $icon = 'fa-exclamation-circle';
                        }
                        ?>
                        <div class="notification-item <?php echo $isUnread ? 'unread' : ''; ?>" data-id="<?php echo $notification['id']; ?>">
                            <div class="notification-icon <?php echo $iconClass; ?>">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="notification-content">
                                <p class="notification-message"><?php echo htmlspecialchars($notification['content']); ?></p>
                                <span class="notification-time">
                                    <?php 
                                    $created = new DateTime($notification['created_at']);
                                    $now = new DateTime();
                                    $interval = $created->diff($now);
                                    
                                    if ($interval->y > 0) echo $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
                                    elseif ($interval->m > 0) echo $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
                                    elseif ($interval->d > 0) echo $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
                                    elseif ($interval->h > 0) echo $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                                    elseif ($interval->i > 0) echo $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
                                    else echo 'Just now';
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="notification-item">
                            <div class="notification-content">
                                <p class="notification-message">No notifications found</p>
                            </div>
                          </div>';
                }
            } else {
                echo '<div class="notification-item">
                        <div class="notification-content">
                            <p class="notification-message">No notifications available for your role</p>
                        </div>
                      </div>';
            }
            ?>
        </div>
    </div>


<script>
// Function to show push notification
function showPushNotification(title, message) {
    // Check if browser supports notifications
    if (!("Notification" in window)) {
        console.log("This browser does not support desktop notification");
        return;
    }

    // Check if notification permissions have already been granted
    if (Notification.permission === "granted") {
        // If it's okay let's create a notification
        new Notification(title, {
            body: message,
            icon: 'assets/images/notification-icon.jpeg'
        });
    } 
    // Otherwise, we need to ask the user for permission
    else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(function (permission) {
            // If the user accepts, let's create a notification
            if (permission === "granted") {
                new Notification(title, {
                    body: message,
                    icon: 'assets/images/notification-icon.jpeg'
                });
            }
        });
    }
}

// Function to check for new notifications
function checkForNewNotifications() {
    fetch('functions/get_notification_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            const currentCount = badge ? parseInt(badge.textContent) || 0 : 0;
            
            if (data.count > currentCount) {
                // New notification arrived
                const diff = data.count - currentCount;
                
                // Update badge
                if (badge) {
                    badge.textContent = data.count;
                } else if (data.count > 0) {
                    // Create badge if it doesn't exist
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.textContent = data.count;
                    document.querySelector('.notification-bell').appendChild(newBadge);
                }
                
                // Show push notification for new ones
                if (diff > 0) {
                    showPushNotification('New Notification', `You have ${diff} new notification${diff > 1 ? 's' : ''}`);
                }
                
                // Refresh notifications list
                refreshNotifications();
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}

// Function to refresh notifications list
function refreshNotifications() {
    fetch('functions/get_notifications.php')
        .then(response => response.text())
        .then(html => {
            document.querySelector('.notification-list').innerHTML = html;
        })
        .catch(error => console.error('Error refreshing notifications:', error));
}

// Mark notification as read when clicked
document.querySelector('.notification-list')?.addEventListener('click', function(e) {
    const notificationItem = e.target.closest('.notification-item');
    if (notificationItem && notificationItem.classList.contains('unread')) {
        const notificationId = notificationItem.dataset.id;
        fetch('functions/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notificationItem.classList.remove('unread');
                updateBadgeCount();
            }
        });
    }
});

// Mark all as read
document.querySelector('.mark-all-read')?.addEventListener('click', function() {
    fetch('functions/mark_all_notifications_read.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove badge
                const badge = document.querySelector('.notification-badge');
                if (badge) badge.remove();
                
                // Remove unread classes
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });
            }
        })
        .catch(error => console.error('Error marking notifications as read:', error));
});

// Update badge count
function updateBadgeCount() {
    fetch('functions/get_notification_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                } else {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'notification-badge';
                    newBadge.textContent = data.count;
                    document.querySelector('.notification-bell').appendChild(newBadge);
                }
            } else if (badge) {
                badge.remove();
            }
        });
}

// Request notification permission on page load
document.addEventListener('DOMContentLoaded', function() {
    if (!("Notification" in window)) {
        console.log("This browser does not support desktop notification");
    } else if (Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission();
    }
    
    // Initial check
    checkForNewNotifications();
    
    // Check for new notifications every 10 seconds
    setInterval(checkForNewNotifications, 10000);
});
</script>