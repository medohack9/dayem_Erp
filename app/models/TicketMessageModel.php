<?php

namespace App\Models;

use App\Core\Model;

class TicketMessageModel extends Model
{
    public function findByTicketId(int $ticketId): array
    {
        return $this->query(
            "SELECT tm.*, u.name as sender_name, u.role as sender_role
             FROM ticket_messages tm
             LEFT JOIN users u ON tm.sender_id = u.id
             WHERE tm.ticket_id = :ticket_id
             ORDER BY tm.created_at ASC",
            ['ticket_id' => $ticketId]
        )->fetchAll();
    }

    public function create(array $data): string
    {
        return $this->insert('ticket_messages', $data);
    }

    public function countByTicketId(int $ticketId): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as total FROM ticket_messages WHERE ticket_id = :ticket_id",
            ['ticket_id' => $ticketId]
        )->fetch();
        return (int)($result['total'] ?? 0);
    }
}