<?php

namespace App\Models;

use App\Core\Model;

class FileModel extends Model
{
    private const MAX_FILE_SIZE = 5242880; // 5MB
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg', 'image/png', 'image/gif',
        'application/pdf',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip', 'application/x-rar-compressed',
    ];
    private const VALID_PRIORITIES = ['منخفضة', 'متوسطة', 'عالية'];

    public function findAllActive(
        string $search = '',
        int $page = 1,
        int $perPage = 15,
        ?string $priority = null,
        ?int $userId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        bool $adminView = false,
        ?int $filterUserId = null
    ): array {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $conditions = ['f.deleted_at IS NULL'];

        if (!$adminView && $userId !== null) {
            $conditions[] = 'f.user_id = :user_id';
            $params['user_id'] = $userId;
        } elseif ($adminView && $filterUserId !== null) {
            $conditions[] = 'f.user_id = :filter_user_id';
            $params['filter_user_id'] = $filterUserId;
        }

        if ($search !== '') {
            $conditions[] = '(f.name LIKE :search OR f.original_name LIKE :search2)';
            $params['search'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        if ($priority !== null && $priority !== 'all' && in_array($priority, self::VALID_PRIORITIES)) {
            $conditions[] = 'f.priority = :priority';
            $params['priority'] = $priority;
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $conditions[] = 'f.created_at >= :date_from';
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== null && $dateTo !== '') {
            $conditions[] = 'f.created_at <= :date_to';
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        $where = implode(' AND ', $conditions);

        $countResult = $this->query(
            "SELECT COUNT(*) as total FROM files f" . ($adminView ? " LEFT JOIN users u ON f.user_id = u.id" : "") . " WHERE {$where}",
            $params
        )->fetch();
        $total = (int)($countResult['total'] ?? 0);

        $selectFields = $adminView 
            ? "f.*, u.name as uploader_name, u.employee_code as uploader_code"
            : "f.*";
        
        $joinClause = $adminView 
            ? "LEFT JOIN users u ON f.user_id = u.id"
            : "";

        $results = $this->query(
            "SELECT {$selectFields} FROM files f {$joinClause} WHERE {$where} ORDER BY f.created_at DESC LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        )->fetchAll();

        return [
            'files' => $results,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ];
    }

    public function findById(int $id): ?array
    {
        $result = $this->query(
            "SELECT f.*, u.name as uploader_name, u.employee_code as uploader_code
             FROM files f
             LEFT JOIN users u ON f.user_id = u.id
             WHERE f.id = :id AND f.deleted_at IS NULL",
            ['id' => $id]
        )->fetch();
        return $result ?: null;
    }

    public function findByUserId(int $userId): array
    {
        return $this->query(
            "SELECT * FROM files WHERE user_id = :user_id AND deleted_at IS NULL ORDER BY created_at DESC",
            ['user_id' => $userId]
        )->fetchAll();
    }

    public function create(array $data): string
    {
        return $this->insert('files', $data);
    }

    public function updateFile(int $id, array $data): void
    {
        $this->update('files', $data, 'id = :id AND deleted_at IS NULL', ['id' => $id]);
    }

    public function softDelete(int $id): ?int
    {
        $file = $this->findById($id);
        if (!$file) {
            return null;
        }
        $this->update('files', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $id]);
        return (int)$file['file_size'];
    }

    public function getStorageUsage(int $userId): int
    {
        $result = $this->query(
            "SELECT COALESCE(SUM(file_size), 0) as total_size FROM files WHERE user_id = :user_id AND deleted_at IS NULL",
            ['user_id' => $userId]
        )->fetch();
        return (int)($result['total_size'] ?? 0);
    }

    public function checkQuota(int $userId, int $additionalBytes = 0): array
    {
        $userResult = $this->query(
            "SELECT storage_quota FROM users WHERE id = :id",
            ['id' => $userId]
        )->fetch();

        $quota = $userResult['storage_quota'] ?? null;
        $unlimited = $quota === null;
        $used = $this->getStorageUsage($userId);
        $totalWithNew = $used + $additionalBytes;

        return [
            'unlimited' => $unlimited,
            'quota' => $unlimited ? null : (int)$quota,
            'used' => $used,
            'available' => $unlimited ? PHP_INT_MAX : max(0, (int)$quota - $used),
            'would_exceed' => !$unlimited && $totalWithNew > $quota,
            'can_upload' => $unlimited || $totalWithNew <= $quota,
        ];
    }

    public function generateStoredName(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $uuid = $this->generateUuid();
        $timestamp = time();
        return $uuid . '_' . $timestamp . '.' . $extension;
    }

    public function validateFileType(string $mimeType): bool
    {
        return in_array($mimeType, self::ALLOWED_MIME_TYPES);
    }

    public function validateFileSize(int $size): bool
    {
        return $size > 0 && $size <= self::MAX_FILE_SIZE;
    }

    public function validatePriority(string $priority): bool
    {
        return in_array($priority, self::VALID_PRIORITIES);
    }

    public function getAllowedMimeTypes(): array
    {
        return self::ALLOWED_MIME_TYPES;
    }

    public function getMaxFileSize(): int
    {
        return self::MAX_FILE_SIZE;
    }

    public function getValidPriorities(): array
    {
        return self::VALID_PRIORITIES;
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}