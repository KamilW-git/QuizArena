<?php
require_once __DIR__ . '/layout/header.php';

use QuizArena\Config\Database;
use QuizArena\Controllers\AdminController;

$db = Database::connect();
$adminController = new AdminController($db);

$searchQuery = $_GET['q'] ?? '';
$users = $adminController->searchUsers($searchQuery ?: null);
?>

<h1>Users Management</h1>

<form class="search-bar" method="GET">
    <input type="text" name="q" placeholder="Search by ID, Username or Email..." value="<?= htmlspecialchars($searchQuery) ?>" class="form-control">
    <button type="submit" class="btn-primary">Search</button>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Registered</th>
            <th>Quizzes</th>
            <th>Games Played</th>
            <th>Status</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td style="font-size: 0.8em; color: var(--color-text-muted);"><?= htmlspecialchars(substr($u['id'], 0, 8)) ?>...</td>
                <td><a href="/admin/user_details.php?id=<?= urlencode($u['id']) ?>"><?= htmlspecialchars($u['username']) ?></a></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars(date('Y-m-d', strtotime($u['created_at']))) ?></td>
                <td><?= (int)$u['quiz_count'] ?></td>
                <td><?= (int)$u['games_played'] ?></td>
                <td><span class="badge <?= strtolower($u['status']) ?>"><?= htmlspecialchars($u['status']) ?></span></td>
                <td><span class="badge <?= strtolower($u['role']) ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                <td class="actions">
                    <?php if ($u['id'] !== QuizArena\Helpers\Auth::user()['id']): ?>
                        <?php if ($u['status'] === 'ACTIVE'): ?>
                            <button class="btn-primary" style="background: var(--color-error); padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="adminAction('SUSPEND_USER', 'USER', '<?= $u['id'] ?>', {status: 'SUSPENDED'})">Suspend</button>
                            <button class="btn-primary" style="background: var(--color-error); padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="adminAction('BAN_USER', 'USER', '<?= $u['id'] ?>', {status: 'BANNED'})">Ban</button>
                        <?php else: ?>
                            <button class="btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="adminAction('ACTIVATE_USER', 'USER', '<?= $u['id'] ?>', {status: 'ACTIVE'})">Activate</button>
                        <?php endif; ?>
                        <button class="btn-primary" style="background: var(--color-error); padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="adminAction('DELETE_USER', 'USER', '<?= $u['id'] ?>')">Delete</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
            <tr><td colspan="9" style="text-align: center;">No users found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
