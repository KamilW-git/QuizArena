<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Controllers\QuizController;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../../.env');
Auth::start();
Auth::require();

$user   = Auth::user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db         = Database::connect();
    $controller = new QuizController($db);

    $data = [
        'title'      => $_POST['title'] ?? '',
        'category'   => $_POST['category'] ?? '',
        'difficulty' => $_POST['difficulty'] ?? 1,
        'questions'  => [],
    ];

    // Zbierz pytania z POST
    $rawQuestions = $_POST['questions'] ?? [];
    foreach ($rawQuestions as $q) {
        $data['questions'][] = [
            'content'        => $q['content'] ?? '',
            'correct_answer' => (int) ($q['correct_answer'] ?? 0),
            'time_limit'     => (int) ($q['time_limit'] ?? 30),
            'answers'        => $q['answers'] ?? ['', '', '', ''],
        ];
    }

    $result = $controller->create($user['id'], $data);

    if ($result['success']) {
        header('Location: /quiz/browse.php?created=1');
        exit;
    }

    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz — QuizArena</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/create.css">
</head>
<body>

<nav class="navbar">
    <a href="/dashboard.php" class="nav-logo">Quiz<span>Arena</span></a>
    <div class="nav-links">
        <a href="/dashboard.php">Dashboard</a>
        <a href="/quiz/create.php" class="active">Create Quiz</a>
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
        <h1>Create Quiz</h1>
        <p>Build your own quiz and share it with the community</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert-error"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" id="quiz-form">

        <!-- Quiz info -->
        <div class="form-card">
            <h2 class="form-card-title">Quiz Details</h2>

            <div class="form-row">
                <div class="form-group">
                    <label>Quiz Title</label>
                    <input
                        type="text"
                        name="title"
                        placeholder="e.g. World Capitals"
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                    >
                    <?php if (!empty($errors['title'])): ?>
                        <span class="field-error"><?= $errors['title'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input
                        type="text"
                        name="category"
                        placeholder="e.g. Geography"
                        value="<?= htmlspecialchars($_POST['category'] ?? '') ?>"
                    >
                    <?php if (!empty($errors['category'])): ?>
                        <span class="field-error"><?= $errors['category'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Difficulty</label>
                    <select name="difficulty">
                        <option value="1" <?= ($_POST['difficulty'] ?? 1) == 1 ? 'selected' : '' ?>>⭐ Easy</option>
                        <option value="2" <?= ($_POST['difficulty'] ?? 1) == 2 ? 'selected' : '' ?>>⭐⭐ Medium</option>
                        <option value="3" <?= ($_POST['difficulty'] ?? 1) == 3 ? 'selected' : '' ?>>⭐⭐⭐ Hard</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Questions -->
        <div class="form-card">
            <h2 class="form-card-title">Questions</h2>

            <?php if (!empty($errors['questions'])): ?>
                <div class="alert-error" style="margin-bottom:1rem"><?= $errors['questions'] ?></div>
            <?php endif; ?>

            <div id="questions-list"></div>

            <button type="button" id="add-question" class="btn-add-question">
                + Add Question
            </button>
        </div>

        <div class="form-actions">
            <a href="/dashboard.php" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-publish">🚀 Publish Quiz</button>
        </div>

    </form>
</div>

<script src="/assets/js/quiz-creator.js"></script>
</body>
</html>