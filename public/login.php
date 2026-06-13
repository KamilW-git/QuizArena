<?php

require_once __DIR__ . '/../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Controllers\AuthController;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../.env');
Auth::start();
Auth::guest();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Nieprawidłowy token zabezpieczający. Spróbuj ponownie.';
    } else {
        $db         = Database::connect();
        $controller = new AuthController($db);
        $result     = $controller->login($_POST);

        if ($result['success']) {
            header('Location: /dashboard.php');
            exit;
        }

        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php require __DIR__ . '/includes/theme-head.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — QuizArena</title>
    <link rel="stylesheet" href="/assets/css/main.css?v=2">
</head>
<body>
    <div class="auth-theme-wrap">
        <?php require __DIR__ . '/includes/theme-toggle.php'; ?>
    </div>
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-logo">QuizArena</h1>
            <h2>Welcome back</h2>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::generateCsrfToken()) ?>">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                    <?php if (!empty($errors['email'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                    <?php if (!empty($errors['password'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-primary">Sign in</button>
            </form>

            <p class="auth-link">Don't have an account? <a href="/register.php">Register</a></p>
        </div>
    </div>
<script src="/assets/js/theme.js"></script>
</body>
</html>