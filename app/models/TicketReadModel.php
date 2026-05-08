<?php

namespace App\Models;

use App\Core\Model;

class TicketReadModel extends Model
{
    public function findByTicketAndUser(int $ticketId, int $userId): ?array
    {
        return $this->findOne(
            'ticket_reads',
            'ticket_id = :ticket_id AND user_id = :user_id',
            ['ticket_id' => $ticketId, 'user_id' => $userId]
        );
    }

    public function upsert(int $ticketId, int $userId): void
    {
        $existing = $this->findByTicketAndUser($ticketId, $userId);
        if ($existing) {
            $this->update(
                'ticket_reads',
                ['last_read_at' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $existing['id']]
            );
        } else {
            $this->insert('ticket_reads', [
                'ticket_id' => $ticketId,
                'user_id' => $userId,
                'last_read_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function countUnreadForUser(int $userId): int
    {
        $result = $this->query(
            "SELECT COUNT(*) as total FROM tickets t
             LEFT JOIN ticket_reads tr ON tr.ticket_id = t.id AND tr.user_id = :user_id
             WHERE t.deleted_at IS NULL
             AND t.status != 'مغلق'
             AND (tr.last_read_at IS NULL OR tr.last_read_at < t.updated_at)",
            ['user_id' => $userId]
        )->fetch();
        return (int)($result['total'] ?? 0);
    }

    public function isUnread(int $ticketId, int $userId): bool
    {
        $ticket = $this->query(
            "SELECT t.updated_at FROM tickets t WHERE t.id = :id AND t.deleted_at IS NULL",
            ['id' => $ticketId]
        )->fetch();

        if (!$ticket) {
            return false;
        }

        $read = $this->findByTicketAndUser($ticketId, $userId);
        if (!$read) {
            return true;
        }

        return $read['last_read_at'] < $ticket['updated_at'];
    }
}