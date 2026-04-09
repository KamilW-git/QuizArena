<?php

namespace QuizArena\Models;

use PDO;
use QuizArena\Models\Achievement;

class GameSession
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Utwórz nową sesję gry i zwróć jej UUID.
     */
    public function create(string $userId, string $quizId): string
    {
        $stmt = $this->db->prepare('
            INSERT INTO game_sessions (user_id, quiz_id)
            VALUES (:user_id, :quiz_id)
            RETURNING id
        ');
        $stmt->execute([
            'user_id' => $userId,
            'quiz_id' => $quizId,
        ]);

        return $stmt->fetchColumn();
    }

    /**
     * Zapisz odpowiedź gracza na jedno pytanie.
     *
     * @param string $sessionId  UUID sesji
     * @param string $questionId UUID pytania
     * @param int    $chosenIndex Indeks wybranej odpowiedzi (0-3), -1 = timeout
     * @param bool   $isCorrect  Czy odpowiedź była poprawna
     * @param int    $timeSpent  Czas w sekundach
     */
    public function saveAnswer(
        string $sessionId,
        string $questionId,
        int    $chosenIndex,
        bool   $isCorrect,
        int    $timeSpent
    ): void {
        // chosen_index BETWEEN 0 AND 3 — przy timeout wstawiamy 0 (constraint wymaga)
        $safeIndex = max(0, min(3, $chosenIndex));

        $stmt = $this->db->prepare('
            INSERT INTO game_answers (session_id, question_id, chosen_index, is_correct, time_spent)
            VALUES (:session_id, :question_id, :chosen_index, :is_correct, :time_spent)
        ');
        $stmt->execute([
            'session_id'   => $sessionId,
            'question_id'  => $questionId,
            'chosen_index' => $safeIndex,
            'is_correct'   => $isCorrect ? 'true' : 'false',
            'time_spent'   => $timeSpent,
        ]);
    }

    /**
     * Zakończ sesję — wylicz score, correct_count, time_taken i zapisz.
     * Zwraca finalny wynik.
     */
    public function finish(string $sessionId): array
    {
        // Pobierz wszystkie odpowiedzi tej sesji
        $stmt = $this->db->prepare('
            SELECT is_correct, time_spent
            FROM game_answers
            WHERE session_id = :session_id
        ');
        $stmt->execute(['session_id' => $sessionId]);
        $answers = $stmt->fetchAll();

        $correctCount = 0;
        $totalTime    = 0;
        foreach ($answers as $a) {
            if ($a['is_correct']) $correctCount++;
            $totalTime += (int) $a['time_spent'];
        }

        $totalQuestions = count($answers);
        $accuracy       = $totalQuestions > 0 ? $correctCount / $totalQuestions : 0;

        // Prosta formuła XP: 100 za pytanie * accuracy + bonus za szybkość
        $baseXp   = (int) round(100 * $correctCount);
        $speedXp  = $totalTime > 0 ? (int) max(0, 50 - $totalTime) : 0;
        $xpEarned = $baseXp + $speedXp;
        $score    = $baseXp; // score = punkty bez bonusu za szybkość

        // Zaktualizuj sesję
        $stmt = $this->db->prepare('
            UPDATE game_sessions
            SET score         = :score,
                correct_count = :correct_count,
                time_taken    = :time_taken,
                completed_at  = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            'score'         => $score,
            'correct_count' => $correctCount,
            'time_taken'    => $totalTime,
            'id'            => $sessionId,
        ]);

        // Dodaj XP użytkownikowi
        $stmt = $this->db->prepare('
            UPDATE users u
            SET xp = u.xp + :xp
            FROM game_sessions gs
            WHERE gs.id = :session_id
              AND u.id  = gs.user_id
        ');
        $stmt->execute([
            'xp'         => $xpEarned,
            'session_id' => $sessionId,
        ]);

        // Pobierz user_id tej sesji
        $stmt = $this->db->prepare('SELECT user_id FROM game_sessions WHERE id = :id');
        $stmt->execute(['id' => $sessionId]);
        $userId = $stmt->fetchColumn();

         // Sprawdź i przyznaj achievementy
        $achievement = new Achievement($this->db);
        $newlyUnlocked = $achievement->checkAndAward($userId);

        return [
            'score'          => $score,
            'correct_count'  => $correctCount,
            'total_questions'=> $totalQuestions,
            'xp_earned'      => $xpEarned,
            'accuracy'       => round($accuracy * 100),
            'achievements'    => $newlyUnlocked,
        ];
    }

    /**
     * Pobierz sesję po ID (do results.php).
     */
    public static function findById(PDO $db, string $sessionId): ?array
    {
        $stmt = $db->prepare('
            SELECT gs.*, q.title as quiz_title, q.category
            FROM game_sessions gs
            JOIN quizzes q ON q.id = gs.quiz_id
            WHERE gs.id = :id
        ');
        $stmt->execute(['id' => $sessionId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}