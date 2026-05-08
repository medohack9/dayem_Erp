<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\UserModel;
use App\Models\LogModel;

class PasswordController extends Controller
{
    public function changeForm(): void
    {
        if (!Auth::check() || !Auth::mustChangePassword()) {
            $this->redirect(url('/'));
            return;
        }

        $pageTitle = 'تغيير كلمة المرور';
        $activePage = '';
        $csrfToken = $_SESSION['csrf_token'] ?? '';

        ob_start();
        require ROOT_PATH . '/app/views/auth/change-password.php';
        $content = ob_get_clean();

        require ROOT_PATH . '/app/views/layouts/login.php';
    }

    public function change(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']);
            return;
        }

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = '';

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';
            $confirmPassword = $input['confirm_password'] ?? '';
            $providedToken = $input['_csrf_token'] ?? '';
        } else {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $providedToken = $_POST['_csrf_token'] ?? '';
        }

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $errors = [];

        if (empty($currentPassword)) {
            $errors['current_password'] = 'كلمة المرور الحالية مطلوبة';
        }

        if (empty($newPassword)) {
            $errors['new_password'] = 'كلمة المرور الجديدة مطلوبة';
        } elseif (mb_strlen($newPassword) < 8) {
            $errors['new_password'] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        }

        if (empty($confirmPassword)) {
            $errors['confirm_password'] = 'تأكيد كلمة المرور مطلوب';
        } elseif ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'كلمة المرور وتأكيدها غير متطابقين';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $userModel = new UserModel();
        $user = $userModel->findById(Auth::id());

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => ['current_password' => 'كلمة المرور الحالية غير صحيحة']]);
            return;
        }

        $userModel->updateUser(Auth::id(), [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
            'must_change_password' => 0,
        ]);

        Auth::clearMustChangePassword();

        $logModel = new LogModel();
        $logModel->logAction('user', Auth::id(), 'password_change', null, null, $_SERVER['REMOTE_ADDR'] ?? null);

        echo json_encode([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح',
            'redirect' => url('/'),
        ]);
    }
}