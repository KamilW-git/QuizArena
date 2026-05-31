<?php
require_once __DIR__ . '/layout/header.php';

use QuizArena\Config\Database;
use QuizArena\Controllers\AdminController;

$db = Database::connect();
$adminController = new AdminController($db);

$searchQuery = $_GET['q'] ?? '';
$quizzes = $adminController->searchQuizzes($searchQuery ?: null);
?>

<h1>Quizzes Management</h1>

<form class="search-bar" method="GET">
    <input type="text" name="q" placeholder="Search by ID, Title or Author..." value="<?= htmlspecialchars($searchQuery) ?>" class="form-control">
    <button type="submit" class="btn-primary">Search</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Category</th>
            <th>Author</th>
            <th>Created</th>
            <th>Questions</th>
            <th>Plays</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($quizzes as $q): ?>
            <tr>
                <td style="font-size: 0.8em; color: var(--color-text-muted);"><?= htmlspecialchars(substr($q['id'], 0, 8)) ?>...</td>
                <td><a href="/admin/quiz_edit.php?id=<?= urlencode($q['id']) ?>"><?= htmlspecialchars($q['title']) ?></a></td>
                <td><?= htmlspecialchars($q['category']) ?></td>
                <td><?= htmlspecialchars($q['author']) ?></td>
                <td><?= htmlspecialchars(date('Y-m-d', strtotime($q['created_at']))) ?></td>
                <td><?= (int)$q['question_count'] ?></td>
                <td><?= (int)$q['games_played'] ?></td>
                <td><span class="badge <?= strtolower($q['status']) ?>"><?= htmlspecialchars($q['status']) ?></span></td>
                <td class="actions">
                    <a href="/admin/quiz_edit.php?id=<?= urlencode($q['id']) ?>" class="btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; text-decoration: none;">Edit</a>
                    <?php if ($q['status'] === 'PUBLISHED'): ?>
                        <button class="btn-primary" style="background: var(--color-error); padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="adminAction('HIDE_QUIZ', 'QUIZ', '<?= $q['id'] ?>', {status: 'HIDDEN'})">Hide</button>
                    <?php else: ?>
                        <button class="btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="adminAction('PUBLISH_QUIZ', 'QUIZ', '<?= $q['id'] ?>', {status: 'PUBLISHED'})">Publish</button>
                    <?php endif; ?>
                    <button class="btn-primary" style="background: var(--color-error); padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="adminAction('DELETE_QUIZ', 'QUIZ', '<?= $q['id'] ?>')">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($quizzes)): ?>
            <tr><td colspan="9" style="text-align: center;">No quizzes found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
