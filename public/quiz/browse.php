<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Controllers\QuizController;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../../.env');
Auth::start();
Auth::require();

$user       = Auth::user();
$db         = Database::connect();
$controller = new QuizController($db);

$category   = $_GET['category'] ?? null;
$quizzes    = $controller->getAll($category);
$categories = $controller->getCategories();

$difficultyLabel = ['', '⭐ Easy', '⭐⭐ Medium', '⭐⭐⭐ Hard'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Quizzes — QuizArena</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/browse.css">
</head>
<body>

<nav class="navbar">
    <a href="/dashboard.php" class="nav-logo">Quiz<span>Arena</span></a>
    <div class="nav-links">
        <a href="/dashboard.php">Dashboard</a>
        <a href="/quiz/create.php">Create Quiz</a>
        <a href="/leaderboard/index.php">Leaderboard</a>
    </div>
    <div class="nav-right">
        <a href="#" class="nav-bell">🔔</a>
        <a href="/profile/index.php" class="nav-avatar">
            <?= strtoupper(substr($user['username'], 0, 2)) ?>
            <span class="nav-level">LVL <?= max(1, floor($user['xp'] / 200) + 1) ?></span>
        </a>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1>Browse Quizzes</h1>
        <p>Pick a quiz and start playing</p>
    </div>

    <!-- Filters -->
    <div class="filters">
        <a href="/quiz/browse.php" class="filter-tag <?= !$category ? 'active' : '' ?>">
            All
        </a>
        <?php foreach ($categories as $cat): ?>
                href="/quiz/browse.php?category=<?= urlencode($cat) ?>"
                class="filter-tag <?= $category === $cat ? 'active' : '' ?>"
            >
                <?= htmlspecialchars($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Quiz grid -->
    <?php if (empty($quizzes)): ?>
        <div class="empty-state">
            <p>No quizzes yet. Be the first to create one! 🎯</p>
            <a href="/quiz/create.php" class="btn-primary">Create Quiz</a>
        </div>
    <?php else: ?>
        <div class="quiz-grid">
            <?php foreach ($quizzes as $quiz): ?>
                <a href="/quiz/play.php?id=<?= $quiz['id'] ?>" class="quiz-card">
                    <div class="quiz-card-thumb">
                        <span class="quiz-card-icon">🧠</span>
                        <span class="quiz-card-category"><?= htmlspecialchars($quiz['category']) ?></span>
                    </div>
                    <div class="quiz-card-body">
                        <div class="quiz-card-title"><?= htmlspecialchars($quiz['title']) ?></div>
                        <div class="quiz-card-meta">
                            <span><?= $quiz['question_count'] ?> questions</span>
                            <span><?= $difficultyLabel[$quiz['difficulty']] ?></span>
                        </div>
                        <div class="quiz-card-footer">
                            <span class="quiz-card-author">by <?= htmlspecialchars($quiz['username']) ?></span>
                            <span class="quiz-card-play">Play ▶</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>