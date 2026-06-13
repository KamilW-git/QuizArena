<?php

namespace QuizArena\Models;

use PDO;
use QuizArena\Exceptions\GameException;
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
     * Pobierz sesję i zweryfikuj, że należy do podanego użytkownika.
     *
     * @throws GameException 404 gdy brak sesji, 403 gdy cudza sesja
     */
    public function requireOwnedSession(string $sessionId, string $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, user_id, quiz_id, score, correct_count, time_taken, is_finished
            FROM game_sessions
            WHERE id = :id
        ');
        $stmt->execute(['id' => $sessionId]);
        $session = $stmt->fetch();

        if (!$session) {
            throw new GameException('Session not found', 404);
        }

        if ($session['user_id'] !== $userId) {
            throw new GameException('Access denied', 403);
        }

        return $session;
    }

    /**
     * @throws GameException 409 gdy sesja jest już zakończona
     */
    public function requireActiveSession(string $sessionId, string $userId): array
    {
        $session = $this->requireOwnedSession($sessionId, $userId);

        if (self::toBool($session['is_finished'])) {
            throw new GameException('Session already finished', 409);
        }

        return $session;
    }

    /**
     * @throws GameException 404 gdy pytanie nie należy do quizu sesji
     */
    public function requireQuestionInSession(string $sessionId, string $quizId, string $questionId): array
    {
        $stmt = $this->db->prepare('
            SELECT id, correct_answer
            FROM questions
            WHERE id = :id AND quiz_id = :quiz_id
        ');
        $stmt->execute([
            'id'      => $questionId,
            'quiz_id' => $quizId,
        ]);
        $question = $stmt->fetch();

        if (!$question) {
            throw new GameException('Question not found in this session', 404);
        }

        return $question;
    }

    public function hasAnsweredQuestion(string $sessionId, string $questionId): bool
    {
        $stmt = $this->db->prepare('
            SELECT 1 FROM game_answers
            WHERE session_id = :session_id AND question_id = :question_id
            LIMIT 1
        ');
        $stmt->execute([
            'session_id'  => $sessionId,
            'question_id' => $questionId,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Zapisz odpowiedź gracza na jedno pytanie.
     *
     * @throws GameException 409 gdy pytanie już zostało udzielone
     */
    public function saveAnswer(
        string $sessionId,
        string $questionId,
        int    $chosenIndex,
        bool   $isCorrect,
        int    $timeSpent
    ): void {
        if ($this->hasAnsweredQuestion($sessionId, $questionId)) {
            throw new GameException('Question already answered', 409);
        }

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
     * Idempotentne: ponowne wywołanie nie przyznaje XP ponownie.
     */
    public function finish(string $sessionId, string $userId): array
    {
        $session = $this->requireOwnedSession($sessionId, $userId);

        if (self::toBool($session['is_finished'])) {
            return $this->buildFinishResult($sessionId, $session, xpEarned: 0, achievements: []);
        }

        $ownsTransaction = !$this->db->inTransaction();
        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $stmt = $this->db->prepare('
                SELECT is_finished FROM game_sessions
                WHERE id = :id FOR UPDATE
            ');
            $stmt->execute(['id' => $sessionId]);
            $locked = $stmt->fetch();

            if (!$locked || self::toBool($locked['is_finished'])) {
                if ($ownsTransaction) {
                    $this->db->rollBack();
                }
                $session = $this->requireOwnedSession($sessionId, $userId);
                return $this->buildFinishResult($sessionId, $session, xpEarned: 0, achievements: []);
            }

            $result = $this->finalizeSession($sessionId, $session['user_id']);

            if ($ownsTransaction) {
                $this->db->commit();
            }

            return $result;
        } catch (\Throwable $e) {
            if ($ownsTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function finalizeSession(string $sessionId, string $userId): array
    {
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
            if ($a['is_correct']) {
                $correctCount++;
            }
            $totalTime += (int) $a['time_spent'];
        }

        $totalQuestions = count($answers);
        $accuracy       = $totalQuestions > 0 ? $correctCount / $totalQuestions : 0;

        $baseXp   = (int) round(100 * $correctCount);
        $speedXp  = $totalTime > 0 ? (int) max(0, 50 - $totalTime) : 0;
        $xpEarned = $baseXp + $speedXp;
        $score    = $baseXp;

        $stmt = $this->db->prepare('SELECT xp FROM users WHERE id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $oldXp    = (int) $stmt->fetchColumn();
        $oldLevel = max(1, (int) floor($oldXp / 1000) + 1);
        $newLevel = max(1, (int) floor(($oldXp + $xpEarned) / 1000) + 1);

        $stmt = $this->db->prepare('
            UPDATE game_sessions
            SET score         = :score,
                correct_count = :correct_count,
                time_taken    = :time_taken,
                is_finished   = TRUE,
                completed_at  = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            'score'         => $score,
            'correct_count' => $correctCount,
            'time_taken'    => $totalTime,
            'id'            => $sessionId,
        ]);

        $stmt = $this->db->prepare('
            UPDATE users
            SET xp = xp + :xp
            WHERE id = :user_id
        ');
        $stmt->execute([
            'xp'      => $xpEarned,
            'user_id' => $userId,
        ]);

        if ($newLevel > $oldLevel) {
            $stmt = $this->db->prepare('
                INSERT INTO notifications (user_id, title, message, type)
                VALUES (:user_id, :title, :message, :type)
            ');
            $stmt->execute([
                'user_id' => $userId,
                'title'   => 'Level Up! 🎉',
                'message' => "Gratulacje! Wbiłeś $newLevel poziom!",
                'type'    => 'level_up',
            ]);
        }

        $achievement     = new Achievement($this->db);
        $newlyUnlocked   = $achievement->checkAndAward($userId);

        $session = $this->requireOwnedSession($sessionId, $userId);

        return $this->buildFinishResult($sessionId, $session, $xpEarned, $newlyUnlocked, $accuracy, $totalQuestions);
    }

    private function buildFinishResult(
        string $sessionId,
        array $session,
        int $xpEarned,
        array $achievements,
        ?float $accuracy = null,
        ?int $totalQuestions = null
    ): array {
        if ($accuracy === null || $totalQuestions === null) {
            $stmt = $this->db->prepare('
                SELECT COUNT(*) AS total, SUM(CASE WHEN is_correct THEN 1 ELSE 0 END) AS correct
                FROM game_answers
                WHERE session_id = :session_id
            ');
            $stmt->execute(['session_id' => $sessionId]);
            $stats          = $stmt->fetch();
            $totalQuestions = (int) $stats['total'];
            $correctCount   = (int) $stats['correct'];
            $accuracy       = $totalQuestions > 0 ? $correctCount / $totalQuestions : 0;
        } else {
            $correctCount = (int) $session['correct_count'];
        }

        return [
            'score'           => (int) $session['score'],
            'correct_count'   => (int) $session['correct_count'],
            'total_questions' => $totalQuestions,
            'xp_earned'       => $xpEarned,
            'accuracy'        => (int) round($accuracy * 100),
            'achievements'    => $achievements,
            'already_finished'=> self::toBool($session['is_finished']) && $xpEarned === 0,
        ];
    }

    private static function toBool(mixed $value): bool
    {
        return $value === true || $value === 't' || $value === 1 || $value === '1';
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
