<?php

namespace App\Models;

use App\Core\Model;

class TaskNoteModel extends Model
{
    public function findByTaskId(int $taskId): array
    {
        return $this->query(
            "SELECT tn.*, u.name as user_name, u.role as user_role
             FROM task_notes tn
             LEFT JOIN users u ON tn.user_id = u.id
             WHERE tn.task_id = :task_id
             ORDER BY tn.created_at ASC",
            ['task_id' => $taskId]
        )->fetchAll();
    }

    public function create(array $data): string
    {
        return $this->insert('task_notes', $data);
    }

    public function deleteById(int $id): void
    {
        $this->delete('task_notes', 'id = :id', ['id' => $id]);
    }
}