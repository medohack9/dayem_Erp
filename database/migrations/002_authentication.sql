CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    `status` ENUM('active', 'suspended', 'terminated') NOT NULL DEFAULT 'active',
    `failed_login_attempts` INT NOT NULL DEFAULT 0,
    `locked_until` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `identifier` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `outcome` ENUM('success', 'failed', 'locked') NOT NULL,
    `failure_reason` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `session_id` VARCHAR(128) NOT NULL UNIQUE,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_activity` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX `idx_users_email` ON `users`(`email`);
CREATE INDEX `idx_users_phone` ON `users`(`phone`);
CREATE INDEX `idx_users_status` ON `users`(`status`);
CREATE INDEX `idx_users_role` ON `users`(`role`);
CREATE INDEX `idx_auth_logs_user_id` ON `auth_logs`(`user_id`);
CREATE INDEX `idx_auth_logs_created_at` ON `auth_logs`(`created_at`);
CREATE INDEX `idx_auth_logs_outcome` ON `auth_logs`(`outcome`);
CREATE INDEX `idx_auth_logs_ip_address` ON `auth_logs`(`ip_address`);
CREATE INDEX `idx_user_sessions_user_id` ON `user_sessions`(`user_id`);
CREATE INDEX `idx_user_sessions_session_id` ON `user_sessions`(`session_id`);

INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`, `status`) VALUES ('مدير النظام', 'admin@dayem.com', '01023810857', '$2y$10$htoYafZz5fwSNUFViAdZuePHxZP2Qrf8ugmctBlsz3Cr2xW/ualFq', 'admin', 'active');