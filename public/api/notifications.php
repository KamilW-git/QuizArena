<?php
/**
 * GET /api/notifications.php
 * Fetches recent notifications for the logged in user.
 */
require_once __DIR__ . '/../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

header('Content-Type: application/json');

Env::load(__DIR__ . '/../../.env');
Auth::start();

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = Auth::user();

try {
    $db = Database::connect();
    
    $stmt = $db->prepare('
        SELECT id, title, message, type, is_read, created_at
        FROM notifications
        WHERE user_id = :user_id
        ORDER BY created_at DESC
        LIMIT 20
    ');
    $stmt->execute(['user_id' => $user['id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = FALSE');
    $stmt->execute(['user_id' => $user['id']]);
    $unreadCount = (int) $stmt->fetchColumn();

    echo json_encode([
        'unread_count'  => $unreadCount,
        'notifications' => $notifications
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
