-- Create system_settings table
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system settings
INSERT INTO `system_settings` (`setting_name`, `setting_value`, `setting_type`, `description`) VALUES
('system_name', 'PIMS', 'string', 'System display name'),
('system_email', 'waltielappy@gmail.com', 'string', 'System email address for notifications'),
('maintenance_mode', '0', 'boolean', 'Enable/disable maintenance mode'),
('allow_registration', '1', 'boolean', 'Allow new user registration'),
('session_timeout', '3600', 'integer', 'User session timeout in seconds'),
('max_login_attempts', '5', 'integer', 'Maximum failed login attempts before lockout'),
('password_min_length', '8', 'integer', 'Minimum password length'),
('backup_retention_days', '30', 'integer', 'Number of days to keep backup files'),
('email_notifications', '1', 'boolean', 'Enable email notifications'),
('debug_mode', '0', 'boolean', 'Enable debug mode and error logging'),
('primary_color', '#191BA9', 'string', 'Primary theme color'),
('secondary_color', '#5CC2F2', 'string', 'Secondary theme color'),
('accent_color', '#FF6B6B', 'string', 'Accent theme color'),
('system_logo', '', 'string', 'Path to system logo image');

-- Create system_logs table for audit trail
CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `module` (`module`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create security_logs table for security audit
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'low',
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_type` (`event_type`),
  KEY `severity` (`severity`),
  KEY `timestamp` (`timestamp`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create login_logs table for failed login tracking
CREATE TABLE IF NOT EXISTS `login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `failure_reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `ip_address` (`ip_address`),
  KEY `success` (`success`),
  KEY `attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create scheduled_backups table for backup scheduling
CREATE TABLE IF NOT EXISTS `scheduled_backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `backup_type` enum('full','database','files') DEFAULT 'full',
  `schedule_type` enum('daily','weekly','monthly') DEFAULT 'daily',
  `schedule_day` int(11) DEFAULT NULL,
  `schedule_time` time DEFAULT '00:00:00',
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `last_run` timestamp NULL DEFAULT NULL,
  `next_run` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`),
  KEY `next_run` (`next_run`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
