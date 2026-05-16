<?php
/**
 * POST /api/notifications/read.php
 * Marks all notifications as read.
 */
require_once __DIR__ . '/../../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

header('Content-Type: application/json');

Env::load(__DIR__ . '/../../../.env');
Auth::start();

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user = Auth::user();

try {
    $db = Database::connect();
    
    $stmt = $db->prepare('UPDATE notifications SET is_read = TRUE WHERE user_id = :user_id AND is_read = FALSE');
    $stmt->execute(['user_id' => $user['id']]);

    echo json_encode(['success' => true]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
