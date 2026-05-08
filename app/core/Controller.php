<?php

namespace App\Core;

use App\Core\Auth;

class Controller
{
    protected string $csrfToken;

    public function render(string $view, array $data = []): void
    {
        $pageTitle = $data['pageTitle'] ?? 'Dayem ERP';
        $activePage = $data['activePage'] ?? '';
        $pageScripts = $data['pageScripts'] ?? [];
        extract($data);

        ob_start();
        require ROOT_PATH . "/app/views/{$view}.php";
        $content = ob_get_clean();

        require ROOT_PATH . '/app/views/layouts/main.php';
    }

    public function renderWithoutLayout(string $view, array $data = []): void
    {
        extract($data);
        require ROOT_PATH . "/app/views/{$view}.php";
    }

    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    public function getCsrfToken(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    public function validateCsrf(): void
    {
        $token = $_POST['_csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
            http_response_code(403);
            $this->render('errors/403', [
                'pageTitle' => 'طلب غير مصرح به',
                'activePage' => '',
            ]);
            exit;
        }
    }

    public function requireAuth(): void
    {
        if (!Auth::check()) {
            header('Location: ' . url('/auth/login'));
            exit;
        }
    }

    public function requireRole(string ...$roles): void
    {
        if (!in_array(Auth::role(), $roles)) {
            http_response_code(403);
            $this->render('errors/403', [
                'pageTitle' => 'غير مصرح',
                'activePage' => '',
            ]);
            exit;
        }
    }
}