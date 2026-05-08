<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\TicketModel;
use App\Models\TicketMessageModel;
use App\Models\TicketAttachmentModel;
use App\Models\TicketReadModel;
use App\Models\EmployeeModel;
use App\Models\LogModel;

class TicketController extends Controller
{
    protected TicketModel $ticketModel;
    protected TicketMessageModel $messageModel;
    protected TicketAttachmentModel $attachmentModel;
    protected TicketReadModel $readModel;
    protected EmployeeModel $employeeModel;
    protected LogModel $logModel;

    private const MAX_ATTACHMENT_SIZE = 5242880; // 5MB
    private const MAX_ATTACHMENTS = 10;
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg', 'image/png', 'image/gif',
        'application/pdf',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip',
    ];
    private const VALID_STATUSES = ['مفتوح', 'قيد المراجعة', 'تم الرد', 'مغلق'];
    private const VALID_CATEGORIES = ['تقرير مشكلة', 'طلب صيانة', 'طلب معلومات', 'أخرى'];

    public function __construct()
    {
        $this->ticketModel = new TicketModel();
        $this->messageModel = new TicketMessageModel();
        $this->attachmentModel = new TicketAttachmentModel();
        $this->readModel = new TicketReadModel();
        $this->employeeModel = new EmployeeModel();
        $this->logModel = new LogModel();
    }

    public function index(): void
    {
        $config = require ROOT_PATH . '/config/app.php';
        $perPage = (int)($config['per_page'] ?? 15);
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $statusFilter = $_GET['status'] ?? 'all';
        $categoryFilter = $_GET['category'] ?? 'all';

        if ($page < 1) {
            $page = 1;
        }

        $adminView = Auth::isAdmin();
        $createdBy = null;

        if (!$adminView) {
            $createdBy = Auth::id();
        } else {
            $createdBy = isset($_GET['employee_id']) && $_GET['employee_id'] !== '' ? (int)$_GET['employee_id'] : null;
        }

        $result = $this->ticketModel->findAllActive(
            $search, $page, $perPage,
            $statusFilter !== 'all' ? $statusFilter : null,
            $categoryFilter !== 'all' ? $categoryFilter : null,
            $adminView ? $createdBy : Auth::id(),
            $adminView
        );

        $employees = $adminView ? $this->employeeModel->findAllActive('', 1, 1000, null, 'active')['employees'] : [];

        $this->render('tickets/index', [
            'pageTitle' => 'التذاكر',
            'activePage' => 'tickets',
            'tickets' => $result['tickets'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['totalPages'],
            'search' => $search,
            'statusFilter' => $statusFilter,
            'categoryFilter' => $categoryFilter,
            'employees' => $employees,
            'adminView' => $adminView,
            'perPage' => $perPage,
            'pageScripts' => ['/js/tickets.js'],
        ]);
    }

    public function createForm(): void
    {
        $this->render('tickets/create', [
            'pageTitle' => 'تذكرة جديدة',
            'activePage' => 'tickets',
            'pageScripts' => ['/js/tickets.js'],
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

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? '';
        $attachments = $_FILES['attachments'] ?? [];

        $errors = [];

        if (empty($title)) {
            $errors['title'] = 'عنوان التذكرة مطلوب';
        } elseif (mb_strlen($title) > 200) {
            $errors['title'] = 'عنوان التذكرة يجب أن لا يتجاوز 200 حرف';
        }

        if (empty($description)) {
            $errors['description'] = 'وصف التذكرة مطلوب';
        } elseif (mb_strlen($description) > 5000) {
            $errors['description'] = 'وصف التذكرة يجب أن لا يتجاوز 5000 حرف';
        }

        if (empty($category) || !in_array($category, self::VALID_CATEGORIES)) {
            $errors['category'] = 'يجب اختيار التصنيف';
        }

        $attachmentErrors = $this->validateAttachments($attachments);
        if (!empty($attachmentErrors)) {
            $errors['attachments'] = $attachmentErrors;
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $ticketCode = $this->ticketModel->generateTicketCode();

        $data = [
            'ticket_code' => $ticketCode,
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'status' => 'مفتوح',
            'created_by' => Auth::id(),
        ];

        $id = $this->ticketModel->create($data);

        if (!empty($attachments) && !empty($attachments['name'][0])) {
            $this->processAttachments($attachments, (int)$id, null);
        }

        $this->logModel->logAction('ticket', (int)$id, 'create', null, null, $_SERVER['REMOTE_ADDR'] ?? null);

        $ticket = $this->ticketModel->findById((int)$id);

        echo json_encode([
            'success' => true,
            'message' => 'تم إنشاء التذكرة بنجاح',
            'ticket' => $ticket,
        ]);
    }

    public function show(int $id): void
    {
        $ticket = $this->ticketModel->findById($id);

        if (!$ticket) {
            http_response_code(404);
            $this->render('errors/404', ['pageTitle' => 'التذكرة غير موجودة', 'activePage' => '']);
            return;
        }

        if (!Auth::isAdmin() && (int)$ticket['created_by'] !== Auth::id()) {
            http_response_code(403);
            $this->render('errors/403', ['pageTitle' => 'غير مصرح', 'activePage' => '']);
            return;
        }

        $messages = $this->messageModel->findByTicketId($id);
        $attachments = $this->attachmentModel->findByTicketId($id);

        $messageAttachments = [];
        foreach ($attachments as $att) {
            $msgId = $att['message_id'] ?? 'ticket';
            if (!isset($messageAttachments[$msgId])) {
                $messageAttachments[$msgId] = [];
            }
            $messageAttachments[$msgId][] = $att;
        }

        $this->readModel->upsert($id, Auth::id());

        $this->render('tickets/show', [
            'pageTitle' => $ticket['title'],
            'activePage' => 'tickets',
            'ticket' => $ticket,
            'messages' => $messages,
            'attachments' => $attachments,
            'messageAttachments' => $messageAttachments,
            'adminView' => Auth::isAdmin(),
            'pageScripts' => ['/js/tickets.js'],
        ]);
    }

    public function addMessage(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $ticket = $this->ticketModel->findById($id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
            return;
        }

        if (!Auth::isAdmin() && (int)$ticket['created_by'] !== Auth::id()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بالوصول لهذه التذكرة']);
            return;
        }

        if (!Auth::isAdmin() && $ticket['status'] === 'مغلق') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'لا يمكن إرسال رسائل على تذكرة مغلقة']);
            return;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = strpos($contentType, 'application/json') !== false;
        $providedToken = '';
        $content = '';

        if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true);
            $providedToken = $input['_csrf_token'] ?? '';
            $content = trim($input['content'] ?? '');
        } else {
            $providedToken = $_POST['_csrf_token'] ?? '';
            $content = trim($_POST['content'] ?? '');
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        if (empty($content)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => ['content' => 'محتوى الرسالة مطلوب']]);
            return;
        }

        if (mb_strlen($content) > 5000) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => ['content' => 'محتوى الرسالة يجب أن لا يتجاوز 5000 حرف']]);
            return;
        }

        $senderType = Auth::isAdmin() ? 'admin' : 'employee';

        $messageData = [
            'ticket_id' => $id,
            'sender_id' => Auth::id(),
            'sender_type' => $senderType,
            'content' => $content,
        ];

        $messageId = $this->messageModel->create($messageData);

        $savedAttachments = [];
        if (!$isJson && !empty($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
            $savedAttachments = $this->processAttachments($_FILES['attachments'], $id, (int)$messageId);
        }

        if (Auth::isAdmin() && $ticket['status'] !== 'مغلق') {
            $this->ticketModel->updateTicket($id, ['status' => 'تم الرد']);
        }

        $this->readModel->upsert($id, Auth::id());

        $this->logModel->logAction('ticket', $id, 'reply', ['sender_type' => $senderType], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم إرسال الرسالة بنجاح',
            'data' => [
                'message_id' => $messageId,
                'attachments' => $savedAttachments,
            ],
        ]);
    }

    public function updateStatus(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بتغيير حالة التذكرة']);
            return;
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            $input = $_POST;
        }

        $providedToken = $input['_csrf_token'] ?? '';
        $status = $input['status'] ?? '';

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        if (!in_array($status, self::VALID_STATUSES)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'حالة غير صحيحة']);
            return;
        }

        $ticket = $this->ticketModel->findById($id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
            return;
        }

        $oldStatus = $ticket['status'];
        $this->ticketModel->updateTicket($id, ['status' => $status]);

        $this->logModel->logAction('ticket', $id, 'status_change', ['from' => $oldStatus, 'to' => $status], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث حالة التذكرة بنجاح',
        ]);
    }

    public function delete(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بحذف التذكرة']);
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

        $ticket = $this->ticketModel->findById($id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
            return;
        }

        $this->ticketModel->softDelete($id);

        $this->logModel->logAction('ticket', $id, 'delete', ['title' => $ticket['title']], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم حذف التذكرة بنجاح',
        ]);
    }

    public function serveAttachment(int $id, string $filename): void
    {
        $attachment = $this->attachmentModel->findById($id);
        if (!$attachment) {
            http_response_code(404);
            echo 'الملف غير موجود';
            return;
        }

        $ticket = $this->ticketModel->findById((int)$attachment['ticket_id']);
        if (!$ticket) {
            http_response_code(404);
            echo 'التذكرة غير موجودة';
            return;
        }

        if (!Auth::isAdmin() && (int)$ticket['created_by'] !== Auth::id()) {
            http_response_code(403);
            echo 'غير مصرح بالوصول لهذا الملف';
            return;
        }

        $filePath = ROOT_PATH . '/' . $attachment['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'الملف غير موجود';
            return;
        }

        header('Content-Type: ' . $attachment['mime_type']);
        header('Content-Disposition: inline; filename="' . $attachment['original_name'] . '"');
        header('Content-Length: ' . $attachment['file_size']);
        readfile($filePath);
    }

    public function unreadCount(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $count = $this->readModel->countUnreadForUser(Auth::id());
        echo json_encode(['success' => true, 'count' => $count]);
    }

    public function markRead(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $ticket = $this->ticketModel->findById($id);
        if (!$ticket) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'التذكرة غير موجودة']);
            return;
        }

        if (!Auth::isAdmin() && (int)$ticket['created_by'] !== Auth::id()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح']);
            return;
        }

        $this->readModel->upsert($id, Auth::id());
        echo json_encode(['success' => true]);
    }

    private function validateAttachments(array $attachments): array
    {
        $errors = [];

        if (empty($attachments) || empty($attachments['name'][0])) {
            return $errors;
        }

        $fileCount = count($attachments['name']);
        if ($fileCount > self::MAX_ATTACHMENTS) {
            $errors[] = 'الحد الأقصى ' . self::MAX_ATTACHMENTS . ' ملفات';
            return $errors;
        }

        for ($i = 0; $i < $fileCount; $i++) {
            if ($attachments['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            if ($attachments['size'][$i] > self::MAX_ATTACHMENT_SIZE) {
                $errors[] = 'الملف "' . $attachments['name'][$i] . '" يتجاوز الحد الأقصى (5 ميجابايت)';
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $attachments['tmp_name'][$i]);
            finfo_close($finfo);

            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
                $errors[] = 'نوع الملف "' . $attachments['name'][$i] . '" غير مدعوم';
            }
        }

        return $errors;
    }

    private function processAttachments(array $attachments, int $ticketId, ?int $messageId): array
    {
        $saved = [];

        if (empty($attachments) || empty($attachments['name'][0])) {
            return $saved;
        }

        $uploadDir = ROOT_PATH . '/storage/uploads/tickets/' . $ticketId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileCount = count($attachments['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($attachments['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $attachments['tmp_name'][$i]);
            finfo_close($finfo);

            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
                continue;
            }

            if ($attachments['size'][$i] > self::MAX_ATTACHMENT_SIZE) {
                continue;
            }

            $originalName = $attachments['name'][$i];
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $storedName = uniqid('att_', true) . '.' . $extension;
            $filePath = 'storage/uploads/tickets/' . $ticketId . '/' . $storedName;
            $fullPath = ROOT_PATH . '/' . $filePath;

            if (move_uploaded_file($attachments['tmp_name'][$i], $fullPath)) {
                $attachmentData = [
                    'ticket_id' => $ticketId,
                    'message_id' => $messageId,
                    'original_name' => $originalName,
                    'stored_name' => $storedName,
                    'file_path' => $filePath,
                    'file_size' => $attachments['size'][$i],
                    'mime_type' => $mimeType,
                ];

                $this->attachmentModel->create($attachmentData);
                $saved[] = $attachmentData;
            }
        }

        return $saved;
    }
}