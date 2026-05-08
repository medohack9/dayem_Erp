-- Migration 004: Employee profile fields
-- Adds employee-specific columns to the users table

USE dayem_erp;

-- Add national_id column if not exists
SET @dbname = DATABASE();
SET @tablename = 'users';

SET @colname = 'national_id';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `national_id` VARCHAR(14) NULL AFTER `phone`, ADD UNIQUE INDEX `uk_users_national_id` (`national_id`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @colname = 'birth_date';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `birth_date` DATE NULL AFTER `national_id`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @colname = 'salary';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `salary` DECIMAL(10,2) NULL AFTER `birth_date`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @colname = 'hire_date';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `hire_date` DATE NULL AFTER `salary`'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @colname = 'employee_code';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @colname) > 0,
    'SELECT 1',
    'ALTER TABLE `users` ADD COLUMN `employee_code` VARCHAR(20) NULL AFTER `hire_date`, ADD UNIQUE INDEX `uk_users_employee_code` (`employee_code`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_users_department_id';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = @indexname) > 0,
    'SELECT 1',
    'ALTER TABLE `users` ADD INDEX `idx_users_department_id` (`department_id`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_users_status';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = @indexname) > 0,
    'SELECT 1',
    'ALTER TABLE `users` ADD INDEX `idx_users_status` (`status`)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;