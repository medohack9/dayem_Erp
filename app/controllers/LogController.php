<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\LogModel;
use App\Models\UserModel;

class LogController extends Controller
{
    protected LogModel $logModel;
    protected UserModel $userModel;

    private const PER_PAGE = 50;
    private const EXPORT_LIMIT = 10000;

    public function __construct()
    {
        $this->logModel = new LogModel();
        $this->userModel = new UserModel();
    }

    public function index(): void
    {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            $this->render('errors/403', ['pageTitle' => 'غير مصرح', 'activePage' => '']);
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        $userId = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;
        $action = $_GET['action'] ?? null;
        $entityType = $_GET['entity_type'] ?? null;
        $entityId = isset($_GET['entity_id']) && $_GET['entity_id'] !== '' ? (int)$_GET['entity_id'] : null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;

        if ($action === '') {
            $action = null;
        }
        if ($entityType === '') {
            $entityType = null;
        }
        if ($dateFrom === '') {
            $dateFrom = null;
        }
        if ($dateTo === '') {
            $dateTo = null;
        }

        $result = $this->logModel->findAllWithFilters(
            $page,
            self::PER_PAGE,
            $userId,
            $action,
            $entityType,
            $entityId,
            $dateFrom,
            $dateTo
        );

        $users = $this->getAllUsers();
        $actions = $this->logModel->getDistinctActions();
        $entityTypes = $this->logModel->getDistinctEntityTypes();

        $this->render('logs/index', [
            'pageTitle' => 'سجلات النظام',
            'activePage' => 'logs',
            'logs' => $result['logs'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'perPage' => self::PER_PAGE,
            'users' => $users,
            'actions' => $actions,
            'entityTypes' => $entityTypes,
            'filters' => [
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'pageScripts' => ['/js/logs.js'],
        ]);
    }

    public function list(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح']);
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        $userId = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;
        $action = $_GET['action'] ?? null;
        $entityType = $_GET['entity_type'] ?? null;
        $entityId = isset($_GET['entity_id']) && $_GET['entity_id'] !== '' ? (int)$_GET['entity_id'] : null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;

        if ($action === '') {
            $action = null;
        }
        if ($entityType === '') {
            $entityType = null;
        }
        if ($dateFrom === '') {
            $dateFrom = null;
        }
        if ($dateTo === '') {
            $dateTo = null;
        }

        $result = $this->logModel->findAllWithFilters(
            $page,
            self::PER_PAGE,
            $userId,
            $action,
            $entityType,
            $entityId,
            $dateFrom,
            $dateTo
        );

        echo json_encode([
            'success' => true,
            'logs' => $result['logs'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
        ]);
    }

    public function detail(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح']);
            return;
        }

        $log = $this->logModel->findById($id);

        if (!$log) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'السجل غير موجود']);
            return;
        }

        $log['details_parsed'] = $log['details'] ? json_decode($log['details'], true) : null;

        echo json_encode([
            'success' => true,
            'log' => $log,
        ]);
    }

    public function export(): void
    {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'غير مصرح';
            return;
        }

        $userId = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;
        $action = $_GET['action'] ?? null;
        $entityType = $_GET['entity_type'] ?? null;
        $entityId = isset($_GET['entity_id']) && $_GET['entity_id'] !== '' ? (int)$_GET['entity_id'] : null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;

        if ($action === '') {
            $action = null;
        }
        if ($entityType === '') {
            $entityType = null;
        }
        if ($dateFrom === '') {
            $dateFrom = null;
        }
        if ($dateTo === '') {
            $dateTo = null;
        }

        $logs = $this->logModel->getForExport(
            $userId,
            $action,
            $entityType,
            $entityId,
            $dateFrom,
            $dateTo,
            self::EXPORT_LIMIT
        );

        $filename = 'logs_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv($output, ['المستخدم', 'الإجراء', 'النوع', 'الرقم', 'التفاصيل', 'IP', 'الوقت']);

        foreach ($logs as $log) {
            fputcsv($output, [
                $log['user_name'] ?? 'مستخدم محذوف',
                $log['action'],
                $log['entity_type'],
                $log['entity_id'],
                $log['details'] ?? '',
                $log['ip_address'] ?? '',
                $log['created_at'],
            ]);
        }

        fclose($output);
    }

    public function exportToday(): void
    {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'غير مصرح';
            return;
        }

        $today = date('Y-m-d');

        $logs = $this->logModel->getForExport(
            null,
            null,
            null,
            null,
            $today,
            $today,
            self::EXPORT_LIMIT
        );

        $filename = 'logs_today_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv($output, ['المستخدم', 'الإجراء', 'النوع', 'الرقم', 'التفاصيل', 'IP', 'الوقت']);

        foreach ($logs as $log) {
            fputcsv($output, [
                $log['user_name'] ?? 'مستخدم محذوف',
                $log['action'],
                $log['entity_type'],
                $log['entity_id'],
                $log['details'] ?? '',
                $log['ip_address'] ?? '',
                $log['created_at'],
            ]);
        }

        fclose($output);
    }

    public function filterOptions(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح']);
            return;
        }

        $users = $this->getAllUsers();
        $actions = $this->logModel->getDistinctActions();
        $entityTypes = $this->logModel->getDistinctEntityTypes();

        echo json_encode([
            'success' => true,
            'actions' => $actions,
            'entity_types' => $entityTypes,
            'users' => $users,
        ]);
    }

    private function getAllUsers(): array
    {
        return $this->query(
            "SELECT id, name, status FROM users WHERE deleted_at IS NULL ORDER BY name ASC"
        )->fetchAll();
    }

    private function query(string $sql, array $params = [])
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}