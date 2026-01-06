-- Create system_logs table for audit trail
CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `action` varchar(100) NOT NULL,
    `module` varchar(50) NOT NULL,
    `description` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_module` (`module`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create security_logs table for security events
CREATE TABLE IF NOT EXISTS `security_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `event_type` enum('critical','high','medium','low') NOT NULL,
    `title` varchar(200) NOT NULL,
    `description` text NOT NULL,
    `category` varchar(50) NOT NULL,
    `status` enum('open','investigating','resolved','false_positive') DEFAULT 'open',
    `affected_user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `resolved_by` int(11) DEFAULT NULL,
    `resolved_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_status` (`status`),
    KEY `idx_category` (`category`),
    KEY `idx_created_at` (`created_at`),
    KEY `fk_alert_affected_user` (`affected_user_id`),
    KEY `fk_alert_resolved_by` (`resolved_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create login_logs table for login tracking
CREATE TABLE IF NOT EXISTS `login_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `username` varchar(100) NOT NULL,
    `success` tinyint(1) DEFAULT 0,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_username` (`username`),
    KEY `idx_success` (`success`),
    KEY `idx_attempt_time` (`attempt_time`),
    KEY `idx_ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create scheduled_backups table for backup scheduling
CREATE TABLE IF NOT EXISTS `scheduled_backups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `schedule_type` enum('daily','weekly','monthly','custom') NOT NULL,
    `schedule_day` varchar(20) DEFAULT NULL,
    `schedule_time` time DEFAULT NULL,
    `backup_type` enum('full','database','files') NOT NULL,
    `include_files` tinyint(1) DEFAULT 0,
    `include_database` tinyint(1) DEFAULT 0,
    `is_active` tinyint(1) DEFAULT 1,
    `last_run` datetime DEFAULT NULL,
    `next_run` datetime DEFAULT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_schedule_type` (`schedule_type`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_next_run` (`next_run`),
    KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add foreign key constraints (run these separately after tables are created)
-- Note: Make sure the users table exists before running these ALTER commands

-- Foreign key for security_logs table (affected_user_id)
ALTER TABLE `security_logs` 
ADD CONSTRAINT `fk_security_affected_user` FOREIGN KEY (`affected_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Foreign key for security_logs table (resolved_by)
ALTER TABLE `security_logs` 
ADD CONSTRAINT `fk_security_resolved_by` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Foreign key for system_logs table
ALTER TABLE `system_logs` 
ADD CONSTRAINT `fk_system_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Foreign key for login_logs table
ALTER TABLE `login_logs` 
ADD CONSTRAINT `fk_login_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- Foreign key for scheduled_backups table
ALTER TABLE `scheduled_backups` 
ADD CONSTRAINT `fk_backup_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
