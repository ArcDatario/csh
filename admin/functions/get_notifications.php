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
            $iconClass = 'info';
            $icon = 'fa-info-circle';
            
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