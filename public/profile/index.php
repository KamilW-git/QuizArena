<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;
use QuizArena\Models\Achievement;

Env::load(__DIR__ . '/../../.env');
Auth::start();
Auth::require();

$db          = Database::connect();
$sessionUser = Auth::user();

// Można zobaczyć profil innego gracza przez ?id=uuid, domyślnie własny
$profileId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS)
           ?? $sessionUser['id'];

// Pobierz dane gracza
$stmt = $db->prepare('SELECT id, username, email, xp, created_at FROM users WHERE id = :id');
$stmt->execute(['id' => $profileId]);
$profile = $stmt->fetch();

if (!$profile) {
    header('Location: /dashboard.php');
    exit;
}

$isOwnProfile = $profile['id'] === $sessionUser['id'];

// ── Statystyki ogólne ──────────────────────────────────────────────────────
$stmt = $db->prepare('
    SELECT
        COUNT(DISTINCT gs.id)                           AS games_played,
        COALESCE(SUM(gs.score), 0)                      AS total_score,
        COALESCE(AVG(gs.correct_count), 0)              AS avg_correct,
        COALESCE(MAX(gs.score), 0)                      AS best_score,
        COALESCE(
            SUM(gs.correct_count)::float /
            NULLIF(COUNT(ga.id), 0) * 100
        , 0)                                            AS accuracy,
        COALESCE(SUM(gs.time_taken), 0)                 AS total_time
    FROM game_sessions gs
    LEFT JOIN game_answers ga ON ga.session_id = gs.id
    WHERE gs.user_id = :uid
');
$stmt->execute(['uid' => $profileId]);
$stats = $stmt->fetch();

$gamesPlayed = (int)   $stats['games_played'];
$totalScore  = (int)   $stats['total_score'];
$bestScore   = (int)   $stats['best_score'];
$accuracy    = (int)   round((float) $stats['accuracy']);
$avgCorrect  = round((float) $stats['avg_correct'], 1);
$totalTime   = (int)   $stats['total_time'];

// ── Ranking pozycja ────────────────────────────────────────────────────────
$stmt = $db->prepare('
    SELECT COUNT(*) + 1 AS rank
    FROM (
        SELECT user_id, SUM(score) AS total
        FROM game_sessions
        GROUP BY user_id
    ) sub
    WHERE sub.total > :my_score
');
$stmt->execute(['my_score' => $totalScore]);
$rankRow     = $stmt->fetch();
$globalRank  = $gamesPlayed > 0 ? (int) $rankRow['rank'] : null;

// ── Historia gier (ostatnie 20) ────────────────────────────────────────────
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
    WHERE gs.user_id = :uid
    ORDER BY gs.completed_at DESC
    LIMIT 20
');

 $achievementModel = new Achievement($db);
 $achievements     = $achievementModel->getAllForUser($profileId);

$stmt->execute(['uid' => $profileId]);
$history = $stmt->fetchAll();

// ── Ulubiona kategoria ────────────────────────────────────────────────────
$stmt = $db->prepare('
    SELECT q.category, COUNT(*) AS cnt
    FROM game_sessions gs
    JOIN quizzes q ON q.id = gs.quiz_id
    WHERE gs.user_id = :uid
    GROUP BY q.category
    ORDER BY cnt DESC
    LIMIT 1
');
$stmt->execute(['uid' => $profileId]);
$favCategory = $stmt->fetchColumn() ?: null;

// ── XP / level ────────────────────────────────────────────────────────────
$xp       = (int) $profile['xp'];
$level    = max(1, (int) floor($xp / 200) + 1);
$xpInLvl  = $xp % 200;
$xpPct    = round(($xpInLvl / 200) * 100);
$initials = strtoupper(substr($profile['username'], 0, 2));
$memberSince = date('M Y', strtotime($profile['created_at']));

// ── Helpers ────────────────────────────────────────────────────────────────
function timeAgo(string $dt): string {
    $diff = time() - strtotime($dt);
    return match(true) {
        $diff < 60     => 'just now',
        $diff < 3600   => (int)($diff/60).'m ago',
        $diff < 86400  => (int)($diff/3600).'h ago',
        $diff < 604800 => (int)($diff/86400).'d ago',
        default        => date('M j, Y', strtotime($dt)),
    };
}

function formatTime(int $seconds): string {
    if ($seconds < 60)   return $seconds . 's';
    if ($seconds < 3600) return round($seconds / 60) . 'm';
    return round($seconds / 3600, 1) . 'h';
}

$diffLabel = ['', 'Easy', 'Medium', 'Hard'];
$diffColor = ['', 'var(--secondary)', 'var(--primary)', 'var(--tertiary)'];
$catIcon   = [
    'Geography' => '🌍', 'Science' => '🔬', 'History' => '📜',
    'Math' => '➗', 'Technology' => '💻', 'Sports' => '⚽',
    'Music' => '🎵', 'Movies' => '🎬',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($profile['username']) ?> — QuizArena</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
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
            <?= strtoupper(substr($sessionUser['username'], 0, 2)) ?>
            <span class="nav-level">LVL <?= max(1, (int)floor(($sessionUser['xp']??0)/200)+1) ?></span>
        </a>
    </div>
</nav>

<div class="container">

    <!-- ── Profile header ── -->
    <div class="profile-header">
        <div class="profile-avatar-wrap">
            <div class="profile-avatar">
                <?= $initials ?>
            </div>
            <div class="profile-level-badge">LVL <?= $level ?></div>
        </div>

        <div class="profile-info">
            <div class="profile-name-row">
                <h1 class="profile-username"><?= htmlspecialchars($profile['username']) ?></h1>
                <?php if ($globalRank): ?>
                    <span class="profile-rank-badge"># <?= $globalRank ?></span>
                <?php endif; ?>
            </div>
            <p class="profile-meta">
                Member since <?= $memberSince ?>
                <?php if ($favCategory): ?>
                    · Loves <?= htmlspecialchars($favCategory) ?> <?= $catIcon[$favCategory] ?? '🧠' ?>
                <?php endif; ?>
            </p>

            <!-- XP bar -->
            <div class="profile-xp">
                <div class="profile-xp-bar">
                    <div class="profile-xp-fill" style="width: <?= $xpPct ?>%"></div>
                </div>
                <span class="profile-xp-label"><?= $xpInLvl ?> / 200 XP → Level <?= $level + 1 ?></span>
            </div>
        </div>

        <?php if ($isOwnProfile): ?>
            <a href="/logout.php" class="profile-logout">Sign out</a>
        <?php endif; ?>
    </div>

    <!-- ── Stats grid ── -->
    <div class="profile-stats">
        <div class="pstat">
            <span class="pstat__icon">🎮</span>
            <span class="pstat__value"><?= $gamesPlayed ?: '—' ?></span>
            <span class="pstat__label">Games</span>
        </div>
        <div class="pstat pstat--accent">
            <span class="pstat__icon">⭐</span>
            <span class="pstat__value"><?= number_format($totalScore) ?></span>
            <span class="pstat__label">Total Score</span>
        </div>
        <div class="pstat">
            <span class="pstat__icon">🎯</span>
            <span class="pstat__value"><?= $gamesPlayed > 0 ? $accuracy.'%' : '—' ?></span>
            <span class="pstat__label">Accuracy</span>
        </div>
        <div class="pstat">
            <span class="pstat__icon">🏅</span>
            <span class="pstat__value"><?= $gamesPlayed > 0 ? number_format($bestScore) : '—' ?></span>
            <span class="pstat__label">Best Score</span>
        </div>
        <div class="pstat">
            <span class="pstat__icon">⏱</span>
            <span class="pstat__value"><?= $gamesPlayed > 0 ? formatTime($totalTime) : '—' ?></span>
            <span class="pstat__label">Time Played</span>
        </div>
        <div class="pstat">
            <span class="pstat__icon">✅</span>
            <span class="pstat__value"><?= $gamesPlayed > 0 ? $avgCorrect : '—' ?></span>
            <span class="pstat__label">Avg Correct</span>
        </div>
    </div>

    <!-- ── Game history ── -->
    <div class="section-header" style="margin-top: 2rem">
        <div class="section-title">Game History</div>
        <span class="section-link" style="cursor:default"><?= $gamesPlayed ?> total</span>
    </div>

    <?php if (empty($history)): ?>
        <div class="empty-state">
            <p>No games played yet. <?= $isOwnProfile ? 'Start playing!' : 'Check back later.' ?></p>
            <?php if ($isOwnProfile): ?>
                <a href="/quiz/browse.php" class="btn-primary">Browse Quizzes</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="history-list">
            <?php foreach ($history as $game):
                $gameAcc = $game['total_questions'] > 0
                    ? round(($game['correct_count'] / $game['total_questions']) * 100)
                    : 0;
                $icon = $catIcon[$game['category']] ?? '🧠';
                $trophy = match(true) {
                    $gameAcc === 100 => '🏆',
                    $gameAcc >= 80   => '🥇',
                    $gameAcc >= 60   => '🥈',
                    default          => '🥉',
                };
                $diff = (int)($game['difficulty'] ?? 1);
            ?>
                <a href="/quiz/results.php?session=<?= htmlspecialchars($game['id']) ?>" class="history-row">
                    <span class="history-icon"><?= $icon ?></span>

                    <span class="history-main">
                        <span class="history-title"><?= htmlspecialchars($game['quiz_title']) ?></span>
                        <span class="history-sub">
                            <span class="history-diff" style="color: <?= $diffColor[$diff] ?>">
                                <?= $diffLabel[$diff] ?>
                            </span>
                            · <?= timeAgo($game['completed_at']) ?>
                        </span>
                    </span>

                    <span class="history-acc">
                        <span class="history-acc-bar">
                            <span class="history-acc-fill" style="width:<?= $gameAcc ?>%"></span>
                        </span>
                        <span class="history-acc-val"><?= $gameAcc ?>%</span>
                    </span>

                    <span class="history-score">
                        <span class="history-trophy"><?= $trophy ?></span>
                        <span class="history-pts"><?= number_format($game['score']) ?> pts</span>
                        <span class="history-correct"><?= $game['correct_count'] ?>/<?= $game['total_questions'] ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

             <?php
?>

    <!-- ── Achievements ── -->
    <div class="section-header" style="margin-top: 2rem">
        <div class="section-title">Achievements</div>
        <span class="section-link" style="cursor:default">
            <?= count(array_filter($achievements, fn($a) => $a['unlocked'])) ?>
            / <?= count($achievements) ?> unlocked
        </span>
    </div>

    <div class="achievements-grid">
        <?php foreach ($achievements as $ach):
            $unlocked = (bool) $ach['unlocked'];
            $icon = match($ach['key']) {
                'first_game'    => '🎮',
                'perfect_score' => '💯',
                'quiz_creator'  => '✏️',
                'ten_games'     => '🔥',
                default         => '🏅',
            };
        ?>
            <div class="ach-card <?= $unlocked ? 'ach-card--unlocked' : 'ach-card--locked' ?>">
                <div class="ach-icon"><?= $icon ?></div>
                <div class="ach-body">
                    <div class="ach-name"><?= htmlspecialchars($ach['name']) ?></div>
                    <div class="ach-desc"><?= htmlspecialchars($ach['description']) ?></div>
                    <?php if ($unlocked): ?>
                        <div class="ach-date">
                            Unlocked <?= date('M j, Y', strtotime($ach['unlocked_at'])) ?>
                        </div>
                    <?php else: ?>
                        <div class="ach-xp">+<?= $ach['xp_reward'] ?> XP on unlock</div>
                    <?php endif; ?>
                </div>
                <?php if ($unlocked): ?>
                    <div class="ach-check">✓</div>
                <?php else: ?>
                    <div class="ach-lock">🔒</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>   


    <?php endif; ?>
            

</div>
</body>
</html>