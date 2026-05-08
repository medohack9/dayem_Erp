-- Migration 009: Salaries and salary_configs tables
-- Creates tables for the salary module

USE dayem_erp;

CREATE TABLE IF NOT EXISTS `salary_configs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `basic_salary` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `allowances` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `deductions` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_salary_configs_user_id` (`user_id`),
    KEY `idx_salary_configs_user_id` (`user_id`),
    CONSTRAINT `fk_salary_configs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `salaries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `month` TINYINT NOT NULL,
    `year` INT NOT NULL,
    `basic_salary` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `allowances` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `deductions` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `net_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `status` ENUM('unpaid','paid') NOT NULL DEFAULT 'unpaid',
    `paid_at` TIMESTAMP NULL,
    `paid_by` INT NULL,
    `deleted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_salaries_user_month_year` (`user_id`, `month`, `year`),
    KEY `idx_salaries_user_id` (`user_id`),
    KEY `idx_salaries_month_year` (`month`, `year`),
    KEY `idx_salaries_status` (`status`),
    KEY `idx_salaries_paid_by` (`paid_by`),
    KEY `idx_salaries_deleted_at` (`deleted_at`),
    CONSTRAINT `fk_salaries_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_salaries_paid_by` FOREIGN KEY (`paid_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;