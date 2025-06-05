<?php
require_once '../../db_connection.php';
session_start();

$role = $_SESSION['admin_role'] ?? '';

$notificationField = '';
$viewedField = '';
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
}

if ($notificationField) {
    $query = "SELECT * FROM notification 
              WHERE $notificationField = 'yes'
              ORDER BY created_at DESC LIMIT 10";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        while ($notification = $result->fetch_assoc()) {
            $isUnread = empty($notification[$viewedField]) || $notification[$viewedField] == 'no';
            $iconClass = 'info'; // Default class
            $icon = 'fa-info-circle'; // Default icon
            
            // Determine icon based on status column
            if (!empty($notification['status'])) {
                switch(strtolower($notification['status'])) {
                    case 'approved':
                        $iconClass = 'success';
                        $icon = 'fa-check-circle';
                        break;
                    case 'alert':
                        $iconClass = 'warning';
                        $icon = 'fa-exclamation-circle';
                        break;
                    // Add more cases if needed
                }
            }
            
            // Format date as "Month Day - Time"
            $formattedDate = '';
            if (!empty($notification['created_at'])) {
                $date = new DateTime($notification['created_at']);
                $formattedDate = $date->format('F d - g:i A'); // e.g. "June 06 - 4:18 PM"
            }
            ?>
            <div class="notification-item <?php echo $isUnread ? 'unread' : ''; ?>" data-id="<?php echo $notification['id']; ?>">
                <div class="notification-icon <?php echo $iconClass; ?>">
                    <i class="fas <?php echo $icon; ?>"></i>
                </div>
                <div class="notification-content">
                    <p class="notification-message"><?php echo htmlspecialchars($notification['content']); ?></p>
                    <span class="notification-time"><?php echo $formattedDate; ?></span>
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