<?php
/**
 * Audit Trail Module
 * Logs all admin activities for security and compliance
 * 
 * @package BarCIE
 * @version 1.0.0
 * @created 2025-12-12
 */

class AuditTrail {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }
    
    /**
     * Log an admin activity
     * 
     * @param int $admin_id The ID of the admin performing the action
     * @param string $action_type The type of action (login, create_admin, etc.)
     * @param string $action_description Human-readable description
     * @param string $target_type Type of entity affected (optional)
     * @param int $target_id ID of entity affected (optional)
     * @param array $changes Array of changes made (optional)
     * @return bool Success status
     */
    public function log($admin_id, $action_type, $action_description, $target_type = null, $target_id = null, $changes = null) {
        try {
            // Get client information
            $ip_address = $this->getClientIP();
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Convert changes to JSON
            $changes_json = $changes ? json_encode($changes) : null;
            
            $stmt = $this->conn->prepare("
                INSERT INTO admin_activity_log 
                (admin_id, action_type, action_description, target_type, target_id, ip_address, user_agent, changes_json)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param(
                "isssisss",
                $admin_id,
                $action_type,
                $action_description,
                $target_type,
                $target_id,
                $ip_address,
                $user_agent,
                $changes_json
            );
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Audit Trail Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log a login attempt (success or failure)
     */
    public function logLogin($admin_id, $success = true, $username = null) {
        $action_type = $success ? 'login' : 'login_failed';
        $description = $success 
            ? "Admin logged in successfully" 
            : "Failed login attempt" . ($username ? " for username: $username" : "");
        
        return $this->log($admin_id, $action_type, $description);
    }
    
    /**
     * Log a logout
     */
    public function logLogout($admin_id) {
        return $this->log($admin_id, 'logout', "Admin logged out");
    }
    
    /**
     * Log admin creation
     */
    public function logAdminCreation($creator_id, $new_admin_id, $username, $role) {
        return $this->log(
            $creator_id,
            'create_admin',
            "Created new admin account: $username with role: $role",
            'admin',
            $new_admin_id,
            ['username' => $username, 'role' => $role]
        );
    }
    
    /**
     * Log admin update
     */
    public function logAdminUpdate($updater_id, $updated_admin_id, $changes) {
        $change_desc = implode(', ', array_keys($changes));
        return $this->log(
            $updater_id,
            'update_admin',
            "Updated admin (ID: $updated_admin_id) - Changed: $change_desc",
            'admin',
            $updated_admin_id,
            $changes
        );
    }
    
    /**
     * Log admin deletion
     */
    public function logAdminDeletion($deleter_id, $deleted_admin_id, $username) {
        return $this->log(
            $deleter_id,
            'delete_admin',
            "Deleted admin account: $username (ID: $deleted_admin_id)",
            'admin',
            $deleted_admin_id,
            ['username' => $username]
        );
    }
    
    /**
     * Log permission change
     */
    public function logPermissionChange($admin_id, $target_admin_id, $permission_key, $granted) {
        $action = $granted ? 'granted' : 'revoked';
        return $this->log(
            $admin_id,
            'manage_permissions',
            "Permission $action: $permission_key for admin ID: $target_admin_id",
            'admin',
            $target_admin_id,
            ['permission' => $permission_key, 'granted' => $granted]
        );
    }
    
    /**
     * Get activity log for an admin
     */
    public function getAdminActivity($admin_id, $limit = 50, $offset = 0) {
        $stmt = $this->conn->prepare("
            SELECT * FROM admin_activity_log 
            WHERE admin_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        
        $stmt->bind_param("iii", $admin_id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['changes_json']) {
                $row['changes'] = json_decode($row['changes_json'], true);
            }
            $activities[] = $row;
        }
        
        return $activities;
    }
    
    /**
     * Get recent activity across all admins
     */
    public function getRecentActivity($limit = 100, $action_type = null) {
        $query = "
            SELECT 
                aal.*,
                a.username,
                a.role
            FROM admin_activity_log aal
            LEFT JOIN admins a ON aal.admin_id = a.id
        ";
        
        if ($action_type) {
            $query .= " WHERE aal.action_type = ?";
        }
        
        $query .= " ORDER BY aal.created_at DESC LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        
        if ($action_type) {
            $stmt->bind_param("si", $action_type, $limit);
        } else {
            $stmt->bind_param("i", $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['changes_json']) {
                $row['changes'] = json_decode($row['changes_json'], true);
            }
            $activities[] = $row;
        }
        
        return $activities;
    }
    
    /**
     * Get activity statistics
     */
    public function getActivityStats($admin_id = null, $days = 30) {
        $query = "
            SELECT 
                action_type,
                COUNT(*) as count,
                DATE(created_at) as date
            FROM admin_activity_log
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        ";
        
        if ($admin_id) {
            $query .= " AND admin_id = ?";
        }
        
        $query .= " GROUP BY action_type, DATE(created_at) ORDER BY date DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($admin_id) {
            $stmt->bind_param("ii", $days, $admin_id);
        } else {
            $stmt->bind_param("i", $days);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $ip_list = explode(',', $_SERVER[$key]);
                foreach ($ip_list as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
    
    /**
     * Clean old activity logs (for maintenance)
     */
    public function cleanOldLogs($days = 365) {
        $stmt = $this->conn->prepare("
            DELETE FROM admin_activity_log 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        
        $stmt->bind_param("i", $days);
        return $stmt->execute();
    }
}
