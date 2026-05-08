-- Migration 008: Tickets, ticket messages, ticket attachments, and ticket reads
-- Creates tables for the ticket system module

USE dayem_erp;

CREATE TABLE IF NOT EXISTS `tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_code` VARCHAR(20) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `category` VARCHAR(30) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'مفتوح',
    `created_by` INT NOT NULL,
    `assigned_employee` INT NULL,
    `deleted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tickets_ticket_code` (`ticket_code`),
    KEY `idx_tickets_created_by` (`created_by`),
    KEY `idx_tickets_status` (`status`),
    KEY `idx_tickets_category` (`category`),
    KEY `idx_tickets_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_tickets_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tickets_assigned_employee` FOREIGN KEY (`assigned_employee`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ticket_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `sender_id` INT NOT NULL,
    `sender_type` VARCHAR(10) NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_ticket_messages_ticket_id` (`ticket_id`),
    KEY `idx_ticket_messages_sender_id` (`sender_id`),
    KEY `idx_ticket_messages_created_at` (`created_at`),
    CONSTRAINT `fk_ticket_messages_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ticket_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ticket_attachments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `message_id` INT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` INT NOT NULL DEFAULT 0,
    `mime_type` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_ticket_attachments_ticket_id` (`ticket_id`),
    KEY `idx_ticket_attachments_message_id` (`message_id`),
    CONSTRAINT `fk_ticket_attachments_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ticket_attachments_message` FOREIGN KEY (`message_id`) REFERENCES `ticket_messages`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ticket_reads` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `last_read_at` TIMESTAMP NOT NULL,
    UNIQUE KEY `uk_ticket_reads_ticket_user` (`ticket_id`, `user_id`),
    KEY `idx_ticket_reads_user_id` (`user_id`),
    CONSTRAINT `fk_ticket_reads_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ticket_reads_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;