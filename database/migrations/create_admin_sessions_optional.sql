-- Optional: Create admin_sessions table
-- Note: This table is NOT required for the system to work
-- The enhanced admin management system works fine without it

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Active admin sessions for tracking (OPTIONAL)';
