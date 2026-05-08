<?php

namespace App\Core;

class Database
{
    private static ?\PDO $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): \PDO
    {
        if (self::$instance === null) {
            $dbConfig = require ROOT_PATH . '/config/database.php';

            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";

            try {
                self::$instance = new \PDO(
                    $dsn,
                    $dbConfig['username'],
                    $dbConfig['password'],
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (\PDOException $e) {
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}