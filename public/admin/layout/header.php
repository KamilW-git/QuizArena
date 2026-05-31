<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../../../.env');
Auth::requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — QuizArena</title>
    <link rel="stylesheet" href="/assets/css/main.css?v=2">
    <style>
        .admin-nav {
            background-color: var(--color-surface);
            padding: 1rem 2rem;
            display: flex;
            gap: 1.5rem;
            border-bottom: 1px solid var(--color-border);
            margin-bottom: 2rem;
        }
        .admin-nav a {
            color: var(--color-text);
            text-decoration: none;
            font-weight: 500;
        }
        .admin-nav a:hover, .admin-nav a.active {
            color: var(--color-primary);
        }
        .admin-content {
            padding: 0 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .stat-card {
            background-color: var(--color-surface);
            padding: 2rem;
            border-radius: var(--radius-md);
            text-align: center;
            border: 1px solid var(--color-border);
        }
        .stat-card h3 {
            margin: 0 0 1rem 0;
            color: var(--color-text-muted);
        }
        .stat-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--color-primary);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        table th, table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--color-border);
        }
        table th {
            background-color: var(--color-background);
            color: var(--color-text-muted);
        }
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge.active, .badge.published { background: #10b98120; color: #10b981; }
        .badge.suspended { background: #f59e0b20; color: #f59e0b; }
        .badge.banned { background: #ef444420; color: #ef4444; }
        .badge.hidden { background: #6b728020; color: #6b7280; }
        .badge.admin { background: #8b5cf620; color: #8b5cf6; }
        .badge.user { background: #3b82f620; color: #3b82f6; }
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .search-bar input {
            flex-grow: 1;
        }
    </style>
</head>
<body>
    <nav class="admin-nav">
        <strong>QuizArena Admin</strong>
        <a href="/admin/index.php">Dashboard</a>
        <a href="/admin/users.php">Users</a>
        <a href="/admin/quizzes.php">Quizzes</a>
        <a href="/dashboard.php" style="margin-left: auto;">Back to App</a>
    </nav>
    <div class="admin-content">
