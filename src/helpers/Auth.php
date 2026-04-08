<?php

namespace QuizArena\Helpers;

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Sprawdź czy użytkownik jest zalogowany
    public static function check(): bool
    {
        self::start();
        return isset($_SESSION['user_id']);
    }

    // Zwróć dane zalogowanego użytkownika
    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    // Zaloguj użytkownika (zapisz w sesji)
    public static function login(array $user): void
    {
        self::start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user']    = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'xp'       => $user['xp'],
        ];
    }

    // Wyloguj
    public static function logout(): void
    {
        self::start();
        session_destroy();
    }

    // Przekieruj na login jeśli niezalogowany
    public static function require(): void
    {
        if (!self::check()) {
            header('Location: /login.php');
            exit;
        }
    }

    // Przekieruj na dashboard jeśli już zalogowany
    public static function guest(): void
    {
        if (self::check()) {
            header('Location: /dashboard.php');
            exit;
        }
    }
}