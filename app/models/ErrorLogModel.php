<?php

namespace App\Models;

use App\Core\Model;

class ErrorLogModel extends Model
{
    public function logError(array $data): string
    {
        return $this->insert('error_logs', [
            'error_level' => $data['error_level'],
            'error_message' => $data['error_message'],
            'error_file' => $data['error_file'] ?? null,
            'error_line' => $data['error_line'] ?? null,
            'error_trace' => $data['error_trace'] ?? null,
            'request_url' => $data['request_url'] ?? null,
            'request_method' => $data['request_method'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
        ]);
    }

    public function getRecent(int $limit = 50): array
    {
        return $this->query(
            "SELECT * FROM error_logs ORDER BY created_at DESC LIMIT :limit",
            ['limit' => $limit]
        )->fetchAll();
    }

    public function getByLevel(string $level): array
    {
        return $this->findAll('error_logs', 'error_level = :level', ['level' => $level]);
    }
}