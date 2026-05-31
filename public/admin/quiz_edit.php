<?php
require_once __DIR__ . '/layout/header.php';

use QuizArena\Config\Database;
use QuizArena\Controllers\QuizController;
use QuizArena\Helpers\Auth;

$db = Database::connect();
$quizController = new QuizController($db);

$id = $_GET['id'] ?? '';
$quiz = $quizController->getById($id);

if (!$quiz) {
    echo "<h2>Quiz not found</h2>";
    require_once __DIR__ . '/layout/footer.php';
    exit;
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $difficulty = (int)($_POST['difficulty'] ?? 1);
        
        $adminController = new \QuizArena\Controllers\AdminController($db);
        if ($adminController->updateQuiz(Auth::user()['id'], $quiz['id'], $title, $category, $difficulty)) {
            $success = true;
            $quiz['title'] = $title;
            $quiz['category'] = $category;
            $quiz['difficulty'] = $difficulty;
        } else {
            $error = 'Failed to update quiz.';
        }
    }
}
?>

<h1>Edit Quiz: <?= htmlspecialchars($quiz['title']) ?></h1>

<?php if ($success): ?>
    <div class="alert alert-success">Quiz updated successfully!</div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="max-width: 600px; margin-top: 2rem;">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::generateCsrfToken()) ?>">
        
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($quiz['title']) ?>" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" value="<?= htmlspecialchars($quiz['category']) ?>" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="difficulty">Difficulty</label>
            <select id="difficulty" name="difficulty" class="form-control" required>
                <option value="1" <?= $quiz['difficulty'] == 1 ? 'selected' : '' ?>>Easy</option>
                <option value="2" <?= $quiz['difficulty'] == 2 ? 'selected' : '' ?>>Medium</option>
                <option value="3" <?= $quiz['difficulty'] == 3 ? 'selected' : '' ?>>Hard</option>
            </select>
        </div>
        
        <button type="submit" class="btn-primary">Save Changes</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
