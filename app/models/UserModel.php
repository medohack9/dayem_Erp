<?php

namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    public function findById(int $id): ?array
    {
        return $this->findOne('users', 'id = :id AND deleted_at IS NULL', ['id' => $id]);
    }

    public function findByIdentifier(string $identifier): ?array
    {
        if (str_contains($identifier, '@')) {
            return $this->findOne('users', 'email = :email AND deleted_at IS NULL', ['email' => $identifier]);
        }
        return $this->findOne('users', 'phone = :phone AND deleted_at IS NULL', ['phone' => $identifier]);
    }

    public function findAllActive(): array
    {
        return $this->findAll('users', 'deleted_at IS NULL');
    }

    public function isLocked(array $user): bool
    {
        if ($user['locked_until'] === null) {
            return false;
        }
        return strtotime($user['locked_until']) > time();
    }

    public function incrementFailedAttempts(int $userId): void
    {
        $config = require ROOT_PATH . '/config/app.php';
        $duration = (int)($config['lockout_duration'] ?? 15);
        $lockedUntil = date('Y-m-d H:i:s', time() + $duration * 60);

        $this->query(
            'UPDATE users SET failed_login_attempts = failed_login_attempts + 1, locked_until = IF(failed_login_attempts >= 4, :locked_until, locked_until) WHERE id = :id',
            ['id' => $userId, 'locked_until' => $lockedUntil]
        );
    }

    public function resetFailedAttempts(int $userId): void
    {
        $this->query('UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = :id', ['id' => $userId]);
    }

    public function unlockAccount(int $userId): void
    {
        $this->resetFailedAttempts($userId);
    }

    public function createUser(array $data): string
    {
        return $this->insert('users', $data);
    }

    public function updateUser(int $userId, array $data): void
    {
        $this->update('users', $data, 'id = :id', ['id' => $userId]);
    }

    public function softDelete(int $userId): void
    {
        $this->update('users', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $userId]);
    }

    public function findAvailableManagers(?int $excludeDepartmentId = null): array
    {
        $sql = "SELECT u.id, u.name FROM users u
                WHERE u.status = 'active' AND u.deleted_at IS NULL
                AND (u.id NOT IN (SELECT manager_id FROM departments WHERE manager_id IS NOT NULL AND deleted_at IS NULL";

        $params = [];
        if ($excludeDepartmentId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeDepartmentId;
        }

        $sql .= ") OR u.id = (SELECT manager_id FROM departments WHERE id = :dept_id AND deleted_at IS NULL))";

        if ($excludeDepartmentId) {
            $params['dept_id'] = $excludeDepartmentId;
        } else {
            $params['dept_id'] = 0;
        }

        $sql .= " ORDER BY u.name ASC";

        return $this->query($sql, $params)->fetchAll();
    }
}