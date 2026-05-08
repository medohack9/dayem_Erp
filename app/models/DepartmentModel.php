<?php

namespace App\Models;

use App\Core\Model;

class DepartmentModel extends Model
{
    public function findAllActive(string $search = '', int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        if ($search !== '') {
            $countResult = $this->query(
                'SELECT COUNT(*) as total FROM departments WHERE deleted_at IS NULL AND name LIKE :search',
                ['search' => '%' . $search . '%']
            )->fetch();
            $total = (int)($countResult['total'] ?? 0);

            $results = $this->query(
                'SELECT d.*, u.name as manager_name,
                        (SELECT COUNT(*) FROM users WHERE department_id = d.id AND deleted_at IS NULL) as employee_count
                 FROM departments d
                 LEFT JOIN users u ON d.manager_id = u.id
                 WHERE d.deleted_at IS NULL AND d.name LIKE :search
                 ORDER BY d.created_at DESC
                 LIMIT :limit OFFSET :offset',
                ['search' => '%' . $search . '%', 'limit' => $perPage, 'offset' => $offset]
            )->fetchAll();
        } else {
            $countResult = $this->query(
                'SELECT COUNT(*) as total FROM departments WHERE deleted_at IS NULL'
            )->fetch();
            $total = (int)($countResult['total'] ?? 0);

            $results = $this->query(
                'SELECT d.*, u.name as manager_name,
                        (SELECT COUNT(*) FROM users WHERE department_id = d.id AND deleted_at IS NULL) as employee_count
                 FROM departments d
                 LEFT JOIN users u ON d.manager_id = u.id
                 WHERE d.deleted_at IS NULL
                 ORDER BY d.created_at DESC
                 LIMIT :limit OFFSET :offset',
                ['limit' => $perPage, 'offset' => $offset]
            )->fetchAll();
        }

        return [
            'departments' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->findOne('departments', 'id = :id AND deleted_at IS NULL', ['id' => $id]);
    }

    public function findByName(string $name, ?int $excludeId = null): ?array
    {
        if ($excludeId) {
            return $this->findOne('departments', 'name = :name AND id != :exclude_id AND deleted_at IS NULL', ['name' => $name, 'exclude_id' => $excludeId]);
        }
        return $this->findOne('departments', 'name = :name AND deleted_at IS NULL', ['name' => $name]);
    }

    public function create(array $data): string
    {
        return $this->insert('departments', $data);
    }

    public function updateDepartment(int $id, array $data): void
    {
        $this->update('departments', $data, 'id = :id AND deleted_at IS NULL', ['id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $this->update('departments', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
    }

    public function countActive(): int
    {
        $result = $this->query('SELECT COUNT(*) as count FROM departments WHERE deleted_at IS NULL')->fetch();
        return (int)($result['count'] ?? 0);
    }

    public function getEmployeeCount(int $departmentId): int
    {
        $result = $this->query(
            'SELECT COUNT(*) as count FROM users WHERE department_id = :id AND deleted_at IS NULL',
            ['id' => $departmentId]
        )->fetch();
        return (int)($result['count'] ?? 0);
    }

    public function reassignEmployees(int $fromDepartmentId, int $toDepartmentId): void
    {
        $this->query(
            'UPDATE users SET department_id = :to_id WHERE department_id = :from_id AND deleted_at IS NULL',
            ['to_id' => $toDepartmentId, 'from_id' => $fromDepartmentId]
        );
    }

    public function getAllActive(): array
    {
        return $this->query(
            'SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name ASC'
        )->fetchAll();
    }

    public function clearManager(int $userId): void
    {
        $this->query(
            'UPDATE departments SET manager_id = NULL WHERE manager_id = :user_id',
            ['user_id' => $userId]
        );
    }

    public function getDefaultSalary(int $departmentId): ?float
    {
        $result = $this->findOne('departments', 'id = :id AND deleted_at IS NULL', ['id' => $departmentId]);
        return $result ? (float)$result['default_salary'] : null;
    }
}