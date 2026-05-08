<?php

namespace App\Models;

use App\Core\Model;

class LogModel extends Model
{
    public function logAction(string $entityType, int $entityId, string $action, ?array $details = null, ?int $userId = null, ?string $ipAddress = null): string
    {
        return $this->insert('logs', [
            'user_id' => $userId ?? ($_SESSION['user_id'] ?? null),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? null),
        ]);
    }

    public function getByEntity(string $entityType, int $entityId, int $limit = 50): array
    {
        return $this->query(
            'SELECT l.*, u.name as user_name FROM logs l LEFT JOIN users u ON l.user_id = u.id WHERE l.entity_type = :type AND l.entity_id = :eid ORDER BY l.created_at DESC LIMIT :limit',
            ['type' => $entityType, 'eid' => $entityId, 'limit' => $limit]
        )->fetchAll();
    }

    public function getByUser(int $userId, int $limit = 50): array
    {
        return $this->query(
            'SELECT * FROM logs WHERE user_id = :uid ORDER BY created_at DESC LIMIT :limit',
            ['uid' => $userId, 'limit' => $limit]
        )->fetchAll();
    }

    public function findAllWithFilters(
        int $page = 1,
        int $perPage = 50,
        ?int $userId = null,
        ?string $action = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $conditions = ['1=1'];

        if ($userId !== null) {
            $conditions[] = 'l.user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if ($action !== null && $action !== '') {
            $conditions[] = 'l.action = :action';
            $params['action'] = $action;
        }

        if ($entityType !== null && $entityType !== '') {
            $conditions[] = 'l.entity_type = :entity_type';
            $params['entity_type'] = $entityType;
        }

        if ($entityId !== null) {
            $conditions[] = 'l.entity_id = :entity_id';
            $params['entity_id'] = $entityId;
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $conditions[] = 'l.created_at >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== null && $dateTo !== '') {
            $conditions[] = 'l.created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        $where = implode(' AND ', $conditions);

        $countResult = $this->query(
            "SELECT COUNT(*) as total FROM logs l WHERE {$where}",
            $params
        )->fetch();
        $total = (int)($countResult['total'] ?? 0);

        $results = $this->query(
            "SELECT l.*, u.name as user_name, u.status as user_status
             FROM logs l
             LEFT JOIN users u ON l.user_id = u.id
             WHERE {$where}
             ORDER BY l.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        )->fetchAll();

        return [
            'logs' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ];
    }

    public function countWithFilters(
        ?int $userId = null,
        ?string $action = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): int {
        $params = [];
        $conditions = ['1=1'];

        if ($userId !== null) {
            $conditions[] = 'user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if ($action !== null && $action !== '') {
            $conditions[] = 'action = :action';
            $params['action'] = $action;
        }

        if ($entityType !== null && $entityType !== '') {
            $conditions[] = 'entity_type = :entity_type';
            $params['entity_type'] = $entityType;
        }

        if ($entityId !== null) {
            $conditions[] = 'entity_id = :entity_id';
            $params['entity_id'] = $entityId;
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $conditions[] = 'created_at >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== null && $dateTo !== '') {
            $conditions[] = 'created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        $where = implode(' AND ', $conditions);

        $result = $this->query(
            "SELECT COUNT(*) as total FROM logs WHERE {$where}",
            $params
        )->fetch();

        return (int)($result['total'] ?? 0);
    }

    public function getDistinctActions(): array
    {
        return $this->query(
            "SELECT DISTINCT action FROM logs ORDER BY action ASC"
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getDistinctEntityTypes(): array
    {
        return $this->query(
            "SELECT DISTINCT entity_type FROM logs ORDER BY entity_type ASC"
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function findById(int $id): ?array
    {
        $result = $this->query(
            "SELECT l.*, u.name as user_name, u.status as user_status
             FROM logs l
             LEFT JOIN users u ON l.user_id = u.id
             WHERE l.id = :id",
            ['id' => $id]
        )->fetch();
        return $result ?: null;
    }

    public function getForExport(
        ?int $userId = null,
        ?string $action = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $limit = 10000
    ): array {
        $params = [];
        $conditions = ['1=1'];

        if ($userId !== null) {
            $conditions[] = 'l.user_id = :user_id';
            $params['user_id'] = $userId;
        }

        if ($action !== null && $action !== '') {
            $conditions[] = 'l.action = :action';
            $params['action'] = $action;
        }

        if ($entityType !== null && $entityType !== '') {
            $conditions[] = 'l.entity_type = :entity_type';
            $params['entity_type'] = $entityType;
        }

        if ($entityId !== null) {
            $conditions[] = 'l.entity_id = :entity_id';
            $params['entity_id'] = $entityId;
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $conditions[] = 'l.created_at >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== null && $dateTo !== '') {
            $conditions[] = 'l.created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        $where = implode(' AND ', $conditions);

        return $this->query(
            "SELECT l.id, u.name as user_name, l.action, l.entity_type, l.entity_id, 
                    l.details, l.ip_address, l.created_at
             FROM logs l
             LEFT JOIN users u ON l.user_id = u.id
             WHERE {$where}
             ORDER BY l.created_at DESC
             LIMIT :limit",
            array_merge($params, ['limit' => $limit])
        )->fetchAll();
    }
}