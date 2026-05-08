<?php

namespace App\Models;

use App\Core\Model;

class TicketModel extends Model
{
    public function findAllActive(string $search = '', int $page = 1, int $perPage = 15, ?string $status = null, ?string $category = null, ?int $createdBy = null, bool $adminView = true): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $conditions = ['t.deleted_at IS NULL'];

        if (!$adminView && $createdBy !== null) {
            $conditions[] = 't.created_by = :created_by';
            $params['created_by'] = $createdBy;
        } elseif ($createdBy !== null && $adminView) {
            $conditions[] = 't.created_by = :created_by';
            $params['created_by'] = $createdBy;
        }

        if ($search !== '') {
            $conditions[] = '(t.title LIKE :search OR t.ticket_code LIKE :search2 OR u.name LIKE :search3)';
            $params['search'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
        }

        if ($status !== null && $status !== 'all') {
            $conditions[] = 't.status = :status';
            $params['status'] = $status;
        }

        if ($category !== null && $category !== 'all' && $category !== '') {
            $conditions[] = 't.category = :category';
            $params['category'] = $category;
        }

        $where = implode(' AND ', $conditions);

        $countResult = $this->query(
            "SELECT COUNT(*) as total FROM tickets t LEFT JOIN users u ON t.created_by = u.id WHERE {$where}",
            $params
        )->fetch();
        $total = (int)($countResult['total'] ?? 0);

        $results = $this->query(
            "SELECT t.*, u.name as creator_name, u.employee_code as creator_code, u.status as creator_status,
                    tr.last_read_at
             FROM tickets t
             LEFT JOIN users u ON t.created_by = u.id
             LEFT JOIN ticket_reads tr ON tr.ticket_id = t.id AND tr.user_id = :current_user_id
             WHERE {$where}
             ORDER BY t.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset, 'current_user_id' => $createdBy ?? 0])
        )->fetchAll();

        return [
            'tickets' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ];
    }

    public function findById(int $id): ?array
    {
        $result = $this->query(
            "SELECT t.*, u.name as creator_name, u.employee_code as creator_code, u.status as creator_status
             FROM tickets t
             LEFT JOIN users u ON t.created_by = u.id
             WHERE t.id = :id AND t.deleted_at IS NULL",
            ['id' => $id]
        )->fetch();
        return $result ?: null;
    }

    public function generateTicketCode(): string
    {
        $result = $this->query(
            "SELECT MAX(CAST(SUBSTRING(ticket_code, 5) AS UNSIGNED)) as max_code FROM tickets WHERE ticket_code IS NOT NULL"
        )->fetch();

        $maxCode = (int)($result['max_code'] ?? 0);
        $nextCode = $maxCode + 1;
        return 'TKT-' . str_pad((string)$nextCode, 3, '0', STR_PAD_LEFT);
    }

    public function create(array $data): string
    {
        return $this->insert('tickets', $data);
    }

    public function updateTicket(int $id, array $data): void
    {
        $this->update('tickets', $data, 'id = :id AND deleted_at IS NULL', ['id' => $id]);
    }

    public function softDelete(int $id): void
    {
        $this->update('tickets', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
    }
}