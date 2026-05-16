<?php
/**
 * POST /api/quiz/rate.php
 *
 * Body (JSON):
 * {
 *   "quiz_id": "uuid",
 *   "rating":  5
 * }
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

$body   = json_decode(file_get_contents('php://input'), true);
$quizId = $body['quiz_id'] ?? null;
$rating = (int) ($body['rating'] ?? 0);

if (!$quizId || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid quiz_id or rating (1-5 required)']);
    exit;
}

$user = Auth::user();

try {
    $db = Database::connect();

    // Używamy UPSERT, aby pozwolić użytkownikowi zmienić zdanie (ocenę) w przyszłości
    $stmt = $db->prepare('
        INSERT INTO quiz_ratings (user_id, quiz_id, rating)
        VALUES (:user_id, :quiz_id, :rating)
        ON CONFLICT (user_id, quiz_id) 
        DO UPDATE SET rating = EXCLUDED.rating, created_at = NOW()
    ');
    
    $stmt->execute([
        'user_id' => $user['id'],
        'quiz_id' => $quizId,
        'rating'  => $rating
    ]);

    echo json_encode(['success' => true, 'message' => 'Rating saved successfully']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'detail' => $e->getMessage()]);
}
