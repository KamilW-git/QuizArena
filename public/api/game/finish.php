<?php
/**
 * POST /api/game/finish.php
 *
 * Body (JSON): { "session_id": "uuid" }
 *
 * Response (JSON):
 * {
 *   "score":           300,
 *   "correct_count":   3,
 *   "total_questions": 5,
 *   "xp_earned":       350,
 *   "accuracy":        60,
 *   "redirect_url":    "/quiz/results.php?session=uuid"
 * }
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;
use QuizArena\Models\GameSession;

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

$body      = json_decode(file_get_contents('php://input'), true);
$sessionId = $body['session_id'] ?? null;

if (!$sessionId) {
    http_response_code(400);
    echo json_encode(['error' => 'session_id is required']);
    exit;
}

try {
    $db           = Database::connect();
    $sessionModel = new GameSession($db);
    $result       = $sessionModel->finish($sessionId);

    echo json_encode(array_merge($result, [
        'redirect_url' => '/quiz/results.php?session=' . urlencode($sessionId),
    ]));

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'detail' => $e->getMessage()]);
}