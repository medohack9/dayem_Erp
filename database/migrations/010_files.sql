-- Migration: 010_files.sql
-- Feature: File System (Phase 8)
-- Date: 2026-05-01
-- Description: Create files table and add storage_quota to users table

-- Create files table
CREATE TABLE IF NOT EXISTS `files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `name` VARCHAR(200) NOT NULL COMMENT 'Display name (user-defined)',
    `original_name` VARCHAR(255) NOT NULL COMMENT 'Original filename from upload',
    `stored_name` VARCHAR(255) NOT NULL COMMENT 'Unique system-generated filename',
    `file_path` VARCHAR(500) NOT NULL COMMENT 'Relative path from storage root',
    `file_size` INT NOT NULL COMMENT 'File size in bytes',
    `file_type` VARCHAR(100) NOT NULL COMMENT 'MIME type',
    `priority` VARCHAR(20) NOT NULL DEFAULT 'متوسطة' COMMENT 'منخفضة / متوسطة / عالية',
    `notes` TEXT NULL COMMENT 'Optional notes (max 1000 chars)',
    `deleted_at` TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT `uk_files_stored_name` UNIQUE (`stored_name`),
    CONSTRAINT `fk_files_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes
CREATE INDEX `idx_files_user_id` ON `files`(`user_id`);
CREATE INDEX `idx_files_priority` ON `files`(`priority`);
CREATE INDEX `idx_files_deleted_at` ON `files`(`deleted_at`);
CREATE INDEX `idx_files_created_at` ON `files`(`created_at`);

-- Add storage_quota column to users table
ALTER TABLE `users` 
ADD COLUMN `storage_quota` BIGINT NULL DEFAULT 524288000 COMMENT 'Storage quota in bytes (500MB default, NULL = unlimited for admins)';

-- Update existing admin users to have unlimited storage (NULL)
UPDATE `users` SET `storage_quota` = NULL WHERE `role` = 'admin';

-- Update existing employees to have default 500MB quota
UPDATE `users` SET `storage_quota` = 524288000 WHERE `role` = 'employee' AND `storage_quota` IS NULL;