<?php

namespace App\Core;

class Model
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function findAll(string $table, string $conditions = '', array $params = []): array
    {
        $sql = "SELECT * FROM {$table}";
        if ($conditions) {
            $sql .= " WHERE {$conditions}";
        }
        return $this->query($sql, $params)->fetchAll();
    }

    public function findOne(string $table, string $conditions, array $params = []): ?array
    {
        $sql = "SELECT * FROM {$table} WHERE {$conditions}";
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    public function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_map(fn($col) => "`{$col}`", array_keys($data)));
        $placeholders = implode(', ', array_map(fn($col) => ":{$col}", array_keys($data)));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        return $this->db->lastInsertId();
    }

    public function update(string $table, array $data, string $conditions, array $params = []): void
    {
        $setClauses = implode(', ', array_map(fn($col) => "`{$col}` = :_update_{$col}", array_keys($data)));
        $sql = "UPDATE {$table} SET {$setClauses} WHERE {$conditions}";
        $updateParams = [];
        foreach ($data as $col => $val) {
            $updateParams["_update_{$col}"] = $val;
        }
        $this->query($sql, array_merge($updateParams, $params));
    }

    public function delete(string $table, string $conditions, array $params = []): void
    {
        $sql = "DELETE FROM {$table} WHERE {$conditions}";
        $this->query($sql, $params);
    }
}