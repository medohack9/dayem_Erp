<?php

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
        ];
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION['user_role'] ?? null) === 'admin';
    }

    public static function isEmployee(): bool
    {
        return ($_SESSION['user_role'] ?? null) === 'employee';
    }

    public static function isActive(): bool
    {
        return ($_SESSION['user_status'] ?? 'active') === 'active';
    }

    public static function status(): ?string
    {
        return $_SESSION['user_status'] ?? null;
    }

    public static function login(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_status'] = $user['status'] ?? 'active';
        $_SESSION['must_change_password'] = !empty($user['must_change_password']) ? true : false;
        $_SESSION['last_activity'] = time();
    }

    public static function mustChangePassword(): bool
    {
        return !empty($_SESSION['must_change_password']);
    }

    public static function clearMustChangePassword(): void
    {
        $_SESSION['must_change_password'] = false;
    }

    public static function logout(): void
    {
        session_unset();
        session_destroy();
    }
}