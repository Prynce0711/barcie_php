-- Enhanced Admin Management System Migration
-- Created: 2025-12-12
-- Description: Adds audit trail, permissions, activity tracking, and enhanced admin features

-- ============================================================================
-- 1. ADD NEW COLUMNS TO ADMINS TABLE
-- ============================================================================

-- Add full_name if not exists (should already exist per schema)
-- Skip if column exists
-- ALTER TABLE `admins` ADD COLUMN `full_name` VARCHAR(255) NULL AFTER `email`;

-- Add phone number
-- Skip if column exists
-- ALTER TABLE `admins` ADD COLUMN `phone_number` VARCHAR(20) NULL AFTER `full_name`;

-- Add modified_by to track who made changes
ALTER TABLE `admins` ADD COLUMN `modified_by` INT(11) NULL;

-- Add failed login attempts tracking
ALTER TABLE `admins` ADD COLUMN `failed_login_attempts` INT DEFAULT 0;

-- Add last activity timestamp
ALTER TABLE `admins` ADD COLUMN `last_activity` TIMESTAMP NULL;

-- ============================================================================
-- 2. CREATE ADMIN ACTIVITY LOG TABLE (AUDIT TRAIL)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `admin_activity_log` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT(11) NOT NULL,
  `action_type` ENUM(
    'login', 'logout', 'login_failed',
    'create_admin', 'update_admin', 'delete_admin',
    'create_booking', 'update_booking', 'cancel_booking',
    'create_item', 'update_item', 'delete_item',
    'update_settings', 'view_reports',
    'manage_permissions', 'other'
  ) NOT NULL,
  `action_description` TEXT NULL,
  `target_type` VARCHAR(50) NULL COMMENT 'Type of entity affected (admin, booking, item, etc.)',
  `target_id` INT(11) NULL COMMENT 'ID of affected entity',
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `changes_json` JSON NULL COMMENT 'JSON of what changed (before/after)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_admin_id` (`admin_id`),
  INDEX `idx_action_type` (`action_type`),
  INDEX `idx_target` (`target_type`, `target_id`),
  INDEX `idx_created_at` (`created_at`),
  
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Audit trail for all admin actions';

-- ============================================================================
-- 3. CREATE ADMIN PERMISSIONS TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `admin_permissions` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `permission_key` VARCHAR(100) UNIQUE NOT NULL,
  `permission_name` VARCHAR(255) NOT NULL,
  `permission_description` TEXT NULL,
  `category` VARCHAR(50) NULL COMMENT 'bookings, inventory, reports, users, settings',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX `idx_permission_key` (`permission_key`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Available permissions in the system';

-- ============================================================================
-- 4. CREATE ROLE PERMISSIONS MAPPING TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `role` ENUM('super_admin', 'admin', 'manager', 'staff') NOT NULL,
  `permission_id` INT(11) NOT NULL,
  `granted` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE KEY `unique_role_permission` (`role`, `permission_id`),
  INDEX `idx_role` (`role`),
  
  FOREIGN KEY (`permission_id`) REFERENCES `admin_permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Maps roles to permissions';

-- ============================================================================
-- 5. CREATE ADMIN CUSTOM PERMISSIONS TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `admin_custom_permissions` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT(11) NOT NULL,
  `permission_id` INT(11) NOT NULL,
  `granted` BOOLEAN DEFAULT TRUE COMMENT 'TRUE=allow, FALSE=deny (overrides role)',
  `granted_by` INT(11) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NULL COMMENT 'Optional expiry for temporary permissions',
  
  UNIQUE KEY `unique_admin_permission` (`admin_id`, `permission_id`),
  INDEX `idx_admin_id` (`admin_id`),
  INDEX `idx_expires_at` (`expires_at`),
  
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `admin_permissions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`granted_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Custom per-admin permissions overrides';

-- ============================================================================
-- 6. CREATE ADMIN SESSIONS TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `admin_sessions` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT(11) NOT NULL,
  `session_token` VARCHAR(255) UNIQUE NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  
  INDEX `idx_admin_id` (`admin_id`),
  INDEX `idx_session_token` (`session_token`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_expires_at` (`expires_at`),
  
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Active admin sessions for tracking';

-- ============================================================================
-- 7. INSERT DEFAULT PERMISSIONS
-- ============================================================================

INSERT IGNORE INTO `admin_permissions` (`permission_key`, `permission_name`, `permission_description`, `category`) VALUES
-- Booking Management
('bookings.view', 'View Bookings', 'Can view all bookings and reservations', 'bookings'),
('bookings.create', 'Create Bookings', 'Can create new bookings', 'bookings'),
('bookings.update', 'Update Bookings', 'Can modify existing bookings', 'bookings'),
('bookings.cancel', 'Cancel Bookings', 'Can cancel bookings', 'bookings'),
('bookings.approve', 'Approve Bookings', 'Can approve pending bookings', 'bookings'),

-- Inventory Management
('inventory.view', 'View Inventory', 'Can view rooms and facilities', 'inventory'),
('inventory.create', 'Create Inventory', 'Can add new rooms/facilities', 'inventory'),
('inventory.update', 'Update Inventory', 'Can modify rooms/facilities', 'inventory'),
('inventory.delete', 'Delete Inventory', 'Can remove rooms/facilities', 'inventory'),

-- Payment Management
('payments.view', 'View Payments', 'Can view payment records', 'payments'),
('payments.process', 'Process Payments', 'Can process and verify payments', 'payments'),
('payments.refund', 'Issue Refunds', 'Can process refunds', 'payments'),

-- User/Admin Management
('users.view', 'View Users', 'Can view customer accounts', 'users'),
('users.manage', 'Manage Users', 'Can edit customer accounts', 'users'),
('admins.view', 'View Admins', 'Can view admin list', 'users'),
('admins.create', 'Create Admins', 'Can create new admin accounts', 'users'),
('admins.update', 'Update Admins', 'Can edit admin accounts', 'users'),
('admins.delete', 'Delete Admins', 'Can delete admin accounts', 'users'),
('admins.manage_permissions', 'Manage Permissions', 'Can assign custom permissions', 'users'),

-- Reports
('reports.view', 'View Reports', 'Can view analytics and reports', 'reports'),
('reports.export', 'Export Reports', 'Can export reports to CSV/PDF', 'reports'),
('reports.financial', 'Financial Reports', 'Can view financial reports', 'reports'),

-- Settings
('settings.view', 'View Settings', 'Can view system settings', 'settings'),
('settings.update', 'Update Settings', 'Can modify system settings', 'settings'),
('settings.system', 'System Settings', 'Can access critical system settings', 'settings'),

-- Activity Monitoring
('activity.view_own', 'View Own Activity', 'Can view own activity log', 'monitoring'),
('activity.view_all', 'View All Activity', 'Can view all admin activity logs', 'monitoring');

-- ============================================================================
-- 8. ASSIGN DEFAULT ROLE PERMISSIONS
-- ============================================================================

-- Super Admin: All permissions
INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`, `granted`)
SELECT 'super_admin', `id`, TRUE FROM `admin_permissions`;

-- Manager: Most permissions except critical system settings
INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`, `granted`)
SELECT 'manager', `id`, TRUE FROM `admin_permissions` 
WHERE `permission_key` NOT IN ('settings.system', 'admins.delete', 'admins.manage_permissions');

-- Admin: Booking and inventory management
INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`, `granted`)
SELECT 'admin', `id`, TRUE FROM `admin_permissions` 
WHERE `permission_key` IN (
  'bookings.view', 'bookings.create', 'bookings.update', 'bookings.cancel', 'bookings.approve',
  'inventory.view', 'inventory.update',
  'payments.view', 'payments.process',
  'users.view',
  'reports.view',
  'activity.view_own'
);

-- Staff: View-only access
INSERT IGNORE INTO `role_permissions` (`role`, `permission_id`, `granted`)
SELECT 'staff', `id`, TRUE FROM `admin_permissions` 
WHERE `permission_key` IN (
  'bookings.view',
  'inventory.view',
  'payments.view',
  'activity.view_own'
);

-- ============================================================================
-- 9. ADD INDEXES FOR PERFORMANCE
-- ============================================================================

ALTER TABLE `admins` 
  ADD INDEX `idx_last_activity` (`last_activity`),
  ADD INDEX `idx_modified_by` (`modified_by`);

-- ============================================================================
-- 10. CREATE VIEW FOR ADMIN ACTIVITY SUMMARY
-- ============================================================================

CREATE OR REPLACE VIEW `v_admin_activity_summary` AS
SELECT 
  a.id,
  a.username,
  a.role,
  a.is_active,
  a.last_login,
  a.last_activity,
  COUNT(DISTINCT aal.id) as total_actions,
  COUNT(DISTINCT CASE WHEN aal.action_type = 'login_failed' THEN aal.id END) as failed_logins,
  MAX(aal.created_at) as last_action_time,
  (SELECT COUNT(*) FROM admin_sessions WHERE admin_id = a.id AND is_active = TRUE) as active_sessions
FROM admins a
LEFT JOIN admin_activity_log aal ON a.id = aal.admin_id AND aal.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY a.id;

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================

-- To verify the migration, run:
-- SELECT COUNT(*) FROM admin_activity_log;
-- SELECT COUNT(*) FROM admin_permissions;
-- SELECT COUNT(*) FROM role_permissions;
-- SELECT * FROM v_admin_activity_summary;
