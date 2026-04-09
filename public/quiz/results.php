<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;
use QuizArena\Models\GameSession;

Env::load(__DIR__ . '/../../.env');
Auth::start();
Auth::require();

$sessionId = filter_input(INPUT_GET, 'session', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$sessionId) {
    header('Location: /quiz/browse.php');
    exit;
}

$db      = Database::connect();
$session = GameSession::findById($db, $sessionId);

if (!$session) {
    header('Location: /quiz/browse.php');
    exit;
}

$user     = Auth::user();
$accuracy = $session['correct_count'] > 0 && $session['score'] > 0
    ? round(($session['correct_count'] / max(1, $session['correct_count'] + 1)) * 100)
    : 0;

// Pobierz szczegóły odpowiedzi dla tej sesji
$stmt = $db->prepare('
    SELECT
        q.content        AS question_text,
        q.correct_answer AS correct_index,
        ga.chosen_index,
        ga.is_correct,
        ga.time_spent,
        a_chosen.content AS chosen_text,
        a_correct.content AS correct_text
    FROM game_answers ga
    JOIN questions q ON q.id = ga.question_id
    LEFT JOIN answers a_chosen  ON a_chosen.question_id  = q.id AND a_chosen.index  = ga.chosen_index
    LEFT JOIN answers a_correct ON a_correct.question_id = q.id AND a_correct.index = q.correct_answer
    WHERE ga.session_id = :session_id
    ORDER BY q.order_index ASC
');
$stmt->execute(['session_id' => $sessionId]);
$answers = $stmt->fetchAll();

$totalQuestions = count($answers);
$correctCount   = (int) $session['correct_count'];
$accuracy       = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

// Wyznacz emoji trofeów na podstawie accuracy
$trophy = match(true) {
    $accuracy === 100 => ['🏆', 'Perfect!',    'var(--secondary)'],
    $accuracy >= 80   => ['🥇', 'Excellent!',  'var(--secondary)'],
    $accuracy >= 60   => ['🥈', 'Good Job!',   'var(--primary)'],
    $accuracy >= 40   => ['🥉', 'Not Bad',     'var(--primary)'],
    default           => ['💀', 'Keep Going',  'var(--tertiary)'],
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results — QuizArena</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/results.css">
</head>
<body>

<div class="ambient" aria-hidden="true">
    <div class="ambient__blob ambient__blob--purple"></div>
    <div class="ambient__blob ambient__blob--green"></div>
</div>

<nav class="navbar">
    <a href="/dashboard.php" class="nav-logo">Quiz<span>Arena</span></a>
    <div class="nav-links">
        <a href="/dashboard.php">Dashboard</a>
        <a href="/quiz/browse.php">Browse</a>
        <a href="/quiz/create.php">Create Quiz</a>
    </div>
    <div class="nav-right">
        <a href="/profile/index.php" class="nav-avatar">
            <?= strtoupper(substr($user['username'], 0, 2)) ?>
            <span class="nav-level">LVL <?= max(1, floor($user['xp'] / 200) + 1) ?></span>
        </a>
    </div>
</nav>

<main class="results-wrap">

    <!-- ── Hero score card ── -->
    <div class="score-hero">
        <div class="score-hero__trophy" style="--trophy-color: <?= $trophy[2] ?>">
            <?= $trophy[0] ?>
        </div>
        <h1 class="score-hero__label" style="color: <?= $trophy[2] ?>"><?= $trophy[1] ?></h1>
        <p class="score-hero__quiz"><?= htmlspecialchars($session['quiz_title']) ?></p>

        <div class="score-hero__value">
            <span class="score-hero__number" id="animated-score" data-target="<?= $session['score'] ?>">0</span>
            <span class="score-hero__pts">pts</span>
        </div>
    </div>

    <!-- ── Stats row ── -->
    <div class="result-stats">
        <div class="result-stat">
            <span class="result-stat__icon">✅</span>
            <span class="result-stat__value"><?= $correctCount ?>/<?= $totalQuestions ?></span>
            <span class="result-stat__label">Correct</span>
        </div>
        <div class="result-stat result-stat--accent">
            <span class="result-stat__icon">🎯</span>
            <span class="result-stat__value"><?= $accuracy ?>%</span>
            <span class="result-stat__label">Accuracy</span>
        </div>
        <div class="result-stat">
            <span class="result-stat__icon">⏱</span>
            <span class="result-stat__value"><?= $session['time_taken'] ?>s</span>
            <span class="result-stat__label">Time</span>
        </div>
    </div>

    <!-- ── XP earned banner ── -->
    <div class="xp-banner">
        <span class="xp-banner__label">XP EARNED</span>
        <span class="xp-banner__value">+<?= $session['score'] ?> XP</span>
    </div>

    <!-- ── Answer breakdown ── -->
    <section class="breakdown">
        <h2 class="breakdown__title">Answer Breakdown</h2>

        <?php foreach ($answers as $i => $ans): ?>
            <div class="breakdown-row <?= $ans['is_correct'] ? 'is-correct' : 'is-wrong' ?>">
                <div class="breakdown-row__num"><?= $i + 1 ?></div>
                <div class="breakdown-row__body">
                    <p class="breakdown-row__question">
                        <?= htmlspecialchars($ans['question_text']) ?>
                    </p>
                    <div class="breakdown-row__answers">
                        <?php if ($ans['is_correct']): ?>
                            <span class="answer-tag answer-tag--correct">
                                ✓ <?= htmlspecialchars($ans['chosen_text'] ?? '—') ?>
                            </span>
                        <?php else: ?>
                            <span class="answer-tag answer-tag--wrong">
                                ✗ <?= htmlspecialchars($ans['chosen_text'] ?? 'No answer') ?>
                            </span>
                            <span class="answer-tag answer-tag--correct">
                                ✓ <?= htmlspecialchars($ans['correct_text'] ?? '—') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="breakdown-row__time"><?= $ans['time_spent'] ?>s</div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- ── Actions ── -->
    <div class="results-actions">
        <a href="/quiz/play.php?id=<?= htmlspecialchars($session['quiz_id']) ?>" class="btn-replay">
            ↺ Play Again
        </a>
        <a href="/quiz/browse.php" class="btn-browse">
            Browse Quizzes
        </a>
        <a href="/dashboard.php" class="btn-dash">
            Dashboard
        </a>
    </div>

</main>

<script>
// Animate score counting up
(function () {
    const el     = document.getElementById('animated-score');
    const target = parseInt(el.dataset.target, 10);
    if (!target) return;
    const duration = 1200;
    const start    = performance.now();
    function step(now) {
        const p = Math.min((now - start) / duration, 1);
        // ease-out cubic
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(eased * target);
        if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
})();
</script>
</body>
</html>