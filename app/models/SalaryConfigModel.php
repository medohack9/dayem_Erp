<?php

namespace App\Models;

use App\Core\Model;

class SalaryConfigModel extends Model
{
    public function findByUserId(int $userId): ?array
    {
        return $this->findOne('salary_configs', 'user_id = :user_id', ['user_id' => $userId]);
    }

    public function create(array $data): string
    {
        return $this->insert('salary_configs', $data);
    }

    public function updateConfig(int $userId, array $data): void
    {
        $this->update('salary_configs', $data, 'user_id = :user_id', ['user_id' => $userId]);
    }

    public function upsert(int $userId, float $basicSalary, float $allowances, float $deductions, ?string $notes = null): void
    {
        $existing = $this->findByUserId($userId);
        $data = [
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'notes' => $notes,
        ];

        if ($existing) {
            $this->updateConfig($userId, $data);
        } else {
            $data['user_id'] = $userId;
            $this->create($data);
        }
    }

    public function getEffectiveSalary(int $userId, ?int $departmentId = null): array
    {
        $config = $this->findByUserId($userId);

        if ($config) {
            return [
                'basic_salary' => (float)$config['basic_salary'],
                'allowances' => (float)$config['allowances'],
                'deductions' => (float)$config['deductions'],
                'source' => 'config',
            ];
        }

        if ($departmentId) {
            $deptModel = new DepartmentModel();
            $defaultSalary = $deptModel->getDefaultSalary($departmentId);

            if ($defaultSalary !== null) {
                return [
                    'basic_salary' => $defaultSalary,
                    'allowances' => 0,
                    'deductions' => 0,
                    'source' => 'department',
                ];
            }
        }

        $userModel = new EmployeeModel();
        $user = $userModel->findById($userId);
        if ($user && $user['salary'] !== null) {
            return [
                'basic_salary' => (float)$user['salary'],
                'allowances' => 0,
                'deductions' => 0,
                'source' => 'user_fallback',
            ];
        }

        return [
            'basic_salary' => 0,
            'allowances' => 0,
            'deductions' => 0,
            'source' => 'none',
        ];
    }

    public function getAllWithUsers(): array
    {
        return $this->query(
            "SELECT sc.*, u.name as user_name, u.employee_code, d.name as department_name
             FROM salary_configs sc
             JOIN users u ON sc.user_id = u.id
             LEFT JOIN departments d ON u.department_id = d.id
             WHERE u.deleted_at IS NULL
             ORDER BY u.name ASC"
        )->fetchAll();
    }
}