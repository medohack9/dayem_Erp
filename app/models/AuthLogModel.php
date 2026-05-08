<?php

namespace App\Models;

use App\Core\Model;

class AuthLogModel extends Model
{
    public function logAttempt(array $data): string
    {
        return $this->insert('auth_logs', [
            'user_id' => $data['user_id'] ?? null,
            'identifier' => $data['identifier'],
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
            'outcome' => $data['outcome'],
            'failure_reason' => $data['failure_reason'] ?? null,
        ]);
    }

    public function getRecentByUser(int $userId, int $limit = 50): array
    {
        return $this->query(
            'SELECT * FROM auth_logs WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit',
            ['user_id' => $userId, 'limit' => $limit]
        )->fetchAll();
    }

    public function getRecentByIp(string $ip, int $limit = 50): array
    {
        return $this->query(
            'SELECT * FROM auth_logs WHERE ip_address = :ip ORDER BY created_at DESC LIMIT :limit',
            ['ip' => $ip, 'limit' => $limit]
        )->fetchAll();
    }

    public function countRecentFailedByIdentifier(string $identifier, string $ip, int $windowMinutes = 15): int
    {
        $result = $this->query(
            'SELECT COUNT(*) as count FROM auth_logs WHERE identifier = :identifier AND ip_address = :ip AND outcome = :outcome AND created_at > DATE_SUB(NOW(), INTERVAL :window MINUTE)',
            ['identifier' => $identifier, 'ip' => $ip, 'outcome' => 'failed', 'window' => $windowMinutes]
        )->fetch();
        return (int)($result['count'] ?? 0);
    }

    public function clearFailedByIdentifier(string $identifier, string $ip): void
    {
        $this->query(
            'DELETE FROM auth_logs WHERE identifier = :identifier AND ip_address = :ip AND outcome = :outcome',
            ['identifier' => $identifier, 'ip' => $ip, 'outcome' => 'failed']
        );
    }

    public function getFailedAttemptsCount(int $userId): int
    {
        $result = $this->query(
            'SELECT COUNT(*) as count FROM auth_logs WHERE user_id = :user_id AND outcome = :outcome AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)',
            ['user_id' => $userId, 'outcome' => 'failed']
        )->fetch();
        return (int)($result['count'] ?? 0);
    }
}