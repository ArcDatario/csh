<?php
require_once 'db_connection.php';

/**
 * Logs an action to the system_logs table with concise, human-readable messages
 */
function logAction($account_id, $action, $entity_type, $entity_id = 0, $details = '', $is_from = 'system', $actor_role = '', $actor_name = '') {
    global $conn;

    // Check database connection
    if (!$conn || $conn->connect_error) {
        error_log("Database connection error in logAction");
        return false;
    }

    $account_id = intval($account_id);
    $entity_id = intval($entity_id);
    if ($account_id <= 0) $account_id = 0;

    $action = $conn->real_escape_string(trim($action));
    $entity_type = $conn->real_escape_string(trim($entity_type));
    $details = $conn->real_escape_string(trim($details));
    $is_from = $conn->real_escape_string(trim($is_from));
    $actor_role = $conn->real_escape_string(trim($actor_role));
    $actor_name = $conn->real_escape_string(trim($actor_name));

    // Generate the log message
    $content = generateActionDescription($action, $entity_type, $details, $actor_role, $actor_name);

    // Insert the log entry
    $query = "INSERT INTO system_logs (account_id, content, is_from, created_at) 
              VALUES ($account_id, '$content', '$is_from', NOW())";

    $result = $conn->query($query);
    if (!$result) {
        error_log("Log insertion failed: " . $conn->error);
        error_log("Query: " . $query);
    }

    return $result;
}

/**
 * Generates a descriptive, concise message for the log
 */
function generateActionDescription($action, $entity_type, $details = '', $actor_role = '', $actor_name = '') {
    if (!empty($details)) {
        // Capitalize the first letter of the content
        return ucfirst($details);
    }
    $action_text = getActionDisplayText($action);
    $entity_name = getEntityDisplayName($entity_type);
    return ucfirst("$action_text $entity_name"); // Capitalize first word
}

/**
 * Converts entity types to readable names (used only if details not provided)
 */
function getEntityDisplayName($entity_type) {
    $entity_map = [
        'admin' => 'Administrator',
        'user' => 'User',
        'inventory' => 'Inventory item',
        'product' => 'Product',
        'order' => 'Order',
        'category' => 'Category',
        'system' => 'System setting',
        'profile' => 'Profile',
        'permission' => 'Permission setting'
    ];
    return $entity_map[strtolower($entity_type)] ?? ucfirst($entity_type);
}

/**
 * Converts action types to readable verbs
 */
function getActionDisplayText($action) {
    $action_map = [
        'create' => 'Created',
        'add' => 'Added',
        'update' => 'Updated',
        'edit' => 'Edited',
        'delete' => 'Deleted',
        'remove' => 'Removed',
        'login' => 'Logged in to',
        'logout' => 'Logged out from',
        'approve' => 'Approved',
        'reject' => 'Rejected',
        'export' => 'Exported',
        'import' => 'Imported',
        'change' => 'Changed',
        'reset' => 'Reset',
        'disable' => 'Disabled',
        'enable' => 'Enabled'
    ];
    return $action_map[strtolower($action)] ?? ucfirst($action);
}

/**
 * Formats changes between old and new data for logging
 * Produces concise, human-readable messages:
 * - "Changed Username from 'old' to 'new'"
 * - "Changed multiple fields"
 * - "Updated Password for 'username'"
 */
function formatChanges($old_data, $new_data, $fields_to_check = [], $target_name = '') {
    $changes = [];
    $changed_fields = [];

    // Compare main fields
    foreach ($fields_to_check as $field) {
        $old_value = $old_data[$field] ?? '';
        $new_value = $new_data[$field] ?? '';

        if ($old_value !== $new_value) {
            $changed_fields[] = $field;
            if ($field === 'role' && empty($old_value)) {
                $changes[] = "Role"; // just use field name
            } else {
                $changes[] = formatFieldName($field);
            }
        }
    }

    // Include password as a normal field
    $password_changed = !empty($new_data['password_changed']) && !empty($new_data['username']);
    if ($password_changed) {
        $changes[] = 'Password';
    }

    if (empty($changes)) return "Made no specific changes";

    $summary = '';

    if (count($changes) <= 2) {
        // 1 or 2 fields changed → list them
        $summary = "Changed " . implode(', ', $changes);
    } else {
        // 3+ fields changed → summarize count
        $summary = "Changed " . count($changes) . " fields";
    }

    if (!empty($target_name)) {
        $summary .= " for '$target_name'";
    }

    return ucfirst($summary);
}

/**
 * Formats field names to be more readable
 */
function formatFieldName($field) {
    $field_map = [
        'username' => 'Username',
        'fullname' => 'Full name',
        'role' => 'Role',
        'email' => 'Email address',
        'password' => 'Password',
        'status' => 'Status',
        'title' => 'Title',
        'description' => 'Description',
        'quantity' => 'Quantity',
        'price' => 'Price',
        'category' => 'Category'
    ];
    return $field_map[strtolower($field)] ?? ucfirst(str_replace('_', ' ', $field));
}

/**
 * Gets current admin ID from session
 */
function getCurrentAdminId() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['admin_id']) ? intval($_SESSION['admin_id']) : 0;
}
?>
