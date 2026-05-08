<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\FileModel;
use App\Models\UserModel;
use App\Models\EmployeeModel;
use App\Models\LogModel;

class FileController extends Controller
{
    protected FileModel $fileModel;
    protected UserModel $userModel;
    protected EmployeeModel $employeeModel;
    protected LogModel $logModel;

    private const MAX_FILE_SIZE = 5242880; // 5MB
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg', 'image/png', 'image/gif',
        'application/pdf',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip', 'application/x-rar-compressed',
    ];
    private const VALID_PRIORITIES = ['منخفضة', 'متوسطة', 'عالية'];

    public function __construct()
    {
        $this->fileModel = new FileModel();
        $this->userModel = new UserModel();
        $this->employeeModel = new EmployeeModel();
        $this->logModel = new LogModel();
    }

    public function index(): void
    {
        $config = require ROOT_PATH . '/config/app.php';
        $perPage = (int)($config['per_page'] ?? 15);
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $priorityFilter = $_GET['priority'] ?? 'all';
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;

        if ($page < 1) {
            $page = 1;
        }

        $adminView = Auth::isAdmin();
        $userId = null;
        $filterUserId = null;

        if (!$adminView) {
            $userId = Auth::id();
        } else {
            $filterUserId = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;
        }

        $result = $this->fileModel->findAllActive(
            $search, $page, $perPage,
            $priorityFilter !== 'all' ? $priorityFilter : null,
            $adminView ? null : $userId,
            $dateFrom,
            $dateTo,
            $adminView,
            $adminView ? $filterUserId : null
        );

        $storageInfo = $this->getStorageInfo(Auth::id());

        $employees = $adminView ? $this->employeeModel->findAllActive('', 1, 1000, null, 'active')['employees'] : [];

        $this->render('files/index', [
            'pageTitle' => 'الملفات',
            'activePage' => 'files',
            'files' => $result['files'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'search' => $search,
            'priorityFilter' => $priorityFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'employees' => $employees,
            'filterUserId' => $filterUserId,
            'adminView' => $adminView,
            'storageInfo' => $storageInfo,
            'perPage' => $perPage,
            'pageScripts' => ['/js/files.js'],
        ]);
    }

    public function createForm(): void
    {
        $storageInfo = $this->getStorageInfo(Auth::id());

        $this->render('files/create', [
            'pageTitle' => 'رفع ملف جديد',
            'activePage' => 'files',
            'storageInfo' => $storageInfo,
            'pageScripts' => ['/js/files.js'],
        ]);
    }

    public function create(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = $_POST['_csrf_token'] ?? '';

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $priority = $_POST['priority'] ?? 'متوسطة';
        $notes = trim($_POST['notes'] ?? '');
        $file = $_FILES['file'] ?? null;

        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'اسم الملف مطلوب';
        } elseif (mb_strlen($name) > 200) {
            $errors['name'] = 'اسم الملف يجب أن لا يتجاوز 200 حرف';
        }

        if (empty($priority) || !in_array($priority, self::VALID_PRIORITIES)) {
            $errors['priority'] = 'يجب اختيار الأولوية';
        }

        if (!empty($notes) && mb_strlen($notes) > 1000) {
            $errors['notes'] = 'الملاحظات يجب أن لا تتجاوز 1000 حرف';
        }

        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors['file'] = 'الملف مطلوب';
        } else {
            if ($file['size'] > self::MAX_FILE_SIZE) {
                $errors['file'] = 'حجم الملف يتجاوز الحد المسموح (5 ميجابايت)';
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
                $errors['file'] = 'نوع الملف غير مدعوم';
            }
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $quotaCheck = $this->fileModel->checkQuota(Auth::id(), (int)$file['size']);
        if (!$quotaCheck['can_upload']) {
            http_response_code(413);
            $usedFormatted = $this->formatBytes($quotaCheck['used']);
            $quotaFormatted = $quotaCheck['unlimited'] ? 'غير محدود' : $this->formatBytes($quotaCheck['quota']);
            echo json_encode([
                'success' => false,
                'message' => "مساحة التخزين غير كافية. المستخدم: {$usedFormatted} / {$quotaFormatted}",
            ]);
            return;
        }

        $originalName = $file['name'];
        $storedName = $this->fileModel->generateStoredName($originalName);
        $uploadDir = ROOT_PATH . '/storage/uploads/files/';
        $filePath = 'storage/uploads/files/' . $storedName;
        $fullPath = $uploadDir . $storedName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'فشل في حفظ الملف']);
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        $data = [
            'user_id' => Auth::id(),
            'name' => $name,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'file_type' => $mimeType,
            'priority' => $priority,
            'notes' => $notes ?: null,
        ];

        $id = $this->fileModel->create($data);

        $this->logModel->logAction('files', (int)$id, 'upload', ['name' => $name], null, $_SERVER['REMOTE_ADDR'] ?? null);

        $newStorageInfo = $this->getStorageInfo(Auth::id());

        echo json_encode([
            'success' => true,
            'message' => 'تم رفع الملف بنجاح',
            'file' => [
                'id' => $id,
                'name' => $name,
            ],
            'storage_used' => $newStorageInfo['storage_used_formatted'],
            'storage_quota' => $newStorageInfo['storage_quota_formatted'],
        ]);
    }

    public function editForm(int $id): void
    {
        $file = $this->fileModel->findById($id);

        if (!$file) {
            http_response_code(404);
            $this->render('errors/404', ['pageTitle' => 'الملف غير موجود', 'activePage' => '']);
            return;
        }

        if (!Auth::isAdmin() && (int)$file['user_id'] !== Auth::id()) {
            http_response_code(403);
            $this->render('errors/403', ['pageTitle' => 'غير مصرح', 'activePage' => '']);
            return;
        }

        $this->render('files/edit', [
            'pageTitle' => 'تعديل الملف',
            'activePage' => 'files',
            'file' => $file,
            'pageScripts' => ['/js/files.js'],
        ]);
    }

    public function update(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $file = $this->fileModel->findById($id);
        if (!$file) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'الملف غير موجود']);
            return;
        }

        if (!Auth::isAdmin() && (int)$file['user_id'] !== Auth::id()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بتعديل هذا الملف']);
            return;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = $input['_csrf_token'] ?? '';

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $name = trim($input['name'] ?? '');
        $priority = $input['priority'] ?? '';
        $notes = trim($input['notes'] ?? '');

        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'اسم الملف مطلوب';
        } elseif (mb_strlen($name) > 200) {
            $errors['name'] = 'اسم الملف يجب أن لا يتجاوز 200 حرف';
        }

        if (empty($priority) || !in_array($priority, self::VALID_PRIORITIES)) {
            $errors['priority'] = 'يجب اختيار الأولوية';
        }

        if (!empty($notes) && mb_strlen($notes) > 1000) {
            $errors['notes'] = 'الملاحظات يجب أن لا تتجاوز 1000 حرف';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $data = [
            'name' => $name,
            'priority' => $priority,
            'notes' => $notes ?: null,
        ];

        $this->fileModel->updateFile($id, $data);

        $this->logModel->logAction('files', $id, 'edit', ['name' => $name], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث الملف بنجاح',
        ]);
    }

    public function delete(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $file = $this->fileModel->findById($id);
        if (!$file) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'الملف غير موجود']);
            return;
        }

        if (!Auth::isAdmin() && (int)$file['user_id'] !== Auth::id()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بحذف هذا الملف']);
            return;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = $input['_csrf_token'] ?? '';

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $freedBytes = $this->fileModel->softDelete($id);
        $freedFormatted = $freedBytes ? $this->formatBytes($freedBytes) : '0 B';

        $this->logModel->logAction('files', $id, 'delete', ['name' => $file['name'], 'freed' => $freedFormatted], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الملف بنجاح',
            'storage_freed' => $freedFormatted,
        ]);
    }

    public function download(int $id): void
    {
        $file = $this->fileModel->findById($id);

        if (!$file) {
            http_response_code(404);
            echo 'الملف غير موجود';
            return;
        }

        if (!Auth::isAdmin() && (int)$file['user_id'] !== Auth::id()) {
            http_response_code(403);
            echo 'غير مصرح بالوصول لهذا الملف';
            return;
        }

        $filePath = ROOT_PATH . '/' . $file['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'الملف غير موجود';
            return;
        }

        $this->logModel->logAction('files', $id, 'download', ['name' => $file['name']], null, $_SERVER['REMOTE_ADDR'] ?? null);

        header('Content-Type: ' . $file['file_type']);
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . $file['file_size']);
        readfile($filePath);
    }

    public function storageInfo(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $info = $this->getStorageInfo(Auth::id());

        echo json_encode([
            'success' => true,
            'storage_used' => $info['storage_used'],
            'storage_quota' => $info['storage_quota'],
            'storage_used_formatted' => $info['storage_used_formatted'],
            'storage_quota_formatted' => $info['storage_quota_formatted'],
            'percentage' => $info['percentage'],
            'unlimited' => $info['unlimited'],
        ]);
    }

    public function updateQuota(int $userId): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بتعديل مساحة التخزين']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = $input['_csrf_token'] ?? '';

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $newQuota = $input['storage_quota'] ?? null;

        if ($newQuota !== null) {
            $newQuota = (int)$newQuota;
            if ($newQuota < 0) {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => 'قيمة المساحة غير صحيحة']);
                return;
            }
        }

        $currentUsage = $this->fileModel->getStorageUsage($userId);
        if ($newQuota !== null && $newQuota < $currentUsage) {
            http_response_code(400);
            $usedFormatted = $this->formatBytes($currentUsage);
            echo json_encode(['success' => false, 'message' => "لا يمكن تعيين مساحة أقل من المستخدم حالياً ({$usedFormatted})"]);
            return;
        }

        $this->userModel->updateUser($userId, ['storage_quota' => $newQuota]);

        $this->logModel->logAction('users', $userId, 'quota_change', ['new_quota' => $newQuota ? $this->formatBytes($newQuota) : 'unlimited'], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث مساحة التخزين بنجاح',
        ]);
    }

    private function getStorageInfo(int $userId): array
    {
        $userResult = $this->query(
            "SELECT storage_quota FROM users WHERE id = :id",
            ['id' => $userId]
        )->fetch();

        $quota = $userResult['storage_quota'] ?? null;
        $used = $this->fileModel->getStorageUsage($userId);
        $unlimited = $quota === null;

        return [
            'storage_used' => $used,
            'storage_quota' => $unlimited ? null : (int)$quota,
            'storage_used_formatted' => $this->formatBytes($used),
            'storage_quota_formatted' => $unlimited ? 'غير محدود' : $this->formatBytes((int)$quota),
            'percentage' => $unlimited ? null : ($quota > 0 ? round(($used / $quota) * 100, 1) : 0),
            'unlimited' => $unlimited,
        ];
    }

    private function query(string $sql, array $params = [])
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 1) . $units[$pow];
    }
}