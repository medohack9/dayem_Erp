<?php

echo "=== Dayem ERP Database Setup ===\n\n";

$dbConfig = require __DIR__ . '/../config/database.php';

$mysqli = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], '', $dbConfig['port']);

if ($mysqli->connect_error) {
    echo "ERROR: Database connection failed: " . $mysqli->connect_error . "\n";
    echo "\nPlease verify:\n";
    echo "  1. MySQL is running (XAMPP Control Panel -> Start MySQL)\n";
    echo "  2. Credentials in config/database.php are correct\n";
    echo "  3. MySQL port is correct (default: 3306)\n";
    exit(1);
}

$dbName = $dbConfig['database'];
$charset = $dbConfig['charset'];
$collation = $dbConfig['collation'];

$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}") or die("Failed to create database: " . $mysqli->error);
echo "Database `{$dbName}` created or already exists.\n";

$mysqli->select_db($dbName);

$migrations = [
    '001_core_foundation.sql',
    '002_authentication.sql',
    '003_departments.sql',
    '004_employees.sql',
    '005_employee_account_documents.sql',
    '006_tasks.sql',
    '007_task_notes_media.sql',
];

foreach ($migrations as $migration) {
    $filePath = __DIR__ . "/migrations/{$migration}";
    if (!file_exists($filePath)) {
        echo "  WARNING: Missing migration {$migration}\n";
        continue;
    }

    $sql = file_get_contents($filePath);
    $sql = preg_replace('/^USE\s+\w+\s*;/im', '', $sql);
    $sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);

    if ($mysqli->multi_query($sql)) {
        do {
            $mysqli->use_result();
        } while ($mysqli->more_results() && $mysqli->next_result());
    }

    if ($mysqli->error) {
        echo "  Notice in {$migration}: {$mysqli->error}\n";
    } else {
        echo "  Applied: {$migration}\n";
    }
}

$mysqli->close();
echo "\nAll migrations completed!\n";
echo "Setup complete!\n";