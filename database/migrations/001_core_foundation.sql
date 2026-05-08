CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `error_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `error_level` VARCHAR(20) NOT NULL,
    `error_message` TEXT NOT NULL,
    `error_file` TEXT NULL,
    `error_line` INT NULL,
    `error_trace` LONGTEXT NULL,
    `request_url` TEXT NULL,
    `request_method` VARCHAR(10) NULL,
    `user_agent` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('app_name', 'Dayem ERP', 'general'),
('environment', 'development', 'general'),
('session_timeout', '1800', 'general');