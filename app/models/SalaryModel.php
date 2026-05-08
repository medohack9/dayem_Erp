<?php

namespace App\Models;

use App\Core\Model;

class SalaryModel extends Model
{
    public function findById(int $id): ?array
    {
        $result = $this->query(
            "SELECT s.*, u.name as employee_name, u.employee_code, d.name as department_name,
                    p.name as paid_by_name
             FROM salaries s
             JOIN users u ON s.user_id = u.id
             LEFT JOIN departments d ON u.department_id = d.id
             LEFT JOIN users p ON s.paid_by = p.id
             WHERE s.id = :id AND s.deleted_at IS NULL",
            ['id' => $id]
        )->fetch();
        return $result ?: null;
    }

    public function generateMonthlySalaries(int $month, int $year): array
    {
        $employeeModel = new EmployeeModel();
        $activeEmployees = $employeeModel->findAllActive('', 1, 10000, null, 'active');

        $configModel = new SalaryConfigModel();
        $generated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($activeEmployees['employees'] as $emp) {
            $existing = $this->query(
                "SELECT id FROM salaries WHERE user_id = :uid AND month = :m AND year = :y AND deleted_at IS NULL",
                ['uid' => $emp['id'], 'm' => $month, 'y' => $year]
            )->fetch();

            if ($existing) {
                $skipped++;
                continue;
            }

            $salaryData = $configModel->getEffectiveSalary($emp['id'], $emp['department_id'] ?? null);

            if ($salaryData['basic_salary'] <= 0 && $salaryData['source'] === 'none') {
                $errors[] = $emp['name'];
                continue;
            }

            $net = $salaryData['basic_salary'] + $salaryData['allowances'] - $salaryData['deductions'];

            $this->insert('salaries', [
                'user_id' => $emp['id'],
                'month' => $month,
                'year' => $year,
                'basic_salary' => $salaryData['basic_salary'],
                'allowances' => $salaryData['allowances'],
                'deductions' => $salaryData['deductions'],
                'net_amount' => $net,
                'status' => 'unpaid',
            ]);

            $generated++;
        }

        return ['generated' => $generated, 'skipped' => $skipped, 'errors' => $errors];
    }

    public function recalculateUnpaid(int $month, int $year): int
    {
        $configModel = new SalaryConfigModel();

        $unpaidRecords = $this->query(
            "SELECT s.*, u.department_id FROM salaries s
             JOIN users u ON s.user_id = u.id
             WHERE s.month = :m AND s.year = :y AND s.status = 'unpaid' AND s.deleted_at IS NULL",
            ['m' => $month, 'y' => $year]
        )->fetchAll();

        $updated = 0;

        foreach ($unpaidRecords as $record) {
            $salaryData = $configModel->getEffectiveSalary($record['user_id'], $record['department_id']);

            $net = $salaryData['basic_salary'] + $salaryData['allowances'] - $salaryData['deductions'];

            $this->update('salaries', [
                'basic_salary' => $salaryData['basic_salary'],
                'allowances' => $salaryData['allowances'],
                'deductions' => $salaryData['deductions'],
                'net_amount' => $net,
            ], 'id = :id AND status = :status AND deleted_at IS NULL', ['id' => $record['id'], 'status' => 'unpaid']);

            $updated++;
        }

        return $updated;
    }

    public function markAsPaid(int $id, int $paidBy): bool
    {
        $record = $this->findById($id);
        if (!$record || $record['status'] === 'paid') {
            return false;
        }

        $this->update('salaries', [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
            'paid_by' => $paidBy,
        ], 'id = :id AND status = :status AND deleted_at IS NULL', ['id' => $id, 'status' => 'unpaid']);

        return true;
    }

    public function findAllPaginated(int $page = 1, int $perPage = 15, ?int $month = null, ?int $year = null, ?int $departmentId = null, ?string $status = null, ?string $search = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $conditions = ['s.deleted_at IS NULL'];

        if ($month !== null) {
            $conditions[] = 's.month = :month';
            $params['month'] = $month;
        }

        if ($year !== null) {
            $conditions[] = 's.year = :year';
            $params['year'] = $year;
        }

        if ($departmentId !== null) {
            $conditions[] = 'u.department_id = :dept_id';
            $params['dept_id'] = $departmentId;
        }

        if ($status !== null && $status !== 'all') {
            $conditions[] = 's.status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            $conditions[] = '(u.name LIKE :search OR u.employee_code LIKE :search2)';
            $params['search'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        $where = implode(' AND ', $conditions);

        $countResult = $this->query(
            "SELECT COUNT(*) as total FROM salaries s
             JOIN users u ON s.user_id = u.id
             LEFT JOIN departments d ON u.department_id = d.id
             WHERE {$where}",
            $params
        )->fetch();
        $total = (int)($countResult['total'] ?? 0);

        $results = $this->query(
            "SELECT s.*, u.name as employee_name, u.employee_code,
                    d.name as department_name, p.name as paid_by_name
             FROM salaries s
             JOIN users u ON s.user_id = u.id
             LEFT JOIN departments d ON u.department_id = d.id
             LEFT JOIN users p ON s.paid_by = p.id
             WHERE {$where}
             ORDER BY s.year DESC, s.month DESC, u.name ASC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        )->fetchAll();

        return [
            'salaries' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ];
    }

    public function findUserHistory(int $userId, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;

        $countResult = $this->query(
            "SELECT COUNT(*) as total FROM salaries WHERE user_id = :uid AND deleted_at IS NULL",
            ['uid' => $userId]
        )->fetch();
        $total = (int)($countResult['total'] ?? 0);

        $results = $this->query(
            "SELECT s.*, p.name as paid_by_name
             FROM salaries s
             LEFT JOIN users p ON s.paid_by = p.id
             WHERE s.user_id = :uid AND s.deleted_at IS NULL
             ORDER BY s.year DESC, s.month DESC
             LIMIT :limit OFFSET :offset",
            ['uid' => $userId, 'limit' => $perPage, 'offset' => $offset]
        )->fetchAll();

        return [
            'salaries' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ];
    }

    public function getSummaryStats(?int $month = null, ?int $year = null, ?int $departmentId = null): array
    {
        $params = [];
        $conditions = ['s.deleted_at IS NULL'];

        if ($month !== null) {
            $conditions[] = 's.month = :month';
            $params['month'] = $month;
        }

        if ($year !== null) {
            $conditions[] = 's.year = :year';
            $params['year'] = $year;
        }

        if ($departmentId !== null) {
            $conditions[] = 'u.department_id = :dept_id';
            $params['dept_id'] = $departmentId;
        }

        $where = implode(' AND ', $conditions);

        $result = $this->query(
            "SELECT
                COUNT(*) as total_count,
                COALESCE(SUM(s.net_amount), 0) as total_payroll,
                SUM(CASE WHEN s.status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                COALESCE(SUM(CASE WHEN s.status = 'paid' THEN s.net_amount ELSE 0 END), 0) as paid_amount,
                SUM(CASE WHEN s.status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count,
                COALESCE(SUM(CASE WHEN s.status = 'unpaid' THEN s.net_amount ELSE 0 END), 0) as unpaid_amount
             FROM salaries s
             JOIN users u ON s.user_id = u.id
             WHERE {$where}",
            $params
        )->fetch();

        return [
            'total_count' => (int)($result['total_count'] ?? 0),
            'total_payroll' => (float)($result['total_payroll'] ?? 0),
            'paid_count' => (int)($result['paid_count'] ?? 0),
            'paid_amount' => (float)($result['paid_amount'] ?? 0),
            'unpaid_count' => (int)($result['unpaid_count'] ?? 0),
            'unpaid_amount' => (float)($result['unpaid_amount'] ?? 0),
        ];
    }

    public function hasRecordsForMonth(int $month, int $year): bool
    {
        $result = $this->query(
            "SELECT COUNT(*) as cnt FROM salaries WHERE month = :m AND year = :y AND deleted_at IS NULL",
            ['m' => $month, 'y' => $year]
        )->fetch();
        return (int)$result['cnt'] > 0;
    }

    public function softDelete(int $id): void
    {
        $this->update('salaries', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
    }
}