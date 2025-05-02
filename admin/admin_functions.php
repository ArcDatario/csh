<?php
require_once 'auth_check.php';

// Database connection
function getDBConnection() {
    $host = 'localhost';
    $dbname = 'your_database_name';
    $username = 'your_username';
    $password = 'your_password';
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Get all admins
function getAllAdmins() {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, fullname, role FROM admins ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add new admin
function addAdmin($username, $fullname, $password, $role) {
    $conn = getDBConnection();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO admins (username, fullname, password, role) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$username, $fullname, $hashedPassword, $role]);
}

// Get admin by ID
function getAdminById($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, fullname, role FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update admin
function updateAdmin($id, $username, $fullname, $role) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE admins SET username = ?, fullname = ?, role = ? WHERE id = ?");
    return $stmt->execute([$username, $fullname, $role, $id]);
}

// Update admin with password
function updateAdminWithPassword($id, $username, $fullname, $password, $role) {
    $conn = getDBConnection();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE admins SET username = ?, fullname = ?, password = ?, role = ? WHERE id = ?");
    return $stmt->execute([$username, $fullname, $hashedPassword, $role, $id]);
}

// Delete admin
function deleteAdmin($id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
    return $stmt->execute([$id]);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if (isset($_POST['action'])) {
            $response = ['success' => false, 'message' => ''];
            
            switch ($_POST['action']) {
                case 'add_admin':
                    if (!empty($_POST['username']) && !empty($_POST['fullname']) && !empty($_POST['password']) && !empty($_POST['role'])) {
                        if (addAdmin($_POST['username'], $_POST['fullname'], $_POST['password'], $_POST['role'])) {
                            $response['success'] = true;
                            $response['message'] = 'Admin added successfully';
                        } else {
                            $response['message'] = 'Failed to add admin';
                        }
                    } else {
                        $response['message'] = 'All fields are required';
                    }
                    break;
                    
                case 'get_admin':
                    if (!empty($_POST['id'])) {
                        $admin = getAdminById($_POST['id']);
                        if ($admin) {
                            $response['success'] = true;
                            $response['data'] = $admin;
                        } else {
                            $response['message'] = 'Admin not found';
                        }
                    }
                    break;
                    
                case 'update_admin':
                    if (!empty($_POST['id']) && !empty($_POST['username']) && !empty($_POST['fullname']) && !empty($_POST['role'])) {
                        if (empty($_POST['password'])) {
                            $success = updateAdmin($_POST['id'], $_POST['username'], $_POST['fullname'], $_POST['role']);
                        } else {
                            $success = updateAdminWithPassword($_POST['id'], $_POST['username'], $_POST['fullname'], $_POST['password'], $_POST['role']);
                        }
                        
                        if ($success) {
                            $response['success'] = true;
                            $response['message'] = 'Admin updated successfully';
                        } else {
                            $response['message'] = 'Failed to update admin';
                        }
                    } else {
                        $response['message'] = 'Required fields are missing';
                    }
                    break;
                    
                case 'delete_admin':
                    if (!empty($_POST['id'])) {
                        if (deleteAdmin($_POST['id'])) {
                            $response['success'] = true;
                            $response['message'] = 'Admin deleted successfully';
                        } else {
                            $response['message'] = 'Failed to delete admin';
                        }
                    }
                    break;
                    
                case 'load_admins':
                    $admins = getAllAdmins();
                    $response['success'] = true;
                    $response['data'] = $admins;
                    break;
                    
                default:
                    $response['message'] = 'Invalid action';
            }
            
            echo json_encode($response);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}
?>