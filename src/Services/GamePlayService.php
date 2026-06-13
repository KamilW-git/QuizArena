<?php

namespace QuizArena\Services;

use PDO;
use QuizArena\Exceptions\GameException;
use QuizArena\Models\GameSession;

class GamePlayService
{
    private GameSession $sessions;

    public function __construct(PDO $db, ?GameSession $sessions = null)
    {
        $this->sessions = $sessions ?? new GameSession($db);
    }

    /**
     * @return array{correct: bool, correct_index: int, points_awarded: int}
     * @throws GameException
     */
    public function submitAnswer(
        string $userId,
        string $sessionId,
        string $questionId,
        int $chosenIndex,
        int $timeSpentMs
    ): array {
        $session  = $this->sessions->requireActiveSession($sessionId, $userId);
        $question = $this->sessions->requireQuestionInSession(
            $sessionId,
            $session['quiz_id'],
            $questionId
        );

        $correctIndex = (int) $question['correct_answer'];
        $isTimeout    = $chosenIndex === -1;
        $isCorrect    = !$isTimeout && $chosenIndex === $correctIndex;
        $timeSpentSec = (int) round($timeSpentMs / 1000);
        $pointsAwarded = $isCorrect ? 100 : 0;

        $this->sessions->saveAnswer(
            $sessionId,
            $questionId,
            $isTimeout ? 0 : $chosenIndex,
            $isCorrect,
            $timeSpentSec
        );

        return [
            'correct'        => $isCorrect,
            'correct_index'  => $correctIndex,
            'points_awarded' => $pointsAwarded,
        ];
    }

    /**
     * @throws GameException
     */
    public function finishSession(string $userId, string $sessionId): array
    {
        return $this->sessions->finish($sessionId, $userId);
    }

    /**
     * @throws GameException
     */
    public function requireOwnedSession(string $userId, string $sessionId): array
    {
        return $this->sessions->requireOwnedSession($sessionId, $userId);
    }
}
