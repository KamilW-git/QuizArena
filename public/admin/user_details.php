<?php
require_once __DIR__ . '/layout/header.php';

use QuizArena\Config\Database;
use QuizArena\Controllers\AdminController;

$db = Database::connect();
$adminController = new AdminController($db);

$id = $_GET['id'] ?? '';
$user = $adminController->getUserDetails($id);

if (!$user) {
    echo "<h2>User not found</h2>";
    require_once __DIR__ . '/layout/footer.php';
    exit;
}
?>

<h1>User Details: <?= htmlspecialchars($user['username']) ?></h1>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
    <div class="stat-card" style="text-align: left;">
        <h3>Profile Information</h3>
        <p><strong>ID:</strong> <?= htmlspecialchars($user['id']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Role:</strong> <span class="badge <?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span></p>
        <p><strong>Status:</strong> <span class="badge <?= strtolower($user['status']) ?>"><?= htmlspecialchars($user['status']) ?></span></p>
        <p><strong>XP:</strong> <?= (int)$user['xp'] ?></p>
        <p><strong>Registered:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
    </div>
    
    <div class="stat-card" style="text-align: left;">
        <h3>Activity Statistics</h3>
        <p><strong>Quizzes Created:</strong> <?= (int)$user['quiz_count'] ?></p>
        <p><strong>Games Played:</strong> <?= (int)$user['games_played'] ?></p>
        
        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <?php if ($user['id'] !== QuizArena\Helpers\Auth::user()['id']): ?>
                <?php if ($user['status'] === 'ACTIVE'): ?>
                    <button class="btn-primary" style="background: var(--color-error);" onclick="adminAction('SUSPEND_USER', 'USER', '<?= $user['id'] ?>', {status: 'SUSPENDED'})">Suspend Account</button>
                    <button class="btn-primary" style="background: var(--color-error);" onclick="adminAction('BAN_USER', 'USER', '<?= $user['id'] ?>', {status: 'BANNED'})">Ban Account</button>
                <?php else: ?>
                    <button class="btn-primary" onclick="adminAction('ACTIVATE_USER', 'USER', '<?= $user['id'] ?>', {status: 'ACTIVE'})">Reactivate Account</button>
                <?php endif; ?>
                <button class="btn-primary" style="background: var(--color-error);" onclick="adminAction('DELETE_USER', 'USER', '<?= $user['id'] ?>')">Delete User</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
