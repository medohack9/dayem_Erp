<?php

namespace App\Models;

use App\Core\Model;

class UserSessionModel extends Model
{
    public function createSession(int $userId, string $sessionId, string $ip, string $userAgent): string
    {
        $this->deleteByUserId($userId);
        return $this->insert('user_sessions', [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    public function deleteBySessionId(string $sessionId): void
    {
        $this->query('DELETE FROM user_sessions WHERE session_id = :sid', ['sid' => $sessionId]);
    }

    public function deleteByUserId(int $userId): void
    {
        $this->query('DELETE FROM user_sessions WHERE user_id = :uid', ['uid' => $userId]);
    }

    public function findBySessionId(string $sessionId): ?array
    {
        return $this->findOne('user_sessions', 'session_id = :sid', ['sid' => $sessionId]);
    }

    public function updateActivity(string $sessionId): void
    {
        $this->query('UPDATE user_sessions SET last_activity = NOW() WHERE session_id = :sid', ['sid' => $sessionId]);
    }
}