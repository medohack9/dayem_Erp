<?php

namespace App\Core;

class Session
{
    private int $timeout;

    public function __construct(int $timeout = 1800)
    {
        $this->timeout = $timeout;

        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Lax');

        session_start();

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $this->timeout) {
            session_unset();
            session_destroy();
            session_start();
        }

        $_SESSION['last_activity'] = time();

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    public function getCsrfToken(): string
    {
        return $_SESSION['csrf_token'];
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }
}