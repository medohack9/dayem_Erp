<?php

namespace App\Models;

use App\Core\Model;

class TaskModel extends Model
{
    public function findAllActive(string $search = '', int $page = 1, int $perPage = 15, ?string $status = null, ?string $priority = null, ?int $departmentId = null, ?int $assignedTo = null, bool $adminView = true): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $conditions = ['t.deleted_at IS NULL'];

        if (!$adminView && $assignedTo !== null) {
            $conditions[] = 't.assigned_to = :assigned_to';
            $params['assigned_to'] = $assignedTo;
        } elseif ($assignedTo !== null) {
            $conditions[] = 't.assigned_to = :assigned_to';
            $params['assigned_to'] = $assignedTo;
        }

        if ($search !== '') {
            $conditions[] = '(t.title LIKE :search OR t.task_code LIKE :search2)';
            $params['search'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        if ($status !== null && $status !== 'all') {
            $conditions[] = 't.status = :status';
            $params['status'] = $status;
        }

        if ($priority !== null) {
            $conditions[] = 't.priority = :priority';
            $params['priority'] = $priority;
        }

        if ($departmentId !== null) {
            $conditions[] = 't.department_id = :dept_id';
            $params['dept_id'] = $departmentId;
        }

        $where = implode(' AND ', $conditions);

        $countResult = $this->query(
            "SELECT COUNT(*) as total FROM tasks t WHERE {$where}",
            $params
        )->fetch();
        $total = (int)($countResult['total'] ?? 0);

        $results = $this->query(
            "SELECT t.*, u.name as employee_name, u.employee_code, u.status as employee_status,
                    d.name as department_name, a.name as creator_name,
                    CASE WHEN t.due_date IS NOT NULL AND t.due_date < CURDATE() AND t.status != 'مكتمل' THEN 1 ELSE 0 END as is_overdue
             FROM tasks t
             LEFT JOIN users u ON t.assigned_to = u.id
             LEFT JOIN departments d ON t.department_id = d.id
             LEFT JOIN users a ON t.created_by = a.id
             WHERE {$where}
             ORDER BY t.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        )->fetchAll();

        return [
            'tasks' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ];
    }

    public function findById(int $id): ?array
    {
        $result = $this->query(
            "SELECT t.*, u.name as employee_name, u.employee_code, u.status as employee_status,
                    d.name as department_name, a.name as creator_name
             FROM tasks t
             LEFT JOIN users u ON t.assigned_to = u.id
             LEFT JOIN departments d ON t.department_id = d.id
             LEFT JOIN users a ON t.created_by = a.id
             WHERE t.id = :id AND t.deleted_at IS NULL",
            ['id' => $id]
        )->fetch();
        return $result ?: null;
    }

    public function generateTaskCode(): string
    {
        $result = $this->query(
            "SELECT MAX(CAST(SUBSTRING(task_code, 5) AS UNSIGNED)) as max_code FROM tasks WHERE task_code IS NOT NULL"
        )->fetch();

        $maxCode = (int)($result['max_code'] ?? 0);
        $nextCode = $maxCode + 1;
        return 'TSK-' . str_pad((string)$nextCode, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data): string
    {
        return $this->insert('tasks', $data);
    }

    public function updateTask(int $id, array $data): void
    {
        $this->update('tasks', $data, 'id = :id AND deleted_at IS NULL', ['id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $this->update('tasks', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
    }
}