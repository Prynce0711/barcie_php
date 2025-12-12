<?php
/**
 * Enhanced Admin Management API
 * Includes audit trail, permissions, and activity tracking
 * 
 * @package BarCIE
 * @version 2.0.0
 * @created 2025-12-12
 */

session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized - Please login first'
    ]);
    exit;
}

require_once __DIR__ . '/../database/db_connect.php';

// Check if migration has been run by checking for required tables
$migration_check = $conn->query("SHOW TABLES LIKE 'admin_activity_log'");
$migration_complete = ($migration_check && $migration_check->num_rows > 0);

// Initialize modules only if migration is complete
$audit = null;
$permissions = null;

if ($migration_complete) {
    require_once __DIR__ . '/../database/modules/audit_trail.php';
    require_once __DIR__ . '/../database/modules/permissions_manager.php';
    $audit = new AuditTrail($conn);
    $permissions = new PermissionsManager($conn);
} else {
    // Migration not run - return error message
    echo json_encode([
        'success' => false,
        'message' => 'Enhanced admin management system not initialized',
        'error' => 'Database migration required',
        'action_required' => 'Please run the migration SQL file first'
    ]);
    exit;
}

$current_admin_id = $_SESSION['admin_id'] ?? 0;
$current_role = $_SESSION['admin_role'] ?? 'staff';

$response = ['success' => false, 'message' => 'Invalid request'];

try {
    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'list':
            // Check permission
            if (!$permissions->hasPermission($current_admin_id, 'admins.view')) {
                $response = ['success' => false, 'message' => 'Access denied - Insufficient permissions'];
                break;
            }
            
            $query = "
                SELECT 
                    a.id, 
                    a.username, 
                    a.email, 
                    a.full_name,
                    a.phone_number,
                    a.role,
                    a.access_level,
                    a.created_at, 
                    a.last_login,
                    a.last_activity,
                    (SELECT COUNT(*) FROM admin_activity_log WHERE admin_id = a.id AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as recent_actions
                FROM admins a 
                ORDER BY a.id ASC
            ";
            $result = $conn->query($query);
            
            if ($result) {
                $admins = [];
                while ($row = $result->fetch_assoc()) {
                    // Determine access level based on role and permissions
                    $access_level = match($row['role']) {
                        'super_admin' => 'Full System Access',
                        'manager' => 'Management Access',
                        'admin' => 'Administrative Access',
                        'staff' => 'Limited Access',
                        default => 'Unknown'
                    };
                    $row['access_level'] = $access_level;
                    
                    // Determine if admin is currently online (active in last 5 minutes)
                    $last_activity_time = strtotime($row['last_activity'] ?? '1970-01-01');
                    $seconds_since_activity = time() - $last_activity_time;
                    $row['is_currently_active'] = $seconds_since_activity < 300; // 5 minutes
                    $row['seconds_since_activity'] = $seconds_since_activity;
                    
                    // Format last seen
                    if ($row['is_currently_active']) {
                        $row['last_seen'] = 'Online now';
                    } else if ($last_activity_time > 0) {
                        $row['last_seen'] = formatLastSeen($seconds_since_activity);
                    } else {
                        $row['last_seen'] = 'Never';
                    }
                    
                    $admins[] = $row;
                }
                $response = ['success' => true, 'admins' => $admins];
            } else {
                $response = ['success' => false, 'message' => 'Failed to fetch admins: ' . $conn->error];
            }
            break;

        case 'get':
            // Get single admin with detailed info
            if (!$permissions->hasPermission($current_admin_id, 'admins.view')) {
                $response = ['success' => false, 'message' => 'Access denied'];
                break;
            }
            
            $admin_id = intval($_GET['admin_id'] ?? 0);
            
            if ($admin_id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid admin ID'];
                break;
            }

            $stmt = $conn->prepare("
                SELECT 
                    id, username, email, full_name, phone_number,
                    role,
                    created_at, last_login, last_activity
                FROM admins WHERE id = ?
            ");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Get permissions
                $row['permissions'] = $permissions->getAdminPermissions($admin_id);
                
                // Get recent activity
                $row['recent_activity'] = $audit->getAdminActivity($admin_id, 10);
                
                $response = ['success' => true, 'admin' => $row];
            } else {
                $response = ['success' => false, 'message' => 'Admin not found'];
            }
            $stmt->close();
            break;

        case 'create':
            // Create new admin
            if (!$permissions->hasPermission($current_admin_id, 'admins.create')) {
                $response = ['success' => false, 'message' => 'Access denied - Cannot create admin accounts'];
                break;
            }
            
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $full_name = trim($_POST['full_name'] ?? '');
            $phone_number = trim($_POST['phone_number'] ?? '');
            $requested_role = trim($_POST['role'] ?? 'staff');

            if (empty($username) || empty($password)) {
                $response = ['success' => false, 'message' => 'Username and password are required'];
                break;
            }

            // Validate email format
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response = ['success' => false, 'message' => 'Invalid email format'];
                break;
            }

            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $response = ['success' => false, 'message' => 'Username already exists'];
                $stmt->close();
                break;
            }
            $stmt->close();

            // Check if email already exists
            if (!empty($email)) {
                $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $response = ['success' => false, 'message' => 'Email already exists'];
                    $stmt->close();
                    break;
                }
                $stmt->close();
            }

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert new admin
            $stmt = $conn->prepare("
                INSERT INTO admins 
                (username, email, password, full_name, phone_number, role, modified_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("ssssssi", 
                $username, $email, $hashed_password, $full_name, 
                $phone_number, $requested_role, $current_admin_id
            );
            
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                
                // Log the action
                $audit->logAdminCreation($current_admin_id, $new_id, $username, $requested_role);
                
                $response = [
                    'success' => true, 
                    'message' => 'Admin created successfully', 
                    'admin_id' => $new_id
                ];
            } else {
                $response = ['success' => false, 'message' => 'Failed to create admin: ' . $stmt->error];
            }
            $stmt->close();
            break;

        case 'update':
            // Update existing admin
            if (!$permissions->hasPermission($current_admin_id, 'admins.update')) {
                $response = ['success' => false, 'message' => 'Access denied - Cannot update admin accounts'];
                break;
            }
            
            $admin_id = intval($_POST['admin_id'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $full_name = trim($_POST['full_name'] ?? '');
            $phone_number = trim($_POST['phone_number'] ?? '');
            $requested_role = trim($_POST['role'] ?? '');

            if ($admin_id <= 0 || empty($username)) {
                $response = ['success' => false, 'message' => 'Admin ID and username are required'];
                break;
            }

            // Get old data for audit trail
            $old_stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
            $old_stmt->bind_param("i", $admin_id);
            $old_stmt->execute();
            $old_data = $old_stmt->get_result()->fetch_assoc();
            $old_stmt->close();

            // Track changes
            $changes = [];
            if ($old_data['username'] !== $username) $changes['username'] = ['from' => $old_data['username'], 'to' => $username];
            if ($old_data['email'] !== $email) $changes['email'] = ['from' => $old_data['email'], 'to' => $email];
            if (!empty($requested_role) && $old_data['role'] !== $requested_role) $changes['role'] = ['from' => $old_data['role'], 'to' => $requested_role];

            // Check if username is taken by another admin
            $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $username, $admin_id);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $response = ['success' => false, 'message' => 'Username already exists'];
                $stmt->close();
                break;
            }
            $stmt->close();

            // Build UPDATE query dynamically
            $update_fields = ["username = ?", "email = ?", "full_name = ?", "phone_number = ?", "modified_by = ?", "updated_at = NOW()"];
            $params = [$username, $email, $full_name, $phone_number, $current_admin_id];
            $types = "ssssi";

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $update_fields[] = "password = ?";
                $params[] = $hashed_password;
                $types .= "s";
                $changes['password'] = 'changed';
            }

            if (!empty($requested_role) && $admin_id != $current_admin_id) {
                $update_fields[] = "role = ?";
                $params[] = $requested_role;
                $types .= "s";
            }

            $params[] = $admin_id;
            $types .= "i";

            $sql = "UPDATE admins SET " . implode(", ", $update_fields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                // Log the changes
                if (!empty($changes)) {
                    $audit->logAdminUpdate($current_admin_id, $admin_id, $changes);
                }
                
                $response = ['success' => true, 'message' => 'Admin updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update admin: ' . $stmt->error];
            }
            $stmt->close();
            break;

        case 'delete':
            // Delete admin
            if (!$permissions->hasPermission($current_admin_id, 'admins.delete')) {
                $response = ['success' => false, 'message' => 'Access denied - Cannot delete admin accounts'];
                break;
            }
            
            $admin_id = intval($_POST['admin_id'] ?? 0);

            if ($admin_id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid admin ID'];
                break;
            }

            // Prevent deleting yourself
            if ($admin_id == $current_admin_id) {
                $response = ['success' => false, 'message' => 'You cannot delete your own account'];
                break;
            }

            // Get admin info for audit
            $stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin_data = $result->fetch_assoc();
            $stmt->close();

            if (!$admin_data) {
                $response = ['success' => false, 'message' => 'Admin not found'];
                break;
            }

            // Hard delete (since is_active column doesn't exist)
            $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    // Log the deletion
                    $audit->logAdminDeletion($current_admin_id, $admin_id, $admin_data['username']);
                    
                    $response = ['success' => true, 'message' => 'Admin deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Admin not found'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Failed to delete admin: ' . $stmt->error];
            }
            $stmt->close();
            break;

        case 'get_activity':
            // Get activity log
            if (!$permissions->hasPermission($current_admin_id, 'activity.view_all')) {
                // Can only view own activity
                $admin_id = $current_admin_id;
            } else {
                $admin_id = intval($_GET['admin_id'] ?? 0);
            }
            
            $limit = intval($_GET['limit'] ?? 50);
            $offset = intval($_GET['offset'] ?? 0);
            
            if ($admin_id > 0) {
                $activity = $audit->getAdminActivity($admin_id, $limit, $offset);
            } else {
                $activity = $audit->getRecentActivity($limit);
            }
            
            $response = ['success' => true, 'activity' => $activity];
            break;

        case 'get_permissions':
            // Get admin permissions
            if (!$permissions->hasPermission($current_admin_id, 'admins.view')) {
                $response = ['success' => false, 'message' => 'Access denied'];
                break;
            }
            
            $admin_id = intval($_GET['admin_id'] ?? 0);
            
            if ($admin_id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid admin ID'];
                break;
            }
            
            $admin_permissions = $permissions->getAdminPermissions($admin_id);
            $grouped = [];
            
            foreach ($admin_permissions as $perm) {
                $category = $perm['category'] ?? 'other';
                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }
                $grouped[$category][] = $perm;
            }
            
            $response = ['success' => true, 'permissions' => $grouped];
            break;

        case 'update_permission':
            // Update custom permission for an admin
            if (!$permissions->hasPermission($current_admin_id, 'admins.manage_permissions')) {
                $response = ['success' => false, 'message' => 'Access denied - Cannot manage permissions'];
                break;
            }
            
            $admin_id = intval($_POST['admin_id'] ?? 0);
            $permission_key = $_POST['permission_key'] ?? '';
            $granted = (bool)($_POST['granted'] ?? false);
            
            if ($granted) {
                $success = $permissions->grantCustomPermission($admin_id, $permission_key, $current_admin_id);
            } else {
                $success = $permissions->revokeCustomPermission($admin_id, $permission_key, $current_admin_id);
            }
            
            if ($success) {
                $audit->logPermissionChange($current_admin_id, $admin_id, $permission_key, $granted);
                $response = ['success' => true, 'message' => 'Permission updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update permission'];
            }
            break;

        case 'stats':
            // Get dashboard statistics
            if (!$permissions->hasPermission($current_admin_id, 'reports.view')) {
                $response = ['success' => false, 'message' => 'Access denied'];
                break;
            }
            
            $stats = [
                'total_admins' => 0,
                'active_admins' => 0,
                'active_sessions' => 0,
                'recent_actions' => 0,
                'failed_logins_today' => 0
            ];
            
            // Total admins
            $result = $conn->query("SELECT COUNT(*) as count FROM admins");
            $stats['total_admins'] = $result->fetch_assoc()['count'];
            
            // Currently active (last 5 minutes)
            $result = $conn->query("SELECT COUNT(*) as count FROM admins WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
            $stats['active_admins'] = $result->fetch_assoc()['count'];
            
            // Recent actions (last 24 hours)
            $result = $conn->query("SELECT COUNT(*) as count FROM admin_activity_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['recent_actions'] = $result->fetch_assoc()['count'];
            
            // Failed logins today
            $result = $conn->query("SELECT COUNT(*) as count FROM admin_activity_log WHERE action_type = 'login_failed' AND DATE(created_at) = CURDATE()");
            $stats['failed_logins_today'] = $result->fetch_assoc()['count'];
            
            $response = ['success' => true, 'stats' => $stats];
            break;

        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
            break;
    }

} catch (Exception $e) {
    error_log("Admin Management Error: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

/**
 * Format last seen time
 */
function formatLastSeen($seconds) {
    if ($seconds < 60) return 'Just now';
    if ($seconds < 3600) return floor($seconds / 60) . ' min ago';
    if ($seconds < 86400) return floor($seconds / 3600) . ' hr ago';
    if ($seconds < 604800) return floor($seconds / 86400) . ' days ago';
    return date('M j, Y', time() - $seconds);
}

$conn->close();
echo json_encode($response);
exit;
