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

$sort = in_array($sort, ['newest', 'oldest', 'difficulty_asc', 'difficulty_desc', 'popular', 'highest_rated'])
        ? $sort : 'newest';

// ── ORDER BY ───────────────────────────────────────────────────────────────
$orderBy = match($sort) {
    'oldest'          => 'q.created_at ASC',
    'difficulty_asc'  => 'q.difficulty ASC, q.created_at DESC',
    'difficulty_desc' => 'q.difficulty DESC, q.created_at DESC',
    'popular'         => 'play_count DESC, q.created_at DESC',
    'highest_rated'   => 'avg_rating DESC NULLS LAST, play_count DESC',
    default           => 'q.created_at DESC',
};

// ── Zapytanie ──────────────────────────────────────────────────────────────
$where  = ["(q.status = 'PUBLISHED' OR (q.status IS NULL AND q.is_public = true))"];
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

// ── Paginacja ──────────────────────────────────────────────────────────────
$countStmt = $db->prepare("SELECT COUNT(*) FROM quizzes q WHERE $whereSQL");
$countStmt->execute($params);
$totalQuizzes = (int) $countStmt->fetchColumn();

$limit = 12;
$totalPages = max(1, (int) ceil($totalQuizzes / $limit));
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, min($page, $totalPages));
$offset = ($page - 1) * $limit;

$stmt = $db->prepare("
    SELECT
        q.*,
        u.username,
        (SELECT COUNT(id) FROM questions WHERE quiz_id = q.id) AS question_count,
        (SELECT COUNT(id) FROM game_sessions WHERE quiz_id = q.id) AS play_count,
        (SELECT ROUND(AVG(rating), 1) FROM quiz_ratings WHERE quiz_id = q.id) AS avg_rating,
        (SELECT COUNT(id) FROM quiz_ratings WHERE quiz_id = q.id) AS rating_count
    FROM quizzes q
    JOIN users u ON u.id = q.user_id
    WHERE $whereSQL
    ORDER BY $orderBy
    LIMIT $limit OFFSET $offset
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
        'page'       => $_GET['page']       ?? null,
    ]);
    $params = array_merge($params, $override);
    
    // Reset page to 1 if we change category, search, sort, or difficulty
    if (!isset($override['page']) && (isset($override['category']) || isset($override['search']) || isset($override['sort']) || isset($override['difficulty']))) {
        $params['page'] = 1;
    }

    // Usuń puste
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    
    // Clean url: don't show page=1
    if (isset($params['page']) && (int)$params['page'] === 1) {
        unset($params['page']);
    }

    return '/quiz/browse.php' . ($params ? '?' . http_build_query($params) : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?php require __DIR__ . '/../includes/theme-head.php'; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Quizzes — QuizArena</title>
    <link rel="stylesheet" href="/assets/css/main.css?v=2">
    <link rel="stylesheet" href="/assets/css/browse.css?v=2">
</head>
<body>

<nav class="navbar">
    <a href="/dashboard.php" class="nav-logo">QuizArena</a>
    <div class="nav-links">
        <a href="/dashboard.php">Dashboard</a>
        <a href="/quiz/create.php">Create Quiz</a>
        <a href="/leaderboard/index.php">Leaderboard</a>
    </div>
    <div class="nav-right">
        <?php require __DIR__ . '/../includes/theme-toggle.php'; ?>
        <a href="#" class="nav-bell">🔔</a>
        <a href="/profile/index.php" class="nav-avatar" style="overflow: hidden;">
            <img src="https://api.dicebear.com/7.x/bottts/svg?seed=<?= urlencode($user['username']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
        </a>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1>Browse Quizzes</h1>
        <p>
            <?= $totalQuizzes ?> quiz<?= $totalQuizzes !== 1 ? 'zes' : '' ?> found
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
                <a href="<?= browsUrl(['sort' => 'highest_rated']) ?>"
                   class="sort-opt <?= $sort === 'highest_rated' ? 'active' : '' ?>">Highest Rated</a>
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
                $rating = $quiz['avg_rating'] !== null ? number_format((float)$quiz['avg_rating'], 1) : 'New';
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
                            <span><?= $quiz['question_count'] ?> Qs</span>
                            <span>⭐ <?= $rating ?></span>
                            <span class="quiz-card-plays">▶ <?= $plays ?></span>
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

    <!-- ── Pagination ── -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= browsUrl(['page' => $page - 1]) ?>" class="page-link">&laquo; Prev</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= browsUrl(['page' => $i]) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="<?= browsUrl(['page' => $page + 1]) ?>" class="page-link">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
<!-- Mobile Bottom Nav -->
<div class="mobile-bottom-nav">
    <div class="mobile-bottom-nav__inner">
        <a href="/dashboard.php" class="bottom-nav-item" data-nav="dashboard">
            <span class="bottom-nav-icon">🏠</span>
            <span>Home</span>
        </a>
        <a href="/quiz/browse.php" class="bottom-nav-item" data-nav="browse">
            <span class="bottom-nav-icon">🔍</span>
            <span>Browse</span>
        </a>
        <a href="/leaderboard/index.php" class="bottom-nav-item" data-nav="leaderboard">
            <span class="bottom-nav-icon">📊</span>
            <span>Arena</span>
        </a>
        <a href="/profile/index.php" class="bottom-nav-item" data-nav="profile">
            <span class="bottom-nav-icon">👤</span>
            <span>Profile</span>
        </a>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const path = window.location.pathname;
    document.querySelectorAll('.bottom-nav-item').forEach(item => {
        if (path.includes(item.dataset.nav)) {
            item.classList.add('active');
        } else if (path === '/' && item.dataset.nav === 'dashboard') {
            item.classList.add('active');
        }
    });
});
</script>
<script src="/assets/js/theme.js"></script>
<script src="/assets/js/notifications.js"></script>
</body>
</html>