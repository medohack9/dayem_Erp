<?php

namespace App\Models;

use App\Core\Model;

class DocumentModel extends Model
{
    public function findByUserId(int $userId): array
    {
        return $this->findAll('employee_documents', 'user_id = :user_id ORDER BY created_at DESC', ['user_id' => $userId]);
    }

    public function findById(int $id): ?array
    {
        return $this->findOne('employee_documents', 'id = :id', ['id' => $id]);
    }

    public function findByUserAndType(int $userId, string $docType): ?array
    {
        return $this->findOne('employee_documents', 'user_id = :user_id AND doc_type = :doc_type', ['user_id' => $userId, 'doc_type' => $docType]);
    }

    public function findByUserAndId(int $userId, int $id): ?array
    {
        return $this->findOne('employee_documents', 'id = :id AND user_id = :user_id', ['id' => $id, 'user_id' => $userId]);
    }

    public function create(array $data): string
    {
        return $this->insert('employee_documents', $data);
    }

    public function deleteById(int $id): void
    {
        $this->delete('employee_documents', 'id = :id', ['id' => $id]);
    }

    public function deleteUserDocuments(int $userId): void
    {
        $this->delete('employee_documents', 'user_id = :user_id', ['user_id' => $userId]);
    }

    public function updateDocument(int $id, array $data): void
    {
        $this->update('employee_documents', $data, 'id = :id', ['id' => $id]);
    }
}