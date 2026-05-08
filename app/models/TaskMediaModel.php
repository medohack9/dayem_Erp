<?php

namespace App\Models;

use App\Core\Model;

class TaskMediaModel extends Model
{
    public function findByTaskId(int $taskId): array
    {
        return $this->query(
            "SELECT tm.*, u.name as user_name
             FROM task_media tm
             LEFT JOIN users u ON tm.user_id = u.id
             WHERE tm.task_id = :task_id
             ORDER BY tm.created_at ASC",
            ['task_id' => $taskId]
        )->fetchAll();
    }

    public function findById(int $id): ?array
    {
        return $this->findOne('task_media', 'id = :id', ['id' => $id]);
    }

    public function create(array $data): string
    {
        return $this->insert('task_media', $data);
    }

    public function deleteById(int $id): void
    {
        $this->delete('task_media', 'id = :id', ['id' => $id]);
    }
}