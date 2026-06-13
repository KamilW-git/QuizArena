<?php

namespace QuizArena\Tests\Unit;

use QuizArena\Exceptions\GameException;
use QuizArena\Models\GameSession;
use QuizArena\Tests\Support\PdoTestCase;

class GameSessionTest extends PdoTestCase
{
    public function testRequireOwnedSessionReturnsSessionForOwner(): void
    {
        $sessionRow = $this->sessionRow(['is_finished' => false]);
        $pdo        = $this->createPdoMock(function ($pdo) use ($sessionRow) {
            $pdo->method('prepare')->willReturn($this->createStatementMock($sessionRow));
        });

        $result = (new GameSession($pdo))->requireOwnedSession('session-1', 'user-1');

        $this->assertSame('session-1', $result['id']);
        $this->assertSame('user-1', $result['user_id']);
    }

    public function testRequireOwnedSessionThrows404WhenMissing(): void
    {
        $pdo = $this->createPdoMock(function ($pdo) {
            $pdo->method('prepare')->willReturn($this->createStatementMock(false));
        });

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Session not found');

        try {
            (new GameSession($pdo))->requireOwnedSession('missing', 'user-1');
        } catch (GameException $e) {
            $this->assertSame(404, $e->statusCode);
            throw $e;
        }
    }

    public function testRequireOwnedSessionThrows403ForForeignSession(): void
    {
        $pdo = $this->createPdoMock(function ($pdo) {
            $pdo->method('prepare')->willReturn($this->createStatementMock(
                $this->sessionRow(['user_id' => 'other-user'])
            ));
        });

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Access denied');

        try {
            (new GameSession($pdo))->requireOwnedSession('session-1', 'user-1');
        } catch (GameException $e) {
            $this->assertSame(403, $e->statusCode);
            throw $e;
        }
    }

    public function testRequireActiveSessionThrows409WhenFinished(): void
    {
        $pdo = $this->createPdoMock(function ($pdo) {
            $pdo->method('prepare')->willReturn($this->createStatementMock(
                $this->sessionRow(['is_finished' => true])
            ));
        });

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Session already finished');

        try {
            (new GameSession($pdo))->requireActiveSession('session-1', 'user-1');
        } catch (GameException $e) {
            $this->assertSame(409, $e->statusCode);
            throw $e;
        }
    }

    public function testRequireQuestionInSessionReturnsQuestionWhenInQuiz(): void
    {
        $pdo = $this->createPdoMock(function ($pdo) {
            $pdo->method('prepare')->willReturn($this->createStatementMock([
                'id'             => 'question-1',
                'correct_answer' => 2,
            ]));
        });

        $question = (new GameSession($pdo))->requireQuestionInSession(
            'session-1',
            'quiz-1',
            'question-1'
        );

        $this->assertSame('question-1', $question['id']);
        $this->assertSame(2, (int) $question['correct_answer']);
    }

    public function testRequireQuestionInSessionThrows404WhenNotInQuiz(): void
    {
        $pdo = $this->createPdoMock(function ($pdo) {
            $pdo->method('prepare')->willReturn($this->createStatementMock(false));
        });

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Question not found in this session');

        try {
            (new GameSession($pdo))->requireQuestionInSession('session-1', 'quiz-1', 'question-x');
        } catch (GameException $e) {
            $this->assertSame(404, $e->statusCode);
            throw $e;
        }
    }

    public function testHasAnsweredQuestionReturnsTrueWhenAnswerExists(): void
    {
        $pdo = $this->createPdoMock(function ($pdo) {
            $pdo->method('prepare')->willReturn($this->createStatementMock(fetchColumnResult: '1'));
        });

        $this->assertTrue(
            (new GameSession($pdo))->hasAnsweredQuestion('session-1', 'question-1')
        );
    }

    public function testSaveAnswerThrows409WhenQuestionAlreadyAnswered(): void
    {
        $pdo = $this->createPdoMock(function ($pdo) {
            $pdo->method('prepare')->willReturn($this->createStatementMock(fetchColumnResult: '1'));
        });

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Question already answered');

        try {
            (new GameSession($pdo))->saveAnswer('session-1', 'question-1', 0, true, 5);
        } catch (GameException $e) {
            $this->assertSame(409, $e->statusCode);
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function sessionRow(array $overrides = []): array
    {
        return array_merge([
            'id'            => 'session-1',
            'user_id'       => 'user-1',
            'quiz_id'       => 'quiz-1',
            'score'         => 0,
            'correct_count' => 0,
            'time_taken'    => 0,
            'is_finished'   => false,
        ], $overrides);
    }
}
