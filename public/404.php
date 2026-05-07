<?php
require_once __DIR__ . '/../vendor/autoload.php';

use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../.env');
Auth::start();

$isLoggedIn = Auth::check();
$homeLink = $isLoggedIn ? '/dashboard.php' : '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found — QuizArena</title>
    <link rel="stylesheet" href="/assets/css/main.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--tertiary), var(--primary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            text-shadow: 0 0 40px rgba(255, 0, 212, 0.4);
        }
        @media (min-width: 768px) {
            .error-code {
                font-size: 12rem;
            }
        }
        .error-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--text);
        }
        .error-msg {
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            max-width: 450px;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .error-bg-blur {
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(124, 77, 255, 0.15);
            filter: blur(100px);
            border-radius: 50%;
            z-index: -1;
        }
        .error-bg-blur-2 {
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 0, 212, 0.1);
            filter: blur(80px);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-bg-blur"></div>
        <div class="error-bg-blur-2"></div>
        
        <a href="<?= htmlspecialchars($homeLink) ?>" class="nav-logo" style="margin-bottom: 3rem;">QuizArena</a>
        
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Lost in the Arena</h2>
        <p class="error-msg">The page you are looking for has been defeated or never existed. Return to the safe zone before the time runs out.</p>
        
        <a href="<?= htmlspecialchars($homeLink) ?>" class="btn-primary">
            <span class="material-symbols-outlined">home</span>
            Return to Base
        </a>
    </div>
</body>
</html>
