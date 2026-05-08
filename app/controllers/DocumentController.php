<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\DocumentModel;
use App\Models\LogModel;

class DocumentController extends Controller
{
    protected DocumentModel $documentModel;
    protected LogModel $logModel;

    private const ALLOWED_MIME_TYPES = [
        'profile_photo' => ['image/jpeg', 'image/png', 'image/webp'],
        'contract' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'national_id' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
        'other' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/webp'],
    ];

    private const MAX_FILE_SIZE = 5242880;

    private const UPLOAD_DIR = __DIR__ . '/../../storage/uploads/employees/';

    public function __construct()
    {
        $this->documentModel = new DocumentModel();
        $this->logModel = new LogModel();
    }

    public function upload(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بهذا الإجراء']);
            return;
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = $_POST['_csrf_token'] ?? '';
        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $docType = $_POST['doc_type'] ?? '';

        if (empty($userId)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'معرف الموظف مطلوب']);
            return;
        }

        $validTypes = ['profile_photo', 'contract', 'national_id', 'other'];
        if (!in_array($docType, $validTypes)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'نوع المستند غير صحيح']);
            return;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = $this->getUploadErrorMessage($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE);
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => $errorMsg]);
            return;
        }

        $file = $_FILES['file'];

        if ($file['size'] > self::MAX_FILE_SIZE) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'حجم الملف يجب أن لا يتجاوز 5 ميجابايت']);
            return;
        }

        $allowedMimes = self::ALLOWED_MIME_TYPES[$docType] ?? [];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'نوع الملف غير مسموح به']);
            return;
        }

        if ($docType === 'profile_photo') {
            $existing = $this->documentModel->findByUserAndType($userId, 'profile_photo');
            if ($existing) {
                $this->deleteFile($existing['file_path']);
                $this->documentModel->deleteById($existing['id']);
            }
        }

        $originalName = basename($file['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $storedName = $docType . '_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

        $userDir = self::UPLOAD_DIR . $userId;
        if (!is_dir($userDir)) {
            mkdir($userDir, 0755, true);
        }

        $filePath = $userDir . '/' . $storedName;
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'فشل في رفع الملف']);
            return;
        }

        $relativePath = $userId . '/' . $storedName;

        $data = [
            'user_id' => $userId,
            'doc_type' => $docType,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'file_path' => $relativePath,
            'file_size' => $file['size'],
            'mime_type' => $mimeType,
        ];

        $id = $this->documentModel->create($data);

        $this->logModel->logAction('employee', $userId, 'document_upload', ['doc_type' => $docType, 'original_name' => $originalName], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم رفع المستند بنجاح',
            'document' => [
                'id' => $id,
                'doc_type' => $docType,
                'original_name' => $originalName,
                'file_size' => $file['size'],
            ],
        ]);
    }

    public function delete(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح بهذا الإجراء']);
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

        $id = (int)($input['id'] ?? 0);
        $document = $this->documentModel->findById($id);

        if (!$document) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'المستند غير موجود']);
            return;
        }

        $this->deleteFile($document['file_path']);
        $this->documentModel->deleteById($id);

        $this->logModel->logAction('employee', (int)$document['user_id'], 'document_delete', ['doc_type' => $document['doc_type'], 'original_name' => $document['original_name']], null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode(['success' => true, 'message' => 'تم حذف المستند بنجاح']);
    }

    public function serve(int $id, string $filename = ''): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo 'غير مصرح';
            return;
        }

        $document = $this->documentModel->findById($id);
        if (!$document) {
            http_response_code(404);
            echo 'المستند غير موجود';
            return;
        }

        if (Auth::isEmployee() && (int)$document['user_id'] !== Auth::id()) {
            http_response_code(403);
            echo 'غير مصرح بالوصول لهذا المستند';
            return;
        }

        $filePath = self::UPLOAD_DIR . $document['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'الملف غير موجود';
            return;
        }

        header('Content-Type: ' . $document['mime_type']);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: inline; filename="' . $document['original_name'] . '"');
        readfile($filePath);
        exit;
    }

    public function list(int $userId): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول']);
            return;
        }

        if (Auth::isEmployee() && Auth::id() !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'غير مصرح']);
            return;
        }

        $documents = $this->documentModel->findByUserId($userId);

        $result = array_map(function ($doc) {
            return [
                'id' => $doc['id'],
                'doc_type' => $doc['doc_type'],
                'original_name' => $doc['original_name'],
                'file_size' => $doc['file_size'],
                'mime_type' => $doc['mime_type'],
                'created_at' => $doc['created_at'],
                'url' => url('/documents/' . $doc['id'] . '/' . $doc['stored_name']),
            ];
        }, $documents);

        echo json_encode(['success' => true, 'documents' => $result]);
    }

    private function deleteFile(string $relativePath): void
    {
        $fullPath = self::UPLOAD_DIR . $relativePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'حجم الملف كبير جداً',
            UPLOAD_ERR_PARTIAL => 'تم رفع الملف بشكل جزئي',
            UPLOAD_ERR_NO_FILE => 'لم يتم اختيار ملف',
            default => 'حدث خطأ أثناء رفع الملف',
        };
    }
}