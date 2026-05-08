<?php

namespace App\Models;

use App\Core\Model;

class TicketAttachmentModel extends Model
{
    public function findByTicketId(int $ticketId): array
    {
        return $this->query(
            "SELECT * FROM ticket_attachments WHERE ticket_id = :ticket_id ORDER BY created_at ASC",
            ['ticket_id' => $ticketId]
        )->fetchAll();
    }

    public function findByMessageId(int $messageId): array
    {
        return $this->query(
            "SELECT * FROM ticket_attachments WHERE message_id = :message_id ORDER BY created_at ASC",
            ['message_id' => $messageId]
        )->fetchAll();
    }

    public function findById(int $id): ?array
    {
        return $this->findOne('ticket_attachments', 'id = :id', ['id' => $id]);
    }

    public function create(array $data): string
    {
        return $this->insert('ticket_attachments', $data);
    }

    public function deleteById(int $id): void
    {
        $this->delete('ticket_attachments', 'id = :id', ['id' => $id]);
    }

    public function countByTicketId(int $ticketId): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as total FROM ticket_attachments WHERE ticket_id = :ticket_id",
            ['ticket_id' => $ticketId]
        )->fetch();
        return (int)($result['total'] ?? 0);
    }
}