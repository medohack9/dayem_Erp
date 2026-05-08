-- Migration 005: Employee account setup and document uploads
-- Adds must_change_password flag and employee_documents table

USE dayem_erp;

-- Add must_change_password column if not exists
SET @dbname = DATABASE();
SET @tablename = 'users';

SET @colname = 'must_change_password';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `must_change_password` TINYINT(1) NOT NULL DEFAULT 1 AFTER `status`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Set must_change_password = 0 for existing admin accounts
UPDATE `users` SET `must_change_password` = 0 WHERE `role` = 'admin';

-- Create employee_documents table if not exists
CREATE TABLE IF NOT EXISTS `employee_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `doc_type` ENUM('profile_photo', 'contract', 'national_id', 'other') NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT NOT NULL DEFAULT 0,
    `mime_type` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_documents_user_id` (`user_id`),
    KEY `idx_employee_documents_doc_type` (`doc_type`),
    CONSTRAINT `fk_employee_documents_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;