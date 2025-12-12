<?php
session_start();
header('Content-Type: application/json');

// Debug logging
error_log("Admin Management API called - Action: " . ($_REQUEST['action'] ?? 'none'));
error_log("Session admin_logged_in: " . (isset($_SESSION['admin_logged_in']) ? 'true' : 'false'));

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    error_log("Unauthorized access attempt");
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized - Please login first',
        'debug' => [
            'session_exists' => isset($_SESSION['admin_logged_in']),
            'session_value' => $_SESSION['admin_logged_in'] ?? 'not set'
        ]
    ]);
    exit;
}

// Only super_admin can access admin management
$current_role = $_SESSION['admin_role'] ?? 'staff';
if ($current_role !== 'super_admin') {
    error_log("Admin management access denied for role: $current_role");
    echo json_encode([
        'success' => false,
        'message' => 'Access denied - Only Super Administrators can manage admin accounts',
        'role' => $current_role
    ]);
    exit;
}

require_once __DIR__ . '/../database/db_connect.php';

// Detect whether `role` column exists to remain compatible with older DBs
$hasRoleColumn = false;
try {
    $colRes = $conn->query("SHOW COLUMNS FROM `admins` LIKE 'role'");
    if ($colRes && $colRes->num_rows > 0) {
        $hasRoleColumn = true;
    }
} catch (Throwable $e) {
    // ignore and assume column missing
}

$response = ['success' => false, 'message' => 'Invalid request'];

try {
    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'list':
            // Get all admins. If `role` column doesn't exist, fall back to query without it.
            if ($hasRoleColumn) {
                $query = "SELECT id, username, email, role, created_at, last_login FROM admins ORDER BY id ASC";
            } else {
                $query = "SELECT id, username, email, created_at, last_login FROM admins ORDER BY id ASC";
            }
            $result = $conn->query($query);
            
            if ($result) {
                $admins = [];
                while ($row = $result->fetch_assoc()) {
                    $admins[] = $row;
                }
                $response = ['success' => true, 'admins' => $admins];
            } else {
                $response = ['success' => false, 'message' => 'Failed to fetch admins: ' . $conn->error];
            }
            break;

        case 'get':
            // Get single admin
            $admin_id = intval($_GET['admin_id'] ?? 0);
            
            if ($admin_id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid admin ID'];
                break;
            }

            if ($hasRoleColumn) {
                $stmt = $conn->prepare("SELECT id, username, email, role, created_at, last_login FROM admins WHERE id = ?");
            } else {
                $stmt = $conn->prepare("SELECT id, username, email, created_at, last_login FROM admins WHERE id = ?");
            }
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // If role column is missing, ensure we return a default role for frontend compatibility
                if (!isset($row['role'])) {
                    $row['role'] = 'staff';
                }
                $response = ['success' => true, 'admin' => $row];
            } else {
                $response = ['success' => false, 'message' => 'Admin not found'];
            }
            $stmt->close();
            break;

        case 'create':
            // Create new admin
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            // Role handling: only allow assigning role if current user has permission
            $requested_role = trim($_POST['role'] ?? '');
            $current_role = $_SESSION['admin_role'] ?? 'staff';
            $allowed_setters = ['super_admin', 'manager'];
            if ($hasRoleColumn && in_array($current_role, $allowed_setters, true) && !empty($requested_role)) {
                $role_to_set = $requested_role;
            } else {
                // If DB doesn't support role, always default to staff
                $role_to_set = 'staff';
            }

            error_log("CREATE ADMIN - Username: $username, Email: $email, Password length: " . strlen($password));
            error_log("POST data: " . print_r($_POST, true));

            if (empty($username) || empty($password)) {
                error_log("CREATE ADMIN FAILED - Missing username or password");
                $response = ['success' => false, 'message' => 'Username and password are required'];
                break;
            }

            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
            if (!$stmt) {
                error_log("CREATE ADMIN FAILED - Prepare failed: " . $conn->error);
                $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
                break;
            }
            
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                error_log("CREATE ADMIN FAILED - Username already exists: $username");
                $response = ['success' => false, 'message' => 'Username already exists'];
                $stmt->close();
                break;
            }
            $stmt->close();

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            error_log("CREATE ADMIN - Password hashed successfully");

            // Insert new admin (include role if column exists)
            if ($hasRoleColumn) {
                $stmt = $conn->prepare("INSERT INTO admins (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            } else {
                $stmt = $conn->prepare("INSERT INTO admins (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
            }
            if (!$stmt) {
                error_log("CREATE ADMIN FAILED - Prepare INSERT failed: " . $conn->error);
                $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
                break;
            }
            
            if ($hasRoleColumn) {
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $role_to_set);
            } else {
                $stmt->bind_param("sss", $username, $email, $hashed_password);
            }
            
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                error_log("CREATE ADMIN SUCCESS - New admin ID: $new_id");
                $response = ['success' => true, 'message' => 'Admin created successfully', 'admin_id' => $new_id];
            } else {
                error_log("CREATE ADMIN FAILED - Execute failed: " . $stmt->error);
                $response = ['success' => false, 'message' => 'Failed to create admin: ' . $stmt->error];
            }
            $stmt->close();
            break;

        case 'update':
            // Update existing admin
            $admin_id = intval($_POST['admin_id'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $requested_role = trim($_POST['role'] ?? '');
            $current_role = $_SESSION['admin_role'] ?? 'staff';
            $allowed_setters = ['super_admin', 'manager'];
            // Prevent changing own role via this endpoint
            $can_set_role = $hasRoleColumn && in_array($current_role, $allowed_setters, true) && ($admin_id != ($_SESSION['admin_id'] ?? 0));

            if ($admin_id <= 0 || empty($username)) {
                $response = ['success' => false, 'message' => 'Admin ID and username are required'];
                break;
            }

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

            // Update admin
            if (!empty($password)) {
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                if ($can_set_role && !empty($requested_role)) {
                    $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $username, $email, $hashed_password, $requested_role, $admin_id);
                } else {
                    $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $username, $email, $hashed_password, $admin_id);
                }
            } else {
                // Update without changing password
                if ($can_set_role && !empty($requested_role)) {
                    $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $username, $email, $requested_role, $admin_id);
                } else {
                    $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $username, $email, $admin_id);
                }
            }
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Admin updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update admin: ' . $stmt->error];
            }
            $stmt->close();
            break;

        case 'delete':
            // Delete admin
            $admin_id = intval($_POST['admin_id'] ?? 0);

            if ($admin_id <= 0) {
                $response = ['success' => false, 'message' => 'Invalid admin ID'];
                break;
            }

            // Prevent deleting yourself
            if ($admin_id == ($_SESSION['admin_id'] ?? 0)) {
                $response = ['success' => false, 'message' => 'You cannot delete your own account'];
                break;
            }

            // Count total admins
            $count_result = $conn->query("SELECT COUNT(*) as count FROM admins");
            $count = $count_result->fetch_assoc()['count'];
            
            if ($count <= 1) {
                $response = ['success' => false, 'message' => 'Cannot delete the last admin'];
                break;
            }

            $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response = ['success' => true, 'message' => 'Admin deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Admin not found'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Failed to delete admin: ' . $stmt->error];
            }
            $stmt->close();
            break;

        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
            break;
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

$conn->close();
echo json_encode($response);
exit;
