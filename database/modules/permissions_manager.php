<?php
/**
 * Permissions Management Module
 * Handles role-based and custom permissions
 * 
 * @package BarCIE
 * @version 1.0.0
 * @created 2025-12-12
 */

class PermissionsManager {
    private $conn;
    private $cache = [];
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }
    
    /**
     * Check if an admin has a specific permission
     * Checks: role permissions + custom permissions (custom overrides role)
     */
    public function hasPermission($admin_id, $permission_key) {
        // Super admin always has all permissions
        $admin_role = $this->getAdminRole($admin_id);
        if ($admin_role === 'super_admin') {
            return true;
        }
        
        // Check cache first
        $cache_key = "perm_{$admin_id}_{$permission_key}";
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        // Get permission ID
        $permission_id = $this->getPermissionId($permission_key);
        if (!$permission_id) {
            return false;
        }
        
        // Check custom permissions first (they override role permissions)
        $custom_perm = $this->getCustomPermission($admin_id, $permission_id);
        if ($custom_perm !== null) {
            $this->cache[$cache_key] = $custom_perm;
            return $custom_perm;
        }
        
        // Fall back to role permissions
        $role_perm = $this->getRolePermission($admin_role, $permission_id);
        $this->cache[$cache_key] = $role_perm;
        return $role_perm;
    }
    
    /**
     * Get all permissions for an admin
     */
    public function getAdminPermissions($admin_id) {
        $admin_role = $this->getAdminRole($admin_id);
        
        // Get all permissions
        $all_perms = $this->getAllPermissions();
        
        // If super admin, return all as granted
        if ($admin_role === 'super_admin') {
            return array_map(function($perm) {
                $perm['granted'] = true;
                $perm['source'] = 'role';
                return $perm;
            }, $all_perms);
        }
        
        $result = [];
        foreach ($all_perms as $perm) {
            $perm_id = $perm['id'];
            
            // Check custom first
            $custom = $this->getCustomPermission($admin_id, $perm_id);
            if ($custom !== null) {
                $perm['granted'] = $custom;
                $perm['source'] = 'custom';
            } else {
                // Check role
                $role_grant = $this->getRolePermission($admin_role, $perm_id);
                $perm['granted'] = $role_grant;
                $perm['source'] = 'role';
            }
            
            $result[] = $perm;
        }
        
        return $result;
    }
    
    /**
     * Grant custom permission to an admin
     */
    public function grantCustomPermission($admin_id, $permission_key, $granted_by, $expires_at = null) {
        $permission_id = $this->getPermissionId($permission_key);
        if (!$permission_id) {
            return false;
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO admin_custom_permissions 
            (admin_id, permission_id, granted, granted_by, expires_at)
            VALUES (?, ?, TRUE, ?, ?)
            ON DUPLICATE KEY UPDATE granted = TRUE, granted_by = ?, expires_at = ?
        ");
        
        $stmt->bind_param("iiisis", $admin_id, $permission_id, $granted_by, $expires_at, $granted_by, $expires_at);
        return $stmt->execute();
    }
    
    /**
     * Revoke custom permission from an admin
     */
    public function revokeCustomPermission($admin_id, $permission_key, $granted_by) {
        $permission_id = $this->getPermissionId($permission_key);
        if (!$permission_id) {
            return false;
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO admin_custom_permissions 
            (admin_id, permission_id, granted, granted_by)
            VALUES (?, ?, FALSE, ?)
            ON DUPLICATE KEY UPDATE granted = FALSE, granted_by = ?
        ");
        
        $stmt->bind_param("iiii", $admin_id, $permission_id, $granted_by, $granted_by);
        return $stmt->execute();
    }
    
    /**
     * Remove custom permission (fall back to role permission)
     */
    public function removeCustomPermission($admin_id, $permission_key) {
        $permission_id = $this->getPermissionId($permission_key);
        if (!$permission_id) {
            return false;
        }
        
        $stmt = $this->conn->prepare("
            DELETE FROM admin_custom_permissions 
            WHERE admin_id = ? AND permission_id = ?
        ");
        
        $stmt->bind_param("ii", $admin_id, $permission_id);
        return $stmt->execute();
    }
    
    /**
     * Get all permissions grouped by category
     */
    public function getPermissionsByCategory() {
        $stmt = $this->conn->query("
            SELECT * FROM admin_permissions 
            ORDER BY category, permission_name
        ");
        
        $grouped = [];
        while ($row = $stmt->fetch_assoc()) {
            $category = $row['category'] ?? 'other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $row;
        }
        
        return $grouped;
    }
    
    /**
     * Get permissions for a specific role
     */
    public function getRolePermissions($role) {
        $stmt = $this->conn->prepare("
            SELECT p.*, rp.granted
            FROM admin_permissions p
            LEFT JOIN role_permissions rp ON p.id = rp.permission_id AND rp.role = ?
            ORDER BY p.category, p.permission_name
        ");
        
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row;
        }
        
        return $permissions;
    }
    
    /**
     * Update role permission
     */
    public function updateRolePermission($role, $permission_key, $granted) {
        $permission_id = $this->getPermissionId($permission_key);
        if (!$permission_id) {
            return false;
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO role_permissions (role, permission_id, granted)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE granted = ?
        ");
        
        $granted_int = $granted ? 1 : 0;
        $stmt->bind_param("siii", $role, $permission_id, $granted_int, $granted_int);
        return $stmt->execute();
    }
    
    // ==================== PRIVATE HELPER METHODS ====================
    
    private function getAdminRole($admin_id) {
        $stmt = $this->conn->prepare("SELECT role FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['role'] ?? 'staff';
    }
    
    private function getPermissionId($permission_key) {
        $stmt = $this->conn->prepare("SELECT id FROM admin_permissions WHERE permission_key = ?");
        $stmt->bind_param("s", $permission_key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['id'] ?? null;
    }
    
    private function getCustomPermission($admin_id, $permission_id) {
        $stmt = $this->conn->prepare("
            SELECT granted FROM admin_custom_permissions 
            WHERE admin_id = ? AND permission_id = ?
            AND (expires_at IS NULL OR expires_at > NOW())
        ");
        
        $stmt->bind_param("ii", $admin_id, $permission_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return (bool)$row['granted'];
        }
        
        return null;
    }
    
    private function getRolePermission($role, $permission_id) {
        $stmt = $this->conn->prepare("
            SELECT granted FROM role_permissions 
            WHERE role = ? AND permission_id = ?
        ");
        
        $stmt->bind_param("si", $role, $permission_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return (bool)$row['granted'];
        }
        
        return false;
    }
    
    private function getAllPermissions() {
        $stmt = $this->conn->query("SELECT * FROM admin_permissions ORDER BY category, permission_name");
        
        $permissions = [];
        while ($row = $stmt->fetch_assoc()) {
            $permissions[] = $row;
        }
        
        return $permissions;
    }
    
    /**
     * Clear permission cache
     */
    public function clearCache() {
        $this->cache = [];
    }
}
