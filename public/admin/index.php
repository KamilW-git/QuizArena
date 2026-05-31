<?php
require_once __DIR__ . '/layout/header.php';

use QuizArena\Config\Database;
use QuizArena\Controllers\AdminController;

$db = Database::connect();
$adminController = new AdminController($db);

$stats = $adminController->getDashboardStats();
?>

<h1>Dashboard</h1>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
    <div class="stat-card">
        <h3>Total Users</h3>
        <div class="value"><?= $stats['totalUsers'] ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Quizzes</h3>
        <div class="value"><?= $stats['totalQuizzes'] ?></div>
    </div>
</div>

<h2>Recent Admin Activity</h2>
<table>
    <thead>
        <tr>
            <th>Time</th>
            <th>Admin</th>
            <th>Action</th>
            <th>Target Type</th>
            <th>Target ID</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($stats['recentLogs'] as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['created_at']) ?></td>
                <td><?= htmlspecialchars($log['admin_username']) ?></td>
                <td><span class="badge"><?= htmlspecialchars($log['action']) ?></span></td>
                <td><?= htmlspecialchars($log['target_type']) ?></td>
                <td><?= htmlspecialchars($log['target_id'] ?? '-') ?></td>
                <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($stats['recentLogs'])): ?>
            <tr>
                <td colspan="6" style="text-align: center;">No recent activity</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
