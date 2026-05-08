<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\UserModel;
use App\Models\AuthLogModel;
use App\Models\UserSessionModel;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect(url('/'));
            return;
        }

        $flashMessage = '';
        $flashType = '';
        if (isset($_SESSION['flash_message'])) {
            $flashMessage = $_SESSION['flash_message'];
            $flashType = $_SESSION['flash_type'] ?? 'info';
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        }

        $pageTitle = 'تسجيل الدخول';
        $activePage = 'auth';
        $csrfToken = $_SESSION['csrf_token'] ?? '';

        ob_start();
        require ROOT_PATH . '/app/views/auth/login.php';
        $content = ob_get_clean();

        require ROOT_PATH . '/app/views/layouts/login.php';
    }

    public function login(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $csrfToken = $_SESSION['csrf_token'] ?? '';
        $providedToken = '';

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            $identifier = trim($input['identifier'] ?? '');
            $password = $input['password'] ?? '';
            $providedToken = $input['_csrf_token'] ?? '';
        } else {
            $identifier = trim($_POST['identifier'] ?? '');
            $password = $_POST['password'] ?? '';
            $providedToken = $_POST['_csrf_token'] ?? '';
        }

        if (empty($providedToken) || empty($csrfToken) || !hash_equals($csrfToken, $providedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'طلب غير مصرح به']);
            return;
        }

        $errors = [];
        if (empty($identifier)) {
            $errors['identifier'] = 'هذا الحقل مطلوب';
        }
        if (empty($password)) {
            $errors['password'] = 'كلمة المرور مطلوبة';
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        $userModel = new UserModel();
        $authLogModel = new AuthLogModel();
        $logData = [
            'identifier' => $identifier,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ];

        $config = require ROOT_PATH . '/config/app.php';
        $maxAttempts = (int)($config['lockout_attempts'] ?? 5);
        $lockoutWindow = (int)($config['lockout_duration'] ?? 15);
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

        if ($authLogModel->countRecentFailedByIdentifier($identifier, $clientIp, $lockoutWindow) >= $maxAttempts) {
            $authLogModel->logAttempt(array_merge($logData, [
                'user_id' => null,
                'outcome' => 'locked',
                'failure_reason' => 'rate_limited',
            ]));
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'عدد محاولات كثيرة. حاول مرة أخرى بعد ' . $lockoutWindow . ' دقيقة']);
            return;
        }

        $user = $userModel->findByIdentifier($identifier);

        if (!$user) {
            $authLogModel->logAttempt(array_merge($logData, [
                'user_id' => null,
                'outcome' => 'failed',
                'failure_reason' => 'invalid_credentials',
            ]));
            echo json_encode(['success' => false, 'message' => 'بيانات الدخول غير صحيحة']);
            return;
        }

        if ($user['role'] !== 'admin' && $userModel->isLocked($user)) {
            $authLogModel->logAttempt(array_merge($logData, [
                'user_id' => $user['id'],
                'outcome' => 'locked',
                'failure_reason' => 'account_locked',
            ]));
            echo json_encode(['success' => false, 'message' => 'الحساب مقفل مؤقتاً. حاول مرة أخرى بعد 15 دقيقة']);
            return;
        }

        if ($user['status'] !== 'active') {
            $failureReason = $user['status'] === 'suspended' ? 'account_suspended' : 'account_terminated';
            $message = $user['status'] === 'suspended' ? 'حسابك موقوف. تواصل مع الإدارة.' : 'حسابك منتهي. تواصل مع الإدارة.';
            $authLogModel->logAttempt(array_merge($logData, [
                'user_id' => $user['id'],
                'outcome' => 'failed',
                'failure_reason' => $failureReason,
            ]));
            echo json_encode(['success' => false, 'message' => $message]);
            return;
        }

        if (!password_verify($password, $user['password'])) {
            if ($user['role'] !== 'admin') {
                $userModel->incrementFailedAttempts($user['id']);
            }
            $authLogModel->logAttempt(array_merge($logData, [
                'user_id' => $user['id'],
                'outcome' => 'failed',
                'failure_reason' => 'invalid_credentials',
            ]));
            echo json_encode(['success' => false, 'message' => 'بيانات الدخول غير صحيحة']);
            return;
        }

        $userModel->resetFailedAttempts($user['id']);

        $authLogModel->clearFailedByIdentifier($identifier, $clientIp);

        session_regenerate_id(true);
        Auth::login($user);

        $sessionModel = new UserSessionModel();
        $sessionModel->createSession($user['id'], session_id(), $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');

        $authLogModel->logAttempt(array_merge($logData, [
            'user_id' => $user['id'],
            'outcome' => 'success',
            'failure_reason' => null,
        ]));

        echo json_encode([
            'success' => true,
            'redirect' => !empty($user['must_change_password']) ? url('/auth/change-password') : url('/'),
            'user' => [
                'name' => $user['name'],
                'role' => $user['role'],
            ],
        ]);
    }

    public function logout(): void
    {
        if (Auth::check()) {
            $sessionModel = new UserSessionModel();
            $sessionModel->deleteBySessionId(session_id());
        }

        Auth::logout();

        session_start();
        $_SESSION['flash_message'] = 'تم تسجيل الخروج بنجاح';
        $_SESSION['flash_type'] = 'success';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->redirect(url('/auth/login'));
    }
}