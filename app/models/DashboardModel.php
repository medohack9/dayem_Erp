<?php

namespace App\Models;

use App\Core\Model;

class DashboardModel extends Model
{
    public function getKpiStats(): array
    {
        $employeesResult = $this->query(
            "SELECT COUNT(*) as total FROM users WHERE role = 'employee' AND status = 'active' AND deleted_at IS NULL"
        )->fetch();
        $employees = (int)($employeesResult['total'] ?? 0);

        $tasksResult = $this->query(
            "SELECT COUNT(*) as total FROM tasks WHERE deleted_at IS NULL"
        )->fetch();
        $tasks = (int)($tasksResult['total'] ?? 0);

        $ticketsResult = $this->query(
            "SELECT COUNT(*) as total FROM tickets WHERE status != 'مغلق' AND deleted_at IS NULL"
        )->fetch();
        $tickets = (int)($ticketsResult['total'] ?? 0);

        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        $salariesResult = $this->query(
            "SELECT COALESCE(SUM(net_amount), 0) as total FROM salaries 
             WHERE status = 'paid' AND MONTH(paid_at) = :month AND YEAR(paid_at) = :year AND deleted_at IS NULL",
            ['month' => $currentMonth, 'year' => $currentYear]
        )->fetch();
        $salaries = (float)($salariesResult['total'] ?? 0);

        return [
            'employees' => $employees,
            'tasks' => $tasks,
            'tickets' => $tickets,
            'salaries' => $salaries,
        ];
    }

    public function getTaskStatusBreakdown(): array
    {
        $results = $this->query(
            "SELECT status, COUNT(*) as count FROM tasks WHERE deleted_at IS NULL GROUP BY status"
        )->fetchAll();

        $breakdown = [
            'قيد الانتظار' => 0,
            'قيد التنفيذ' => 0,
            'مكتمل' => 0,
        ];

        foreach ($results as $row) {
            if (isset($breakdown[$row['status']])) {
                $breakdown[$row['status']] = (int)$row['count'];
            }
        }

        return $breakdown;
    }

    public function getEmployeeStats(int $userId): array
    {
        $tasksResult = $this->query(
            "SELECT COUNT(*) as total FROM tasks WHERE assigned_to = :user_id AND deleted_at IS NULL",
            ['user_id' => $userId]
        )->fetch();
        $tasks = (int)($tasksResult['total'] ?? 0);

        $ticketsResult = $this->query(
            "SELECT COUNT(*) as total FROM tickets WHERE created_by = :user_id AND deleted_at IS NULL",
            ['user_id' => $userId]
        )->fetch();
        $tickets = (int)($ticketsResult['total'] ?? 0);

        $filesResult = $this->query(
            "SELECT COUNT(*) as total FROM files WHERE user_id = :user_id AND deleted_at IS NULL",
            ['user_id' => $userId]
        )->fetch();
        $files = (int)($filesResult['total'] ?? 0);

        $storageResult = $this->query(
            "SELECT COALESCE(SUM(file_size), 0) as total FROM files WHERE user_id = :user_id AND deleted_at IS NULL",
            ['user_id' => $userId]
        )->fetch();
        $storageUsed = (int)($storageResult['total'] ?? 0);

        $quotaResult = $this->query(
            "SELECT storage_quota FROM users WHERE id = :user_id",
            ['user_id' => $userId]
        )->fetch();
        $storageQuota = $quotaResult ? (int)($quotaResult['storage_quota'] ?? 0) : 0;

        return [
            'tasks' => $tasks,
            'tickets' => $tickets,
            'files' => $files,
            'storageUsed' => $storageUsed,
            'storageQuota' => $storageQuota,
            'storageUsedFormatted' => $this->formatBytes($storageUsed),
            'storageQuotaFormatted' => $storageQuota > 0 ? $this->formatBytes($storageQuota) : 'غير محدود',
        ];
    }

    public function getTasksChartData(bool $isAdmin = true, ?int $userId = null): array
    {
        $weeks = $this->getLast8Weeks();
        $labels = [];
        $created = [];
        $completed = [];

        foreach ($weeks as $week) {
            $labels[] = $week['label'];

            if ($isAdmin) {
                $createdResult = $this->query(
                    "SELECT COUNT(*) as total FROM tasks 
                     WHERE deleted_at IS NULL AND created_at >= :start AND created_at <= :end",
                    ['start' => $week['start'] . ' 00:00:00', 'end' => $week['end'] . ' 23:59:59']
                )->fetch();
                $created[] = (int)($createdResult['total'] ?? 0);

                $completedResult = $this->query(
                    "SELECT COUNT(*) as total FROM tasks 
                     WHERE deleted_at IS NULL AND status = 'مكتمل' AND updated_at >= :start AND updated_at <= :end",
                    ['start' => $week['start'] . ' 00:00:00', 'end' => $week['end'] . ' 23:59:59']
                )->fetch();
                $completed[] = (int)($completedResult['total'] ?? 0);
            } else {
                $createdResult = $this->query(
                    "SELECT COUNT(*) as total FROM tasks 
                     WHERE deleted_at IS NULL AND assigned_to = :user_id AND created_at >= :start AND created_at <= :end",
                    ['user_id' => $userId, 'start' => $week['start'] . ' 00:00:00', 'end' => $week['end'] . ' 23:59:59']
                )->fetch();
                $created[] = (int)($createdResult['total'] ?? 0);

                $completedResult = $this->query(
                    "SELECT COUNT(*) as total FROM tasks 
                     WHERE deleted_at IS NULL AND assigned_to = :user_id AND status = 'مكتمل' AND updated_at >= :start AND updated_at <= :end",
                    ['user_id' => $userId, 'start' => $week['start'] . ' 00:00:00', 'end' => $week['end'] . ' 23:59:59']
                )->fetch();
                $completed[] = (int)($completedResult['total'] ?? 0);
            }
        }

        return [
            'labels' => $labels,
            'created' => $created,
            'completed' => $completed,
        ];
    }

    public function getTicketsChartData(bool $isAdmin = true, ?int $userId = null): array
    {
        $weeks = $this->getLast8Weeks();
        $labels = [];
        $created = [];
        $closed = [];

        foreach ($weeks as $week) {
            $labels[] = $week['label'];

            if ($isAdmin) {
                $createdResult = $this->query(
                    "SELECT COUNT(*) as total FROM tickets 
                     WHERE deleted_at IS NULL AND created_at >= :start AND created_at <= :end",
                    ['start' => $week['start'] . ' 00:00:00', 'end' => $week['end'] . ' 23:59:59']
                )->fetch();
                $created[] = (int)($createdResult['total'] ?? 0);

                $closedResult = $this->query(
                    "SELECT COUNT(*) as total FROM tickets 
                     WHERE deleted_at IS NULL AND status = 'مغلق' AND updated_at >= :start AND updated_at <= :end",
                    ['start' => $week['start'] . ' 00:00:00', 'end' => $week['end'] . ' 23:59:59']
                )->fetch();
                $closed[] = (int)($closedResult['total'] ?? 0);
            } else {
                $createdResult = $this->query(
                    "SELECT COUNT(*) as total FROM tickets 
                     WHERE deleted_at IS NULL AND created_by = :user_id AND created_at >= :start AND created_at <= :end",
                    ['user_id' => $userId, 'start' => $week['start'] . ' 00:00:00', 'end' => $week['end'] . ' 23:59:59']
                )->fetch();
                $created[] = (int)($createdResult['total'] ?? 0);

                $closedResult = $this->query(
                    "SELECT COUNT(*) as total FROM tickets 
                     WHERE deleted_at IS NULL AND created_by = :user_id AND status = 'مغلق' AND updated_at >= :start AND updated_at <= :end",
                    ['user_id' => $userId, 'start' => $week['start'] . ' 00:00:00', 'end' => $week['end'] . ' 23:59:59']
                )->fetch();
                $closed[] = (int)($closedResult['total'] ?? 0);
            }
        }

        return [
            'labels' => $labels,
            'created' => $created,
            'closed' => $closed,
        ];
    }

    public function getLast8Weeks(): array
    {
        $weeks = [];
        $currentMonday = date('Y-m-d', strtotime('monday this week'));

        for ($i = 1; $i <= 8; $i++) {
            $weekMonday = date('Y-m-d', strtotime("-{$i} week", strtotime($currentMonday)));
            $weekSunday = date('Y-m-d', strtotime("+6 days", strtotime($weekMonday)));

            $weeks[] = [
                'start' => $weekMonday,
                'end' => $weekSunday,
                'label' => $this->getArabicWeekLabel($i),
            ];
        }

        return array_reverse($weeks);
    }

    private function getArabicWeekLabel(int $weeksAgo): string
    {
        $labels = [
            1 => 'الأسبوع الماضي',
            2 => 'منذ أسبوعين',
            3 => 'منذ 3 أسابيع',
            4 => 'منذ 4 أسابيع',
            5 => 'منذ 5 أسابيع',
            6 => 'منذ 6 أسابيع',
            7 => 'منذ 7 أسابيع',
            8 => 'منذ 8 أسابيع',
        ];

        return $labels[$weeksAgo] ?? "منذ {$weeksAgo} أسابيع";
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }
}