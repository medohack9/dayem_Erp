-- Migration 007: Task notes and media
-- Adds task_notes and task_media tables for employee interaction with tasks

USE dayem_erp;

CREATE TABLE IF NOT EXISTS `task_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `task_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_task_notes_task_id` (`task_id`),
    KEY `idx_task_notes_user_id` (`user_id`),
    CONSTRAINT `fk_task_notes_task` FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_task_notes_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `task_media` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `task_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `media_type` ENUM('photo','document','voice') NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT NOT NULL DEFAULT 0,
    `mime_type` VARCHAR(100) NOT NULL,
    `duration` INT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_task_media_task_id` (`task_id`),
    KEY `idx_task_media_user_id` (`user_id`),
    KEY `idx_task_media_type` (`media_type`),
    CONSTRAINT `fk_task_media_task` FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_task_media_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;