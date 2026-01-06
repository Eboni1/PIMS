-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2026 at 04:05 AM
-- Server version: 10.6.15-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pims`
--

-- --------------------------------------------------------

--
-- Table structure for table `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('full','database','files') NOT NULL,
  `include_files` tinyint(1) DEFAULT 0,
  `include_database` tinyint(1) DEFAULT 0,
  `file_path` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `online_backup` tinyint(1) DEFAULT 0,
  `cloud_provider` varchar(50) DEFAULT NULL,
  `cloud_backup_url` varchar(500) DEFAULT NULL,
  `cloud_backup_status` enum('pending','uploading','completed','failed') DEFAULT NULL,
  `cloud_backup_error` text DEFAULT NULL,
  `cloud_backup_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `backups`
--

INSERT INTO `backups` (`id`, `name`, `type`, `include_files`, `include_database`, `file_path`, `created_by`, `created_at`, `online_backup`, `cloud_provider`, `cloud_backup_url`, `cloud_backup_status`, `cloud_backup_error`, `cloud_backup_at`) VALUES
(8, 'Daily Backup', 'full', 1, 1, '../backups/Daily Backup_2026-01-05_13-41-33', 1, '2026-01-05 12:41:33', 0, NULL, NULL, NULL, NULL, NULL),
(9, 'Daily Backup', 'full', 1, 1, '../backups/Daily Backup_2026-01-05_14-05-54', 1, '2026-01-05 13:05:54', 0, NULL, NULL, NULL, NULL, NULL),
(10, 'online Backup', 'full', 1, 1, '../backups/online Backup_2026-01-05_14-29-41', 1, '2026-01-05 13:29:41', 1, '0', 'https://drive.google.com/file/d/1qrf12E9fs98ak_we_UXsTnEyGt4z5pcR/view', 'completed', NULL, '2026-01-05 14:13:59'),
(11, 'Daily Backup', 'full', 1, 1, '../backups/Daily Backup_2026-01-05_14-46-20', 1, '2026-01-05 13:46:21', 0, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `backup_execution_logs`
--

CREATE TABLE `backup_execution_logs` (
  `id` int(11) NOT NULL,
  `scheduled_backup_id` int(11) NOT NULL,
  `execution_status` enum('running','completed','failed') NOT NULL,
  `started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `backup_id` int(11) DEFAULT NULL COMMENT 'ID of the created backup if successful',
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_login_attempts`
--

CREATE TABLE `failed_login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_blocked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `online_backup_configs`
--

CREATE TABLE `online_backup_configs` (
  `id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `config_name` varchar(100) NOT NULL,
  `api_key` text DEFAULT NULL,
  `api_secret` text DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `bucket_name` varchar(200) DEFAULT NULL,
  `folder_path` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `online_backup_configs`
--

INSERT INTO `online_backup_configs` (`id`, `provider`, `config_name`, `api_key`, `api_secret`, `access_token`, `refresh_token`, `bucket_name`, `folder_path`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'google_drive', 'Google Drive Backup', '822354506716-8on8lknqkudd71ae2v935aq7hkvib4kq.apps.googleusercontent.com', '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', 'ya29.a0Aa7pCA_l51pZ0iktGhYIs71GN9o2QiGcsand7k9CxDQQpyBQtRjgc7NMToZUlZnMFC9nSmeFQrc7zGywdTGWMPBDy9iBOEpf_QFf3eJOUcJZNUfePIGPhO7sZb3DotyyEx2airAPPBUTE-RATZ8DGzG2PkyO3RKPCyk9iPKl588ACXk9gqQvLeS7vheLMBYlHRxe5V4aCgYKAe4SARESFQHGX2MimGU0dLbDFtN0I3aLKjKrYw0206', '1//0evabxpw2eEOKCgYIARAAGA4SNwF-L9IrMe3TQwU8ekRvEgkTEhkFCqiF1gTHfJAjgWmoOswjzBmrsnJj2HPUfXOctbCgDhxvpc8', '', 'backup', 1, 1, '2026-01-04 13:52:31', '2026-01-05 13:59:23'),
(2, 'dropbox', 'Dropbox Backup', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2026-01-04 13:52:31', '2026-01-04 13:52:31'),
(3, 'onedrive', 'OneDrive Backup', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2026-01-04 13:52:31', '2026-01-04 13:52:31');

-- --------------------------------------------------------

--
-- Table structure for table `password_policies`
--

CREATE TABLE `password_policies` (
  `id` int(11) NOT NULL,
  `policy_name` varchar(100) NOT NULL,
  `min_length` int(11) DEFAULT 8,
  `require_uppercase` tinyint(1) DEFAULT 1,
  `require_lowercase` tinyint(1) DEFAULT 1,
  `require_numbers` tinyint(1) DEFAULT 1,
  `require_special_chars` tinyint(1) DEFAULT 1,
  `max_age_days` int(11) DEFAULT 90,
  `prevent_reuse` int(11) DEFAULT 5,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_policies`
--

INSERT INTO `password_policies` (`id`, `policy_name`, `min_length`, `require_uppercase`, `require_lowercase`, `require_numbers`, `require_special_chars`, `max_age_days`, `prevent_reuse`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Default Policy', 8, 1, 1, 1, 1, 90, 5, 1, '2026-01-06 02:28:39', '2026-01-06 02:28:39');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `module`, `created_at`) VALUES
(1, 'users.create', 'Create new users', 'users', '2026-01-04 01:09:27'),
(2, 'users.read', 'View users list', 'users', '2026-01-04 01:09:27'),
(3, 'users.update', 'Edit user information', 'users', '2026-01-04 01:09:27'),
(4, 'users.delete', 'Delete users', 'users', '2026-01-04 01:09:27'),
(5, 'users.activate', 'Activate/deactivate users', 'users', '2026-01-04 01:09:27'),
(6, 'inventory.create', 'Add new products', 'inventory', '2026-01-04 01:09:27'),
(7, 'inventory.read', 'View products list', 'inventory', '2026-01-04 01:09:27'),
(8, 'inventory.update', 'Edit product information', 'inventory', '2026-01-04 01:09:27'),
(9, 'inventory.delete', 'Delete products', 'inventory', '2026-01-04 01:09:27'),
(10, 'inventory.transaction.in', 'Add stock (IN transactions)', 'inventory', '2026-01-04 01:09:27'),
(11, 'inventory.transaction.out', 'Remove stock (OUT transactions)', 'inventory', '2026-01-04 01:09:27'),
(12, 'categories.create', 'Create new categories', 'categories', '2026-01-04 01:09:27'),
(13, 'categories.read', 'View categories list', 'categories', '2026-01-04 01:09:27'),
(14, 'categories.update', 'Edit category information', 'categories', '2026-01-04 01:09:27'),
(15, 'categories.delete', 'Delete categories', 'categories', '2026-01-04 01:09:27'),
(16, 'reports.view', 'View system reports', 'reports', '2026-01-04 01:09:27'),
(17, 'reports.export', 'Export reports', 'reports', '2026-01-04 01:09:27'),
(18, 'system.settings', 'Access system settings', 'system', '2026-01-04 01:09:27'),
(19, 'system.logs', 'View system logs', 'system', '2026-01-04 01:09:27'),
(20, 'system.backup', 'Create system backup', 'system', '2026-01-04 01:09:27'),
(21, 'system.audit', 'Access security audit', 'system', '2026-01-04 01:09:27');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `price` decimal(10,2) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role` enum('system_admin','admin','office_admin','user') NOT NULL,
  `permission_id` int(11) NOT NULL,
  `can_create` tinyint(1) DEFAULT 0,
  `can_read` tinyint(1) DEFAULT 1,
  `can_update` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `permission_id`, `can_create`, `can_read`, `can_update`, `can_delete`, `created_at`) VALUES
(1, 'system_admin', 12, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(2, 'system_admin', 15, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(3, 'system_admin', 13, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(4, 'system_admin', 14, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(5, 'system_admin', 6, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(6, 'system_admin', 9, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(7, 'system_admin', 7, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(8, 'system_admin', 10, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(9, 'system_admin', 11, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(10, 'system_admin', 8, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(11, 'system_admin', 17, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(12, 'system_admin', 16, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(13, 'system_admin', 21, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(14, 'system_admin', 20, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(15, 'system_admin', 19, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(16, 'system_admin', 18, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(17, 'system_admin', 5, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(18, 'system_admin', 1, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(19, 'system_admin', 4, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(20, 'system_admin', 2, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(21, 'system_admin', 3, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(32, 'admin', 12, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(33, 'admin', 15, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(34, 'admin', 13, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(35, 'admin', 14, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(36, 'admin', 6, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(37, 'admin', 9, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(38, 'admin', 7, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(39, 'admin', 10, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(40, 'admin', 11, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(41, 'admin', 8, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(42, 'admin', 17, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(43, 'admin', 16, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(44, 'admin', 21, 0, 1, 0, 0, '2026-01-04 01:09:27'),
(45, 'admin', 20, 0, 1, 0, 0, '2026-01-04 01:09:27'),
(46, 'admin', 19, 0, 1, 0, 0, '2026-01-04 01:09:27'),
(47, 'admin', 18, 0, 1, 0, 0, '2026-01-04 01:09:27'),
(48, 'admin', 5, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(49, 'admin', 1, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(50, 'admin', 4, 1, 1, 1, 0, '2026-01-04 01:09:27'),
(51, 'admin', 2, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(52, 'admin', 3, 1, 1, 1, 1, '2026-01-04 01:09:27'),
(63, 'office_admin', 12, 1, 0, 0, 0, '2026-01-04 01:09:27'),
(64, 'office_admin', 15, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(65, 'office_admin', 13, 0, 1, 0, 0, '2026-01-04 01:09:27'),
(66, 'office_admin', 14, 1, 0, 1, 0, '2026-01-04 01:09:27'),
(67, 'office_admin', 6, 1, 0, 0, 0, '2026-01-04 01:09:27'),
(68, 'office_admin', 9, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(69, 'office_admin', 7, 0, 1, 0, 0, '2026-01-04 01:09:27'),
(70, 'office_admin', 10, 1, 0, 0, 0, '2026-01-04 01:09:27'),
(71, 'office_admin', 11, 1, 0, 0, 0, '2026-01-04 01:09:27'),
(72, 'office_admin', 8, 1, 0, 1, 0, '2026-01-04 01:09:27'),
(73, 'office_admin', 17, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(74, 'office_admin', 16, 0, 1, 0, 0, '2026-01-04 01:09:27'),
(75, 'office_admin', 21, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(76, 'office_admin', 20, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(77, 'office_admin', 19, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(78, 'office_admin', 18, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(79, 'office_admin', 5, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(80, 'office_admin', 1, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(81, 'office_admin', 4, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(82, 'office_admin', 2, 0, 1, 0, 0, '2026-01-04 01:09:27'),
(83, 'office_admin', 3, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(94, 'user', 12, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(95, 'user', 15, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(96, 'user', 13, 0, 1, 0, 0, '2026-01-04 01:09:27'),
(97, 'user', 14, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(98, 'user', 6, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(99, 'user', 9, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(100, 'user', 7, 0, 1, 0, 0, '2026-01-04 01:09:27'),
(101, 'user', 10, 1, 0, 0, 0, '2026-01-04 01:09:27'),
(102, 'user', 11, 1, 0, 0, 0, '2026-01-04 01:09:27'),
(103, 'user', 8, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(104, 'user', 17, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(105, 'user', 16, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(106, 'user', 21, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(107, 'user', 20, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(108, 'user', 19, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(109, 'user', 18, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(110, 'user', 5, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(111, 'user', 1, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(112, 'user', 4, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(113, 'user', 2, 0, 0, 0, 0, '2026-01-04 01:09:27'),
(114, 'user', 3, 0, 0, 0, 0, '2026-01-04 01:09:27');

-- --------------------------------------------------------

--
-- Table structure for table `security_alerts`
--

CREATE TABLE `security_alerts` (
  `id` int(11) NOT NULL,
  `alert_type` enum('critical','high','medium','low') NOT NULL,
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
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_audit_logs`
--

CREATE TABLE `security_audit_logs` (
  `id` int(11) NOT NULL,
  `audit_id` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `score` int(11) NOT NULL,
  `issues_found` int(11) NOT NULL,
  `critical_issues` int(11) DEFAULT 0,
  `high_issues` int(11) DEFAULT 0,
  `medium_issues` int(11) DEFAULT 0,
  `low_issues` int(11) DEFAULT 0,
  `audit_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`audit_data`)),
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_metrics`
--

CREATE TABLE `security_metrics` (
  `id` int(11) NOT NULL,
  `metric_date` date NOT NULL,
  `total_users` int(11) NOT NULL,
  `active_users` int(11) NOT NULL,
  `failed_logins` int(11) DEFAULT 0,
  `successful_logins` int(11) DEFAULT 0,
  `password_changes` int(11) DEFAULT 0,
  `security_alerts` int(11) DEFAULT 0,
  `audit_score` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('system_admin','admin','office_admin','user') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `password_changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `must_change_password` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone`, `address`, `password_hash`, `role`, `first_name`, `last_name`, `is_active`, `created_at`, `updated_at`, `last_login`, `failed_login_attempts`, `is_locked`, `password_changed_at`, `must_change_password`) VALUES
(1, 'system_admin', 'admin@pims.com', '', '', '$2y$10$sTwhCxd.JawevaKAgnfMaO1p.PJ34C9ROfU4nbTkmuHHdDOzcq/nm', 'system_admin', 'System', 'Administrator', 1, '2026-01-03 13:00:37', '2026-01-04 13:21:03', NULL, 0, 0, '2026-01-06 02:21:26', 0),
(2, 'wjll2022-2920-98466@bicol-u.edu.ph', 'wjll2022-2920-98466@bicol-u.edu.ph', NULL, NULL, '$2y$10$0mPC7iEVtjGUOVHLqGdmNe5whIhEuPVQfmdliPsnSdupq20au5cl2', 'admin', 'Walton', 'loneza', 1, '2026-01-03 22:34:21', '2026-01-03 22:34:21', NULL, 0, 0, '2026-01-06 02:21:26', 0),
(4, 'notlawsfinds@gmail.com', 'notlawsfinds@gmail.com', NULL, NULL, '$2y$10$ekzQ67QhSp7H3QhmLyjbxeUwgXPw4d35vEm0mlbQX98WGDJvVRkry', 'office_admin', 'Joshua ', 'Escano', 1, '2026-01-03 22:44:32', '2026-01-03 22:44:32', NULL, 0, 0, '2026-01-06 02:21:26', 0),
(5, 'waltonloneza@gmail.com', 'waltonloneza@gmail.com', NULL, NULL, '$2y$10$/pK71fdth8E2iJQVIMq5E.MaRIPI.4P7hzzwdgYWsHJlvhT866Sbi', 'admin', 'Walton', 'loneza', 1, '2026-01-03 22:49:38', '2026-01-03 22:49:38', NULL, 0, 0, '2026-01-06 02:21:26', 0),
(11, 'waltielappy@gmail.com', 'waltielappy@gmail.com', NULL, NULL, '$2y$10$hO1CH2GRcHTr81fLfLGokOk6kTlm9zja8X4ipgsq3Pb1ffMFS5bmu', 'user', 'Elton John', 'Moises', 0, '2026-01-04 00:39:40', '2026-01-04 00:45:06', NULL, 0, 0, '2026-01-06 02:21:26', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_password_history`
--

CREATE TABLE `user_password_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `backup_execution_logs`
--
ALTER TABLE `backup_execution_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_attempt_time` (`attempt_time`),
  ADD KEY `idx_is_blocked` (`is_blocked`);

--
-- Indexes for table `online_backup_configs`
--
ALTER TABLE `online_backup_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `password_policies`
--
ALTER TABLE `password_policies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `policy_name` (`policy_name`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `security_alerts`
--
ALTER TABLE `security_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_alert_type` (`alert_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_alert_affected_user` (`affected_user_id`),
  ADD KEY `fk_alert_resolved_by` (`resolved_by`);

--
-- Indexes for table `security_audit_logs`
--
ALTER TABLE `security_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_id` (`audit_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_performed_by` (`performed_by`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `security_metrics`
--
ALTER TABLE `security_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date` (`metric_date`),
  ADD KEY `idx_metric_date` (`metric_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_password_history`
--
ALTER TABLE `user_password_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `backup_execution_logs`
--
ALTER TABLE `backup_execution_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_login_attempts`
--
ALTER TABLE `failed_login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `online_backup_configs`
--
ALTER TABLE `online_backup_configs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_policies`
--
ALTER TABLE `password_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `security_alerts`
--
ALTER TABLE `security_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_audit_logs`
--
ALTER TABLE `security_audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `security_metrics`
--
ALTER TABLE `security_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user_password_history`
--
ALTER TABLE `user_password_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `backups`
--
ALTER TABLE `backups`
  ADD CONSTRAINT `backups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `online_backup_configs`
--
ALTER TABLE `online_backup_configs`
  ADD CONSTRAINT `online_backup_configs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `security_alerts`
--
ALTER TABLE `security_alerts`
  ADD CONSTRAINT `fk_alert_affected_user` FOREIGN KEY (`affected_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_alert_resolved_by` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `security_audit_logs`
--
ALTER TABLE `security_audit_logs`
  ADD CONSTRAINT `fk_audit_performed_by` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_password_history`
--
ALTER TABLE `user_password_history`
  ADD CONSTRAINT `fk_password_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
