<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../../.env');
Auth::start();
Auth::require();

$user = Auth::user();
$db   = Database::connect();

// ── Filtry ─────────────────────────────────────────────────────────────────
$period = $_GET['period'] ?? 'all'; // all | week | today
$period = in_array($period, ['all', 'week', 'today']) ? $period : 'all';

$whereClause = match($period) {
    'today' => "WHERE gs.completed_at >= NOW() - INTERVAL '1 day'",
    'week'  => "WHERE gs.completed_at >= NOW() - INTERVAL '7 days'",
    default => 'WHERE true',
};

// ── Top gracze (po łącznym score) ──────────────────────────────────────────


$stmt = $db->prepare("
    SELECT
        u.id,
        u.username,
        u.xp,
        COUNT(DISTINCT gs.id)                           AS games_played,
        COALESCE(SUM(gs.score), 0)                      AS total_score,
        COALESCE(AVG(gs.correct_count), 0)              AS avg_correct,
        COALESCE(
            SUM(gs.correct_count)::float /
            NULLIF(COUNT(ga.id), 0) * 100
        , 0)                                            AS accuracy
    FROM users u
    JOIN game_sessions gs ON gs.user_id = u.id
    LEFT JOIN game_answers ga ON ga.session_id = gs.id
    $whereClause
    GROUP BY u.id, u.username, u.xp
    HAVING COUNT(DISTINCT gs.id) > 0
    ORDER BY total_score DESC
    LIMIT 50
");
$stmt->execute();
$leaders = $stmt->fetchAll();

// ── Pozycja zalogowanego gracza ────────────────────────────────────────────
$myRank = null;
foreach ($leaders as $i => $row) {
    if ($row['id'] === $user['id']) {
        $myRank = $i + 1;
        break;
    }
}

$myWhereClause = match($period) {
    'today' => "AND gs.completed_at >= NOW() - INTERVAL '1 day'",
    'week'  => "AND gs.completed_at >= NOW() - INTERVAL '7 days'",
    default => '',
};

// Jeśli poza top 50 — osobne zapytanie
$myStats = null;
if ($myRank === null) {
    $stmt = $db->prepare("
           SELECT
                COUNT(gs.id)                        AS games_played,
                COALESCE(SUM(gs.score), 0)          AS total_score,
                COALESCE(AVG(gs.correct_count), 0)  AS avg_correct
            FROM game_sessions gs
            WHERE gs.user_id = :uid $myWhereClause
    ");
    $stmt->execute(['uid' => $user['id']]);
    $myStats = $stmt->fetch();
}

$medal = ['🥇', '🥈', '🥉'];
$level = fn($xp) => max(1, (int) floor($xp / 200) + 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard — QuizArena</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/leaderboard.css">
</head>
<body>

<nav class="navbar">
    <a href="/dashboard.php" class="nav-logo">Quiz<span>Arena</span></a>
    <div class="nav-links">
        <a href="/dashboard.php">Dashboard</a>
        <a href="/quiz/create.php">Create Quiz</a>
        <a href="/leaderboard/index.php" class="active">Leaderboard</a>
    </div>
    <div class="nav-right">
        <a href="#" class="nav-bell">🔔</a>
        <a href="/profile/index.php" class="nav-avatar">
            <?= strtoupper(substr($user['username'], 0, 2)) ?>
            <span class="nav-level">LVL <?= $level($user['xp'] ?? 0) ?></span>
        </a>
    </div>
</nav>

<div class="container">

    <div class="page-header">
        <h1>Leaderboard</h1>
        <p>Top players ranked by total score</p>
    </div>

    <!-- ── Period filter ── -->
    <div class="lb-filters">
        <a href="?period=all"   class="lb-filter <?= $period === 'all'   ? 'active' : '' ?>">All Time</a>
        <a href="?period=week"  class="lb-filter <?= $period === 'week'  ? 'active' : '' ?>">This Week</a>
        <a href="?period=today" class="lb-filter <?= $period === 'today' ? 'active' : '' ?>">Today</a>
    </div>

    <?php if (empty($leaders)): ?>
        <div class="empty-state">
            <p>No games played yet in this period. Be the first! 🎯</p>
            <a href="/quiz/browse.php" class="btn-primary">Play Now</a>
        </div>
    <?php else: ?>

        <!-- ── Podium top 3 ── -->
        <?php if (count($leaders) >= 3): ?>
        <div class="podium">
            <!-- 2nd place -->
            <div class="podium-slot podium-slot--2">
                <div class="podium-avatar">
                    <?= strtoupper(substr($leaders[1]['username'], 0, 2)) ?>
                </div>
                <div class="podium-name"><?= htmlspecialchars($leaders[1]['username']) ?></div>
                <div class="podium-score"><?= number_format($leaders[1]['total_score']) ?></div>
                <div class="podium-block podium-block--2">🥈</div>
            </div>

            <!-- 1st place -->
            <div class="podium-slot podium-slot--1">
                <div class="podium-crown">👑</div>
                <div class="podium-avatar podium-avatar--1">
                    <?= strtoupper(substr($leaders[0]['username'], 0, 2)) ?>
                </div>
                <div class="podium-name"><?= htmlspecialchars($leaders[0]['username']) ?></div>
                <div class="podium-score"><?= number_format($leaders[0]['total_score']) ?></div>
                <div class="podium-block podium-block--1">🥇</div>
            </div>

            <!-- 3rd place -->
            <div class="podium-slot podium-slot--3">
                <div class="podium-avatar">
                    <?= strtoupper(substr($leaders[2]['username'], 0, 2)) ?>
                </div>
                <div class="podium-name"><?= htmlspecialchars($leaders[2]['username']) ?></div>
                <div class="podium-score"><?= number_format($leaders[2]['total_score']) ?></div>
                <div class="podium-block podium-block--3">🥉</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Full table ── -->
        <div class="lb-table">
            <div class="lb-table-head">
                <span class="lb-col-rank">#</span>
                <span class="lb-col-player">Player</span>
                <span class="lb-col-score">Score</span>
                <span class="lb-col-games">Games</span>
                <span class="lb-col-acc">Accuracy</span>
            </div>

            <?php foreach ($leaders as $i => $row):
                $rank     = $i + 1;
                $isMe     = $row['id'] === $user['id'];
                $rowAcc   = (int) round((float) $row['accuracy']);
                $rowLevel = $level((int) $row['xp']);
            ?>
                <div class="lb-row <?= $isMe ? 'lb-row--me' : '' ?> <?= $rank <= 3 ? 'lb-row--top' : '' ?>">
                    <span class="lb-col-rank">
                        <?php if ($rank <= 3): ?>
                            <span class="lb-medal"><?= $medal[$rank - 1] ?></span>
                        <?php else: ?>
                            <span class="lb-rank-num"><?= $rank ?></span>
                        <?php endif; ?>
                    </span>

                    <span class="lb-col-player">
                        <span class="lb-avatar" style="<?= $isMe ? 'background: linear-gradient(135deg, var(--primary), var(--secondary))' : '' ?>">
                            <?= strtoupper(substr($row['username'], 0, 2)) ?>
                        </span>
                        <span class="lb-player-info">
                            <span class="lb-username">
                                <?= htmlspecialchars($row['username']) ?>
                                <?php if ($isMe): ?><span class="lb-you-badge">YOU</span><?php endif; ?>
                            </span>
                            <span class="lb-level">Level <?= $rowLevel ?></span>
                        </span>
                    </span>

                    <span class="lb-col-score">
                        <?= number_format((int) $row['total_score']) ?>
                    </span>

                    <span class="lb-col-games">
                        <?= (int) $row['games_played'] ?>
                    </span>

                    <span class="lb-col-acc">
                        <span class="lb-acc-bar">
                            <span class="lb-acc-fill" style="width: <?= $rowAcc ?>%"></span>
                        </span>
                        <span class="lb-acc-val"><?= $rowAcc ?>%</span>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ── Twoja pozycja jeśli poza top 50 ── -->
        <?php if ($myRank === null && $myStats && (int) $myStats['games_played'] > 0): ?>
            <div class="lb-my-rank">
                <span>Your rank is outside top 50</span>
                <span><?= number_format((int) $myStats['total_score']) ?> pts · <?= (int) $myStats['games_played'] ?> games</span>
            </div>
        <?php elseif ($myRank === null): ?>
            <div class="lb-my-rank lb-my-rank--empty">
                You haven't played yet this period —
                <a href="/quiz/browse.php">play now</a> to get on the board!
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>
</body>
</html>