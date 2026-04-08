<?php

namespace QuizArena\Controllers;

use PDO;
use QuizArena\Models\User;
use QuizArena\Helpers\Auth;

class AuthController
{
    private User $user;

    public function __construct(PDO $db)
    {
        $this->user = new User($db);
    }

    // Rejestracja
    public function register(array $data): array
    {
        $errors = $this->validateRegister($data);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Sprawdź czy email lub username już istnieje
        if ($this->user->findByEmail($data['email'])) {
            return ['success' => false, 'errors' => ['email' => 'Email already in use']];
        }

        if ($this->user->findByUsername($data['username'])) {
            return ['success' => false, 'errors' => ['username' => 'Username already taken']];
        }

        // Utwórz użytkownika i zaloguj go od razu
        $newUser = $this->user->create(
            $data['username'],
            $data['email'],
            $data['password']
        );

        Auth::login($newUser);

        return ['success' => true];
    }

    // Logowanie
    public function login(array $data): array
    {
        $errors = $this->validateLogin($data);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $user = $this->user->findByEmail($data['email']);

        if (!$user || !$this->user->verifyPassword($data['password'], $user['password_hash'])) {
            return ['success' => false, 'errors' => ['general' => 'Invalid email or password']];
        }

        Auth::login($user);

        return ['success' => true];
    }

    // Walidacja rejestracji
    private function validateRegister(array $data): array
    {
        $errors = [];

        if (empty($data['username']) || strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if ($data['password'] !== ($data['password_confirm'] ?? '')) {
            $errors['password_confirm'] = 'Passwords do not match';
        }

        return $errors;
    }

    // Walidacja logowania
    private function validateLogin(array $data): array
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        }

        return $errors;
    }
}