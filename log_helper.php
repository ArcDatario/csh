<?php
require_once 'db_connection.php';

/**
 * Logs an action to the system_logs table
 * 
 * @param int $account_id The ID of the admin performing the action
 * @param string $action The action performed (e.g., 'update', 'create', 'delete')
 * @param string $entity_type The type of entity affected (e.g., 'admin', 'inventory')
 * @param int $entity_id The ID of the entity affected
 * @param string $details Additional details about the action
 * @param string $is_from The source/module of the action (default: 'system')
 * @return bool True if successful, false otherwise
 */
function logAction($account_id, $action, $entity_type, $entity_id, $details = '', $is_from = 'system') {
    global $conn;
    
    // Validate and sanitize parameters
    $account_id = intval($account_id);
    $action = $conn->real_escape_string($action);
    $entity_type = $conn->real_escape_string($entity_type);
    $entity_id = intval($entity_id);
    $details = $conn->real_escape_string($details);
    $is_from = $conn->real_escape_string($is_from);
    
    // Create content string
    $content = "$action $entity_type #$entity_id";
    if (!empty($details)) {
        $content .= ": $details";
    }
    
    // Insert the log entry
    $query = "INSERT INTO system_logs (account_id, content, is_from, created_at) 
              VALUES ($account_id, '$content', '$is_from', NOW())";
    
    return $conn->query($query);
}

/**
 * Formats changes between old and new data for logging
 * 
 * @param array $old_data Associative array of old values
 * @param array $new_data Associative array of new values
 * @param array $fields_to_check Fields to compare between old and new data
 * @return string Formatted change description
 */
function formatChanges($old_data, $new_data, $fields_to_check = []) {
    $changes = [];
    
    foreach ($fields_to_check as $field) {
        $old_value = $old_data[$field] ?? '';
        $new_value = $new_data[$field] ?? '';
        
        if ($old_value !== $new_value) {
            $changes[] = "$field: '$old_value' → '$new_value'";
        }
    }
    
    // Check for additional changes not in the fields list
    foreach ($new_data as $key => $value) {
        if (!in_array($key, $fields_to_check) && strpos($key, '_changed') !== false && $value === true) {
            $field_name = str_replace('_changed', '', $key);
            $changes[] = "$field_name was updated";
        }
    }
    
    return implode(', ', $changes);
}

/**
 * Gets current admin ID from session
 * 
 * @return int Admin ID or 0 if not available
 */
function getCurrentAdminId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['admin_id']) ? intval($_SESSION['admin_id']) : 0;
}
?>