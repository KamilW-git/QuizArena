<?php
/**
 * POST /api/game/answer.php
 *
 * Body (JSON):
 * {
 *   "session_id":    "uuid",
 *   "question_id":   "uuid",
 *   "chosen_index":  2,          // 0-3, lub -1 jeśli timeout
 *   "time_spent_ms": 8500
 * }
 *
 * Response (JSON):
 * {
 *   "correct":       true,
 *   "correct_index": 2,
 *   "points_awarded": 100
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

$body        = json_decode(file_get_contents('php://input'), true);
$sessionId   = $body['session_id']   ?? null;
$questionId  = $body['question_id']  ?? null;
$chosenIndex = $body['chosen_index'] ?? null;   // może być -1 (timeout)
$timeSpentMs = $body['time_spent_ms'] ?? 0;

if (!$sessionId || !$questionId || $chosenIndex === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    $db = Database::connect();

    // Pobierz poprawną odpowiedź dla tego pytania
    $stmt = $db->prepare('SELECT correct_answer FROM questions WHERE id = :id');
    $stmt->execute(['id' => $questionId]);
    $question = $stmt->fetch();

    if (!$question) {
        http_response_code(404);
        echo json_encode(['error' => 'Question not found']);
        exit;
    }

    $correctIndex = (int) $question['correct_answer'];
    $isTimeout    = (int) $chosenIndex === -1;
    $isCorrect    = !$isTimeout && (int) $chosenIndex === $correctIndex;
    $timeSpentSec = (int) round($timeSpentMs / 1000);

    // Prosta punktacja: 100 pkt za poprawną, 0 za złą/timeout
    $pointsAwarded = $isCorrect ? 100 : 0;

    // Zapisz odpowiedź w bazie
    $sessionModel = new GameSession($db);
    $sessionModel->saveAnswer(
        $sessionId,
        $questionId,
        $isTimeout ? 0 : (int) $chosenIndex,  // constraint: 0-3
        $isCorrect,
        $timeSpentSec
    );

    echo json_encode([
        'correct'        => $isCorrect,
        'correct_index'  => $correctIndex,
        'points_awarded' => $pointsAwarded,
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'detail' => $e->getMessage()]);
}