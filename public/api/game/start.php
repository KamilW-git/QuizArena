<?php
/**
 * POST /api/game/start.php
 *
 * Body (JSON): { "quiz_id": "uuid" }
 *
 * Response (JSON):
 * {
 *   "session_id": "uuid",
 *   "questions": [
 *     {
 *       "id":       "uuid",
 *       "text":     "Pytanie...",
 *       "category": "Geografia",
 *       "time_limit": 30,
 *       "answers": [
 *         { "index": 0, "text": "Odpowiedź A" },
 *         ...
 *       ]
 *     }
 *   ]
 * }
 *
 * WAŻNE: NIE zwracamy correct_answer — to zostaje na serwerze.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;
use QuizArena\Models\GameSession;

header('Content-Type: application/json');

Env::load(__DIR__ . '/../../../.env');
Auth::start();

// Tylko zalogowani
if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Odczytaj JSON body
$body   = json_decode(file_get_contents('php://input'), true);
$quizId = $body['quiz_id'] ?? null;

if (!$quizId || !is_string($quizId)) {
    http_response_code(400);
    echo json_encode(['error' => 'quiz_id is required']);
    exit;
}

try {
    $db   = Database::connect();
    $user = Auth::user();

    // Sprawdź czy quiz istnieje i jest publiczny
    $stmt = $db->prepare('SELECT id, category FROM quizzes WHERE id = :id AND is_public = true');
    $stmt->execute(['id' => $quizId]);
    $quiz = $stmt->fetch();

    if (!$quiz) {
        http_response_code(404);
        echo json_encode(['error' => 'Quiz not found']);
        exit;
    }

    // Utwórz sesję gry
    $sessionModel = new GameSession($db);
    $sessionId    = $sessionModel->create($user['id'], $quizId);

    // Pobierz pytania (bez correct_answer!)
    $stmt = $db->prepare('
        SELECT id, content, time_limit, order_index
        FROM questions
        WHERE quiz_id = :quiz_id
        ORDER BY order_index ASC
    ');
    $stmt->execute(['quiz_id' => $quizId]);
    $rawQuestions = $stmt->fetchAll();

    if (empty($rawQuestions)) {
        http_response_code(422);
        echo json_encode(['error' => 'This quiz has no questions']);
        exit;
    }

    // Dla każdego pytania pobierz odpowiedzi (tylko tekst + index, BEZ oznaczenia poprawnej)
    $questions = [];
    foreach ($rawQuestions as $q) {
        $stmt = $db->prepare('
            SELECT index, content
            FROM answers
            WHERE question_id = :question_id
            ORDER BY index ASC
        ');
        $stmt->execute(['question_id' => $q['id']]);
        $answers = $stmt->fetchAll();

        $questions[] = [
            'id'         => $q['id'],
            'text'       => $q['content'],
            'category'   => $quiz['category'],
            'time_limit' => (int) $q['time_limit'],
            'answers'    => array_map(fn($a) => [
                'index' => (int) $a['index'],
                'text'  => $a['content'],
            ], $answers),
        ];
    }

    echo json_encode([
        'session_id' => $sessionId,
        'questions'  => $questions,
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'detail' => $e->getMessage()]);
}