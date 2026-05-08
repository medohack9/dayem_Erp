<?php

namespace App\Models;

use App\Core\Model;

class EmployeeModel extends Model
{
    public function findAllActive(string $search = '', int $page = 1, int $perPage = 15, ?int $departmentId = null, string $status = 'active'): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $conditions = ['u.deleted_at IS NULL', 'u.role = :role'];
        $params['role'] = 'employee';

        if ($search !== '') {
            $conditions[] = '(u.name LIKE :search OR u.employee_code LIKE :search2)';
            $params['search'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        if ($departmentId !== null) {
            $conditions[] = 'u.department_id = :dept_id';
            $params['dept_id'] = $departmentId;
        }

        if ($status !== 'all') {
            $conditions[] = 'u.status = :status';
            $params['status'] = $status;
        }

        $where = implode(' AND ', $conditions);

        $countResult = $this->query(
            "SELECT COUNT(*) as total FROM users u WHERE {$where}",
            $params
        )->fetch();
        $total = (int)($countResult['total'] ?? 0);

        $results = $this->query(
            "SELECT u.*, d.name as department_name, ed.id as photo_id, ed.stored_name as photo_stored_name
             FROM users u
             LEFT JOIN departments d ON u.department_id = d.id
             LEFT JOIN employee_documents ed ON u.id = ed.user_id AND ed.doc_type = 'profile_photo'
             WHERE {$where}
             GROUP BY u.id
             ORDER BY u.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        )->fetchAll();

        return [
            'employees' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->findOne('users', 'id = :id AND deleted_at IS NULL AND role = :role', ['id' => $id, 'role' => 'employee']);
    }

    public function findByEmail(string $email, ?int $excludeId = null): ?array
    {
        if ($excludeId) {
            return $this->findOne('users', 'email = :email AND id != :exclude_id AND deleted_at IS NULL', ['email' => $email, 'exclude_id' => $excludeId]);
        }
        return $this->findOne('users', 'email = :email AND deleted_at IS NULL', ['email' => $email]);
    }

    public function findByPhone(string $phone, ?int $excludeId = null): ?array
    {
        if ($excludeId) {
            return $this->findOne('users', 'phone = :phone AND id != :exclude_id AND deleted_at IS NULL', ['phone' => $phone, 'exclude_id' => $excludeId]);
        }
        return $this->findOne('users', 'phone = :phone AND deleted_at IS NULL', ['phone' => $phone]);
    }

    public function findByNationalId(string $nationalId, ?int $excludeId = null): ?array
    {
        if ($excludeId) {
            return $this->findOne('users', 'national_id = :nid AND id != :exclude_id AND deleted_at IS NULL', ['nid' => $nationalId, 'exclude_id' => $excludeId]);
        }
        return $this->findOne('users', 'national_id = :nid AND deleted_at IS NULL', ['nid' => $nationalId]);
    }

    public function generateEmployeeCode(): string
    {
        $result = $this->query(
            "SELECT MAX(CAST(SUBSTRING(employee_code, 5) AS UNSIGNED)) as max_code FROM users WHERE employee_code IS NOT NULL"
        )->fetch();

        $maxCode = (int)($result['max_code'] ?? 0);
        $nextCode = $maxCode + 1;
        return 'EMP-' . str_pad((string)$nextCode, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data): string
    {
        return $this->insert('users', $data);
    }

    public function updateEmployee(int $id, array $data): void
    {
        $this->update('users', $data, 'id = :id AND deleted_at IS NULL', ['id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $this->update('users', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
    }

    public function generateTempPassword(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $password = '';
        for ($i = 0; $i < 16; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}