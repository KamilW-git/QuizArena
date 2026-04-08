<?php

require_once __DIR__ . '/../vendor/autoload.php';

use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../.env');
Auth::start();
Auth::require();

$user = Auth::user();

// XP do następnego poziomu (co 200 XP = 1 level)
$level    = max(1, floor($user['xp'] / 200) + 1);
$xpInLevel   = $user['xp'] % 200;
$xpPercent   = round(($xpInLevel / 200) * 100);
$initials = strtoupper(substr($user['username'], 0, 2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — QuizArena</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>

<nav class="navbar">
    <a href="/dashboard.php" class="nav-logo">Quiz<span>Arena</span></a>
    <div class="nav-links">
        <a href="/dashboard.php" class="active">Dashboard</a>
        <a href="/quiz/create.php">Create Quiz</a>
        <a href="/leaderboard/index.php">Leaderboard</a>
    </div>
    <div class="nav-right">
        <a href="#" class="nav-bell">🔔</a>
        <a href="/profile/index.php" class="nav-avatar">
            <?= $initials ?>
            <span class="nav-level">LVL <?= $level ?></span>
        </a>
    </div>
</nav>

<div class="container">

    <!-- Hero -->
    <div class="hero">
        <div class="hero-left">
            <div class="hero-avatar">🎮</div>
            <div>
                <div class="hero-greeting">Welcome back,</div>
                <div class="hero-name"><?= htmlspecialchars($user['username']) ?></div>
                <div class="hero-rank">
                    <span>Level <?= $level ?> • <?= $user['xp'] ?> XP total</span>
                </div>
            </div>
        </div>
        <div class="hero-xp">
            <div class="hero-xp-label">XP PROGRESSION</div>
            <div class="hero-xp-values">
                <strong><?= $xpInLevel ?></strong> / 200 XP
            </div>
            <div class="xp-bar">
                <div class="xp-bar-fill" style="width: <?= $xpPercent ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card games">
            <div class="stat-info">
                <div class="stat-label">Games Played</div>
                <div class="stat-value">0</div>
            </div>
            <div class="stat-icon">🎮</div>
        </div>
        <div class="stat-card wins">
            <div class="stat-info">
                <div class="stat-label">Wins</div>
                <div class="stat-value">0</div>
            </div>
            <div class="stat-icon">🏆</div>
        </div>
        <div class="stat-card acc">
            <div class="stat-info">
                <div class="stat-label">Accuracy</div>
                <div class="stat-value">0<sup>%</sup></div>
            </div>
            <div class="stat-icon">🎯</div>
        </div>
    </div>

    <!-- CTA -->
    <div class="cta-row">
        <a href="/quiz/browse.php" class="btn-start">▶ START NEW GAME</a>
        <a href="/quiz/create.php" class="btn-create">+ CREATE QUIZ</a>
    </div>

    <!-- Recent Games -->
    <div class="section-header">
        <div class="section-title">Recent Games</div>
        <a href="#" class="section-link">VIEW ALL HISTORY</a>
    </div>

    <div class="empty-state">
        <p>No games yet. Play your first quiz! 🎯</p>
        <a href="/quiz/browse.php" class="btn-primary">Browse Quizzes</a>
    </div>

</div>

<!-- Footer -->
<footer>
    <div class="footer-brand">
        <div class="auth-logo" style="font-size:1.1rem">Quiz<span style="color:#7C4DFF">Arena</span></div>
        <p>© 2026 QuizArena.<br>The Neon Arena Awaits.</p>
    </div>
    <div class="footer-col">
        <h4>Explore</h4>
        <a href="/quiz/browse.php">Browse Quizzes</a>
        <a href="/leaderboard/index.php">Leaderboard</a>
        <a href="/quiz/create.php">Create Quiz</a>
    </div>
    <div class="footer-col">
        <h4>Legal</h4>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
    <div class="footer-col">
        <h4>Stay Connected</h4>
        <div class="footer-social">
            <a href="#" class="social-btn">⬡</a>
            <a href="#" class="social-btn">✉</a>
        </div>
    </div>
</footer>

<a href="/quiz/browse.php" class="fab">+</a>

</body>
</html>