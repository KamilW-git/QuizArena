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

// ── Parametry filtrów ──────────────────────────────────────────────────────
$category   = $_GET['category'] ?? null;
$search     = trim($_GET['search'] ?? '');
$sort       = $_GET['sort'] ?? 'newest';
$difficulty = isset($_GET['difficulty']) && in_array((int)$_GET['difficulty'], [1,2,3])
              ? (int)$_GET['difficulty'] : null;

$sort = in_array($sort, ['newest', 'oldest', 'difficulty_asc', 'difficulty_desc', 'popular'])
        ? $sort : 'newest';

// ── ORDER BY ───────────────────────────────────────────────────────────────
$orderBy = match($sort) {
    'oldest'          => 'q.created_at ASC',
    'difficulty_asc'  => 'q.difficulty ASC, q.created_at DESC',
    'difficulty_desc' => 'q.difficulty DESC, q.created_at DESC',
    'popular'         => 'play_count DESC, q.created_at DESC',
    default           => 'q.created_at DESC',
};

// ── Zapytanie ──────────────────────────────────────────────────────────────
$where  = ['q.is_public = true'];
$params = [];

if ($category) {
    $where[]              = 'q.category = :category';
    $params['category']   = $category;
}
if ($difficulty) {
    $where[]              = 'q.difficulty = :difficulty';
    $params['difficulty'] = $difficulty;
}
if ($search !== '') {
    $where[]            = '(q.title ILIKE :search OR q.category ILIKE :search)';
    $params['search']   = '%' . $search . '%';
}

$whereSQL = implode(' AND ', $where);

$stmt = $db->prepare("
    SELECT
        q.*,
        u.username,
        COUNT(DISTINCT qu.id)  AS question_count,
        COUNT(DISTINCT gs.id)  AS play_count
    FROM quizzes q
    JOIN users u              ON u.id    = q.user_id
    LEFT JOIN questions qu    ON qu.quiz_id = q.id
    LEFT JOIN game_sessions gs ON gs.quiz_id = q.id
    WHERE $whereSQL
    GROUP BY q.id, u.username
    ORDER BY $orderBy
");
$stmt->execute($params);
$quizzes = $stmt->fetchAll();

// ── Kategorie do filtrów ───────────────────────────────────────────────────
$stmt       = $db->prepare('SELECT DISTINCT category FROM quizzes WHERE is_public = true ORDER BY category ASC');
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

$difficultyLabel = ['', '⭐ Easy', '⭐⭐ Medium', '⭐⭐⭐ Hard'];
$catIcon = [
    'Geography'  => '🌍', 'Science'    => '🔬', 'History' => '📜',
    'Technology' => '💻', 'Math'       => '➗', 'Movies'  => '🎬',
];

// URL pomocniczy — zachowuje obecne filtry, nadpisuje wskazany param
function browsUrl(array $override = []): string {
    $params = array_filter([
        'category'   => $_GET['category']   ?? null,
        'search'     => $_GET['search']     ?? null,
        'sort'       => $_GET['sort']       ?? null,
        'difficulty' => $_GET['difficulty'] ?? null,
    ]);
    $params = array_merge($params, $override);
    // Usuń puste
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    return '/quiz/browse.php' . ($params ? '?' . http_build_query($params) : '');
}
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
            <span class="nav-level">LVL <?= max(1, floor(($user['xp']??0) / 200) + 1) ?></span>
        </a>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1>Browse Quizzes</h1>
        <p>
            <?= count($quizzes) ?> quiz<?= count($quizzes) !== 1 ? 'zes' : '' ?> found
            <?php if ($search): ?>
                for <strong>"<?= htmlspecialchars($search) ?>"</strong>
            <?php endif; ?>
        </p>
    </div>

    <!-- ── Search + Sort bar ── -->
    <div class="browse-bar">
        <form class="search-form" method="GET" action="/quiz/browse.php">
            <?php if ($category): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <?php endif; ?>
            <?php if ($difficulty): ?>
                <input type="hidden" name="difficulty" value="<?= $difficulty ?>">
            <?php endif; ?>
            <div class="search-input-wrap">
                <span class="search-icon">🔍</span>
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="Search quizzes…"
                    value="<?= htmlspecialchars($search) ?>"
                    autocomplete="off"
                >
                <?php if ($search): ?>
                    <a href="<?= browsUrl(['search' => null]) ?>" class="search-clear">✕</a>
                <?php endif; ?>
            </div>
            <button type="submit" class="search-btn">Search</button>
        </form>

        <div class="sort-wrap">
            <label class="sort-label">Sort:</label>
            <div class="sort-options">
                <a href="<?= browsUrl(['sort' => 'newest']) ?>"
                   class="sort-opt <?= $sort === 'newest' ? 'active' : '' ?>">Newest</a>
                <a href="<?= browsUrl(['sort' => 'popular']) ?>"
                   class="sort-opt <?= $sort === 'popular' ? 'active' : '' ?>">Popular</a>
                <a href="<?= browsUrl(['sort' => 'difficulty_asc']) ?>"
                   class="sort-opt <?= $sort === 'difficulty_asc' ? 'active' : '' ?>">Easiest</a>
                <a href="<?= browsUrl(['sort' => 'difficulty_desc']) ?>"
                   class="sort-opt <?= $sort === 'difficulty_desc' ? 'active' : '' ?>">Hardest</a>
            </div>
        </div>
    </div>

    <!-- ── Category filters ── -->
    <div class="filters">
        <a href="<?= browsUrl(['category' => null]) ?>"
           class="filter-tag <?= !$category ? 'active' : '' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= browsUrl(['category' => $cat]) ?>"
               class="filter-tag <?= $category === $cat ? 'active' : '' ?>">
                <?= ($catIcon[$cat] ?? '🧠') . ' ' . htmlspecialchars($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ── Difficulty filter ── -->
    <div class="diff-filters">
        <a href="<?= browsUrl(['difficulty' => null]) ?>"
           class="diff-tag <?= !$difficulty ? 'active' : '' ?>">Any difficulty</a>
        <a href="<?= browsUrl(['difficulty' => 1]) ?>"
           class="diff-tag <?= $difficulty === 1 ? 'active' : '' ?>">⭐ Easy</a>
        <a href="<?= browsUrl(['difficulty' => 2]) ?>"
           class="diff-tag <?= $difficulty === 2 ? 'active' : '' ?>">⭐⭐ Medium</a>
        <a href="<?= browsUrl(['difficulty' => 3]) ?>"
           class="diff-tag <?= $difficulty === 3 ? 'active' : '' ?>">⭐⭐⭐ Hard</a>
    </div>

    <!-- ── Active filters summary ── -->
    <?php if ($category || $difficulty || $search): ?>
        <div class="active-filters">
            <span class="active-filters__label">Active filters:</span>
            <?php if ($category): ?>
                <span class="active-filter-chip">
                    <?= htmlspecialchars($category) ?>
                    <a href="<?= browsUrl(['category' => null]) ?>">✕</a>
                </span>
            <?php endif; ?>
            <?php if ($difficulty): ?>
                <span class="active-filter-chip">
                    <?= $difficultyLabel[$difficulty] ?>
                    <a href="<?= browsUrl(['difficulty' => null]) ?>">✕</a>
                </span>
            <?php endif; ?>
            <?php if ($search): ?>
                <span class="active-filter-chip">
                    "<?= htmlspecialchars($search) ?>"
                    <a href="<?= browsUrl(['search' => null]) ?>">✕</a>
                </span>
            <?php endif; ?>
            <a href="/quiz/browse.php" class="active-filters__clear">Clear all</a>
        </div>
    <?php endif; ?>

    <!-- ── Quiz grid ── -->
    <?php if (empty($quizzes)): ?>
        <div class="empty-state">
            <?php if ($search || $category || $difficulty): ?>
                <p>No quizzes match your filters. 🔍</p>
                <a href="/quiz/browse.php" class="btn-primary">Clear filters</a>
            <?php else: ?>
                <p>No quizzes yet. Be the first to create one! 🎯</p>
                <a href="/quiz/create.php" class="btn-primary">Create Quiz</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="quiz-grid">
            <?php foreach ($quizzes as $quiz):
                $icon = $catIcon[$quiz['category']] ?? '🧠';
                $plays = (int) $quiz['play_count'];
            ?>
                <a href="/quiz/play.php?id=<?= htmlspecialchars($quiz['id']) ?>" class="quiz-card">
                    <div class="quiz-card-thumb">
                        <span class="quiz-card-icon"><?= $icon ?></span>
                        <span class="quiz-card-category"><?= htmlspecialchars($quiz['category']) ?></span>
                        <span class="quiz-card-diff"><?= $difficultyLabel[$quiz['difficulty']] ?></span>
                    </div>
                    <div class="quiz-card-body">
                        <div class="quiz-card-title"><?= htmlspecialchars($quiz['title']) ?></div>
                        <div class="quiz-card-meta">
                            <span><?= $quiz['question_count'] ?> questions</span>
                            <span class="quiz-card-plays">▶ <?= $plays ?> play<?= $plays !== 1 ? 's' : '' ?></span>
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