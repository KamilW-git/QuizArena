<?php

require_once __DIR__ . '/../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../.env');
Auth::start();
Auth::require();

$user = Auth::user();
$db   = Database::connect();

// ── Statystyki gracza ──────────────────────────────────────────────────────
$stmt = $db->prepare('
    SELECT
        COUNT(*)                                        AS games_played,
        COALESCE(AVG(
            CASE WHEN correct_count + (
                SELECT COUNT(*) FROM game_answers ga
                WHERE ga.session_id = gs.id
            ) - correct_count > 0
            THEN correct_count::float / NULLIF((
                SELECT COUNT(*) FROM game_answers ga
                WHERE ga.session_id = gs.id
            ), 0) * 100
            ELSE 0 END
        ), 0)                                           AS avg_accuracy,
        COALESCE(AVG(correct_count), 0)                AS avg_correct,
        COALESCE(SUM(score), 0)                        AS total_score
    FROM game_sessions gs
    WHERE user_id = :user_id
');
$stmt->execute(['user_id' => $user['id']]);
$rawStats = $stmt->fetch();

// Prostsze zapytanie — accuracy liczymy przez game_answers
$stmt = $db->prepare('
    SELECT
        COUNT(DISTINCT gs.id)                                   AS games_played,
        COALESCE(AVG(gs.correct_count), 0)                     AS avg_correct,
        COALESCE(
            SUM(gs.correct_count)::float /
            NULLIF((
                SELECT COUNT(*) FROM game_answers ga
                JOIN game_sessions gs2 ON gs2.id = ga.session_id
                WHERE gs2.user_id = :user_id2
            ), 0) * 100
        , 0)                                                    AS accuracy,
        COALESCE(SUM(gs.score), 0)                             AS total_score
    FROM game_sessions gs
    WHERE gs.user_id = :user_id
');
$stmt->execute(['user_id' => $user['id'], 'user_id2' => $user['id']]);
$stats = $stmt->fetch();

$gamesPlayed = (int)   $stats['games_played'];
$accuracy    = (int)   round((float) $stats['accuracy']);
$avgCorrect  = round((float) $stats['avg_correct'], 1);
$totalScore  = (int)   $stats['total_score'];

// ── Ostatnie gry ───────────────────────────────────────────────────────────
$stmt = $db->prepare('
    SELECT
        gs.id,
        gs.score,
        gs.correct_count,
        gs.time_taken,
        gs.completed_at,
        q.title      AS quiz_title,
        q.category,
        q.difficulty,
        (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) AS total_questions
    FROM game_sessions gs
    JOIN quizzes q ON q.id = gs.quiz_id
    WHERE gs.user_id = :user_id
    ORDER BY gs.completed_at DESC
    LIMIT 6
');
$stmt->execute(['user_id' => $user['id']]);
$recentGames = $stmt->fetchAll();

// ── XP / level ─────────────────────────────────────────────────────────────
// Odśwież XP z bazy (sesja może być nieaktualna po grze)
$stmt = $db->prepare('SELECT xp FROM users WHERE id = :id');
$stmt->execute(['id' => $user['id']]);
$freshXp  = (int) $stmt->fetchColumn();
$level    = max(1, (int) floor($freshXp / 200) + 1);
$xpInLvl  = $freshXp % 200;
$xpPct    = round(($xpInLvl / 200) * 100);
$initials = strtoupper(substr($user['username'], 0, 2));

$difficultyLabel = ['', '⭐', '⭐⭐', '⭐⭐⭐'];
$categoryIcon    = [
    'Geography'      => '🌍',
    'Science'        => '🔬',
    'History'        => '📜',
    'Math'           => '➗',
    'Technology'     => '💻',
    'Sports'         => '⚽',
    'Music'          => '🎵',
    'Movies'         => '🎬',
];
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

    <!-- ── Hero ── -->
    <div class="hero">
        <div class="hero-left">
            <div class="hero-avatar">🎮</div>
            <div>
                <div class="hero-greeting">Welcome back,</div>
                <div class="hero-name"><?= htmlspecialchars($user['username']) ?></div>
                <div class="hero-rank">
                    <span>Level <?= $level ?> · <?= $freshXp ?> XP total</span>
                </div>
            </div>
        </div>
        <div class="hero-xp">
            <div class="hero-xp-label">XP PROGRESSION</div>
            <div class="hero-xp-values">
                <strong><?= $xpInLvl ?></strong> / 200 XP to level <?= $level + 1 ?>
            </div>
            <div class="xp-bar">
                <div class="xp-bar-fill" style="width: <?= $xpPct ?>%"></div>
            </div>
        </div>
    </div>

    <!-- ── Stats ── -->
    <div class="stats-grid">
        <div class="stat-card games">
            <div class="stat-info">
                <div class="stat-label">Games Played</div>
                <div class="stat-value"><?= $gamesPlayed ?></div>
            </div>
            <div class="stat-icon">🎮</div>
        </div>
        <div class="stat-card acc">
            <div class="stat-info">
                <div class="stat-label">Accuracy</div>
                <div class="stat-value">
                    <?= $gamesPlayed > 0 ? $accuracy : '—' ?>
                    <?php if ($gamesPlayed > 0): ?><sup>%</sup><?php endif; ?>
                </div>
            </div>
            <div class="stat-icon">🎯</div>
        </div>
        <div class="stat-card wins">
            <div class="stat-info">
                <div class="stat-label">Avg Correct</div>
                <div class="stat-value">
                    <?= $gamesPlayed > 0 ? $avgCorrect : '—' ?>
                </div>
            </div>
            <div class="stat-icon">✅</div>
        </div>
    </div>

    <!-- ── CTA ── -->
    <div class="cta-row">
        <a href="/quiz/browse.php" class="btn-start">▶ START NEW GAME</a>
        <a href="/quiz/create.php" class="btn-create">+ CREATE QUIZ</a>
    </div>

    <!-- ── Recent Games ── -->
    <div class="section-header">
        <div class="section-title">Recent Games</div>
        <?php if ($gamesPlayed > 0): ?>
            <a href="#" class="section-link">VIEW ALL</a>
        <?php endif; ?>
    </div>

    <?php if (empty($recentGames)): ?>
        <div class="empty-state">
            <p>No games yet. Play your first quiz! 🎯</p>
            <a href="/quiz/browse.php" class="btn-primary">Browse Quizzes</a>
        </div>
    <?php else: ?>
        <div class="games-grid">
            <?php foreach ($recentGames as $game):
                $gameAccuracy = $game['total_questions'] > 0
                    ? round(($game['correct_count'] / $game['total_questions']) * 100)
                    : 0;
                $icon = $categoryIcon[$game['category']] ?? '🧠';
                $trophy = match(true) {
                    $gameAccuracy === 100 => '🏆',
                    $gameAccuracy >= 80   => '🥇',
                    $gameAccuracy >= 60   => '🥈',
                    default               => '🥉',
                };
                $timeAgo = timeAgo($game['completed_at']);
            ?>
                <a href="/quiz/results.php?session=<?= htmlspecialchars($game['id']) ?>" class="game-card">
                    <div class="game-thumb">
                        <span style="position:relative;z-index:1;font-size:2.5rem"><?= $icon ?></span>
                        <span class="game-badge badge-xp">+<?= $game['score'] ?> pts</span>
                    </div>
                    <div class="game-body">
                        <div class="game-title"><?= htmlspecialchars($game['quiz_title']) ?></div>
                        <div class="game-meta">
                            <span class="game-time"><?= $timeAgo ?></span>
                            <span class="game-place"><?= $trophy ?></span>
                        </div>
                        <div class="game-score-bar">
                            <div class="game-score-fill" style="width:<?= $gameAccuracy ?>%"></div>
                        </div>
                        <div class="game-score-text">
                            <?= $game['correct_count'] ?>/<?= $game['total_questions'] ?> correct · <?= $gameAccuracy ?>%
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Footer -->
<footer>
    <div class="footer-brand">
        <div class="auth-logo" style="font-size:1.1rem">Quiz<span style="color:var(--primary)">Arena</span></div>
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

<?php
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    return match(true) {
        $diff < 60     => 'just now',
        $diff < 3600   => (int)($diff / 60) . 'm ago',
        $diff < 86400  => (int)($diff / 3600) . 'h ago',
        $diff < 604800 => (int)($diff / 86400) . 'd ago',
        default        => date('M j', strtotime($datetime)),
    };
}
?>
</body>
</html>