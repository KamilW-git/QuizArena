<?php

namespace QuizArena\Tests\Unit;

use QuizArena\Exceptions\GameException;
use QuizArena\Models\GameSession;
use QuizArena\Models\User;
use QuizArena\Services\GamePlayService;
use QuizArena\Tests\Support\PdoTestCase;

class GamePlayServiceUnitTest extends PdoTestCase
{
    public function testSubmitAnswerReturnsCorrectResultForValidAnswer(): void
    {
        $sessions = $this->createMock(GameSession::class);
        $sessions->method('requireActiveSession')->willReturn([
            'id'            => 'session-1',
            'user_id'       => 'user-1',
            'quiz_id'       => 'quiz-1',
            'score'         => 0,
            'correct_count' => 0,
            'time_taken'    => 0,
            'is_finished'   => false,
        ]);
        $sessions->method('requireQuestionInSession')->willReturn([
            'id'             => 'question-1',
            'correct_answer' => 2,
        ]);
        $sessions->expects($this->once())->method('saveAnswer')->with(
            'session-1',
            'question-1',
            2,
            true,
            5
        );

        $service = new GamePlayService($this->createPdoMock(), $sessions);
        $result  = $service->submitAnswer('user-1', 'session-1', 'question-1', 2, 5000);

        $this->assertTrue($result['correct']);
        $this->assertSame(2, $result['correct_index']);
        $this->assertSame(100, $result['points_awarded']);
    }

    public function testSubmitAnswerReturnsZeroPointsForWrongAnswer(): void
    {
        $sessions = $this->createMock(GameSession::class);
        $sessions->method('requireActiveSession')->willReturn([
            'id'            => 'session-1',
            'user_id'       => 'user-1',
            'quiz_id'       => 'quiz-1',
            'score'         => 0,
            'correct_count' => 0,
            'time_taken'    => 0,
            'is_finished'   => false,
        ]);
        $sessions->method('requireQuestionInSession')->willReturn([
            'id'             => 'question-1',
            'correct_answer' => 2,
        ]);
        $sessions->expects($this->once())->method('saveAnswer')->with(
            'session-1',
            'question-1',
            0,
            false,
            3
        );

        $service = new GamePlayService($this->createPdoMock(), $sessions);
        $result  = $service->submitAnswer('user-1', 'session-1', 'question-1', 0, 3000);

        $this->assertFalse($result['correct']);
        $this->assertSame(0, $result['points_awarded']);
    }

    public function testSubmitAnswerTreatsTimeoutAsIncorrect(): void
    {
        $sessions = $this->createMock(GameSession::class);
        $sessions->method('requireActiveSession')->willReturn([
            'id'            => 'session-1',
            'user_id'       => 'user-1',
            'quiz_id'       => 'quiz-1',
            'score'         => 0,
            'correct_count' => 0,
            'time_taken'    => 0,
            'is_finished'   => false,
        ]);
        $sessions->method('requireQuestionInSession')->willReturn([
            'id'             => 'question-1',
            'correct_answer' => 1,
        ]);
        $sessions->expects($this->once())->method('saveAnswer')->with(
            'session-1',
            'question-1',
            0,
            false,
            30
        );

        $service = new GamePlayService($this->createPdoMock(), $sessions);
        $result  = $service->submitAnswer('user-1', 'session-1', 'question-1', -1, 30000);

        $this->assertFalse($result['correct']);
        $this->assertSame(0, $result['points_awarded']);
    }

    public function testSubmitAnswerPropagatesAuthorizationErrors(): void
    {
        $sessions = $this->createMock(GameSession::class);
        $sessions->method('requireActiveSession')->willThrowException(
            new GameException('Access denied', 403)
        );

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Access denied');

        (new GamePlayService($this->createPdoMock(), $sessions))
            ->submitAnswer('user-1', 'foreign-session', 'question-1', 0, 1000);
    }

    public function testFinishSessionDelegatesToGameSessionModel(): void
    {
        $sessions = $this->createMock(GameSession::class);
        $sessions->expects($this->once())
            ->method('finish')
            ->with('session-1', 'user-1')
            ->willReturn([
                'score'            => 100,
                'correct_count'    => 1,
                'total_questions'  => 1,
                'xp_earned'        => 148,
                'accuracy'         => 100,
                'achievements'     => [],
                'already_finished' => false,
            ]);

        $result = (new GamePlayService($this->createPdoMock(), $sessions))
            ->finishSession('user-1', 'session-1');

        $this->assertSame(100, $result['score']);
        $this->assertSame(148, $result['xp_earned']);
    }
}
