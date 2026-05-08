CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `manager_id` INT NULL,
    `default_salary` DECIMAL(10,2) NULL,
    `deleted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_departments_name` (`name`),
    KEY `idx_departments_manager_id` (`manager_id`),
    KEY `idx_departments_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_departments_manager` FOREIGN KEY (`manager_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(50) NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT NOT NULL,
    `details` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_logs_entity` (`entity_type`, `entity_id`),
    KEY `idx_logs_user_id` (`user_id`),
    KEY `idx_logs_created_at` (`created_at`),
    KEY `idx_logs_action` (`action`),
    CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'department_id');

SET @sql = IF(@col_exists = 0, 'ALTER TABLE `users` ADD COLUMN `department_id` INT NULL AFTER `role`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_users_department_id');

SET @sql2 = IF(@idx_exists = 0, 'ALTER TABLE `users` ADD KEY `idx_users_department_id` (`department_id`)', 'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND CONSTRAINT_NAME = 'fk_users_department');

SET @sql3 = IF(@fk_exists = 0, 'ALTER TABLE `users` ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL', 'SELECT 1');
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

INSERT IGNORE INTO `departments` (`name`, `default_salary`) VALUES
    ('الموارد البشرية', 5000.00),
    ('تقنية المعلومات', 7000.00),
    ('المبيعات', 4500.00);