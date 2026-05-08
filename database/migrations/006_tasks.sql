-- Migration 006: Tasks table
-- Creates tasks table for task management module

USE dayem_erp;

CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `task_code` VARCHAR(20) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `priority` VARCHAR(20) NOT NULL DEFAULT 'متوسط',
    `status` VARCHAR(20) NOT NULL DEFAULT 'جديد',
    `assigned_to` INT NOT NULL,
    `department_id` INT NULL,
    `due_date` DATE NULL,
    `completed_at` TIMESTAMP NULL,
    `created_by` INT NULL,
    `deleted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tasks_task_code` (`task_code`),
    KEY `idx_tasks_assigned_to` (`assigned_to`),
    KEY `idx_tasks_department_id` (`department_id`),
    KEY `idx_tasks_status` (`status`),
    KEY `idx_tasks_priority` (`priority`),
    KEY `idx_tasks_deleted_at` (`deleted_at`),
    KEY `idx_tasks_created_by` (`created_by`),
    CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_tasks_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tasks_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;