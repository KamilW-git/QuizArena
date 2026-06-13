<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Exceptions\GameException;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;
use QuizArena\Services\GamePlayService;

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

$body        = json_decode(file_get_contents('php://input'), true);
$sessionId   = $body['session_id']   ?? null;
$questionId  = $body['question_id']  ?? null;
$chosenIndex = $body['chosen_index'] ?? null;
$timeSpentMs = $body['time_spent_ms'] ?? 0;

if (!$sessionId || !$questionId || $chosenIndex === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    $db      = Database::connect();
    $user    = Auth::user();
    $service = new GamePlayService($db);

    $result = $service->submitAnswer(
        $user['id'],
        $sessionId,
        $questionId,
        (int) $chosenIndex,
        (int) $timeSpentMs
    );

    echo json_encode($result);
} catch (GameException $e) {
    http_response_code($e->statusCode);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
