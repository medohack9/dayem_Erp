<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    public static function check(array $routeConfig): bool
    {
        if (!isset($routeConfig['auth']) || $routeConfig['auth'] === false) {
            return true;
        }

        if (!Auth::check()) {
            if (self::isAjaxRequest()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']);
                exit;
            }
            header('Location: ' . url('/auth/login'));
            exit;
        }

        if (isset($routeConfig['roles']) && is_array($routeConfig['roles'])) {
            if (!in_array(Auth::role(), $routeConfig['roles'])) {
                http_response_code(403);
                $pageTitle = 'غير مصرح';
                $activePage = '';
                ob_start();
                require ROOT_PATH . '/app/views/errors/403.php';
                $content = ob_get_clean();
                require ROOT_PATH . '/app/views/layouts/main.php';
                exit;
            }
        }

        self::validateSession();

        if (Auth::mustChangePassword()) {
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $basePath = $GLOBALS['app_config']['base_path'] ?? '';
            if ($basePath && str_starts_with($currentPath, $basePath)) {
                $currentPath = substr($currentPath, strlen($basePath));
            }
            $currentPath = rtrim($currentPath, '/') ?: '/';

            $allowedPaths = ['/auth/change-password', '/auth/change-password/post'];
            $normalizedPath = rtrim($currentPath, '/') ?: '/';

            $isAllowed = false;
            foreach ($allowedPaths as $allowed) {
                if ($normalizedPath === $allowed) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                if (self::isAjaxRequest()) {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'يجب تغيير كلمة المرور أولاً', 'must_change_password' => true]);
                    exit;
                }
                header('Location: ' . url('/auth/change-password'));
                exit;
            }
        }

        return true;
    }

    public static function validateSession(): void
    {
        if (!Auth::check()) {
            return;
        }

        $sessionModel = new \App\Models\UserSessionModel();
        $session = $sessionModel->findBySessionId(session_id());

        if (!$session) {
            Auth::logout();
            $_SESSION['flash_message'] = 'تم تسجيل دخولك من جهاز آخر';
            $_SESSION['flash_type'] = 'error';
            header('Location: ' . url('/auth/login'));
            exit;
        }

        $sessionModel->updateActivity(session_id());
    }

    private static function isAjaxRequest(): bool
    {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            !empty($_SERVER['CONTENT_TYPE']) &&
            strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
        );
    }
}