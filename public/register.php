<?php

require_once __DIR__ . '/../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Controllers\AuthController;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../.env');

Auth::start();
Auth::guest();// jeśli już zalogowany → przekieruj na dashboard

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db         = Database::connect();
    $controller = new AuthController($db);
    $result     = $controller->register($_POST);

    if ($result['success']) {
        header('Location: /dashboard.php');
        exit;
    }

    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — QuizArena</title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-logo">Quiz<span>Arena</span></h1>
            <h2>Create account</h2>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="off"
                    >
                    <?php if (!empty($errors['username'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['username']) ?></span>
                    <?php endif; ?>
                </div>

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

                <div class="form-group">
                    <label for="password_confirm">Confirm password</label>
                    <input type="password" id="password_confirm" name="password_confirm">
                    <?php if (!empty($errors['password_confirm'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['password_confirm']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-primary">Create account</button>
            </form>

            <p class="auth-link">Already have an account? <a href="/login.php">Sign in</a></p>
        </div>
    </div>
</body>
</html>