<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;
use QuizArena\Models\Quiz;
use QuizArena\Config\Database;

Env::load(__DIR__ . '/../../.env');
Auth::require();

$quizId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$quizId) {
    header('Location: /quiz/browse.php');
    exit;
}

$db   = Database::connect();
$quiz = Quiz::findById($db, $quizId);

if (!$quiz) {
    header('Location: /quiz/browse.php');
    exit;
}

$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['title']) ?> — QuizArena</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/play.css">
</head>
<body>

<div class="ambient" aria-hidden="true">
    <div class="ambient__blob ambient__blob--purple"></div>
    <div class="ambient__blob ambient__blob--green"></div>
</div>

<main class="game-wrap">

    <!-- LOADING -->
    <div class="game-state" id="state-loading">
        <div class="loader">
            <div class="loader__ring"></div>
            <p class="loader__text">Loading quiz...</p>
        </div>
    </div>

    <!-- PLAYING -->
    <div class="game-state is-hidden" id="state-playing">

        <!-- Desktop: progress + maly timer w headerze -->
        <header class="game-header">
            <div class="game-progress">
                <span class="game-progress__label">
                    Question <span id="q-current">1</span> of <span id="q-total">?</span>
                </span>
                <div class="game-progress__bar">
                    <div class="game-progress__fill" id="progress-fill"></div>
                </div>
            </div>
            <div class="game-timer" id="timer-wrap">
                <!-- viewBox 180x180, r=80, circumference=502.7 -->
                <svg class="timer__ring" viewBox="0 0 180 180" aria-hidden="true">
                    <circle class="timer__track" cx="90" cy="90" r="80"/>
                    <circle class="timer__arc"   cx="90" cy="90" r="80" id="timer-arc"/>
                </svg>
                <span class="timer__count" id="timer-count">20</span>
            </div>
        </header>

        <!-- Pytanie -->
        <div class="question-card" id="question-card">
            <p class="question-card__category" id="q-category">Category</p>
            <h2 class="question-card__text" id="q-text">Loading...</h2>
        </div>

        <!-- Odpowiedzi -->
        <div class="answers-grid" id="answers-grid" role="list"></div>

        <!-- Score strip: desktop=inline center, mobile=sticky footer -->
        <div class="score-strip">
            <div class="score-strip__left">
                <span class="score-strip__q-label">CURRENT PROGRESS</span>
                <div class="score-strip__q-row">
                    <span class="score-strip__q-num">
                        <span id="footer-current">01</span>
                        <span class="score-strip__q-sep"> / </span>
                        <span id="footer-total">10</span>
                    </span>
                </div>
                <div class="score-strip__bar">
                    <div class="score-strip__bar-fill" id="progress-fill-footer"></div>
                </div>
            </div>
            <div class="score-strip__right">
                <span class="score-strip__label">SCORE</span>
                <span class="score-strip__value" id="live-score">0</span>
            </div>
        </div>

    </div>

    <!-- FINISHED -->
    <div class="game-state is-hidden" id="state-finished">
        <div class="finish-card">
            <div class="finish-card__icon">🏆</div>
            <h2 class="finish-card__title">Quiz Complete!</h2>
            <p class="finish-card__sub">Calculating your XP...</p>
            <div class="finish-card__spinner"></div>
        </div>
    </div>

</main>

<script>
    window.QUIZ_ID    = <?= json_encode($quizId) ?>;
    window.USER_ID    = <?= (int) $user['id'] ?>;
    window.QUIZ_TITLE = <?= json_encode($quiz['title']) ?>;
</script>
<script src="/assets/js/game.js"></script>
</body>
</html>