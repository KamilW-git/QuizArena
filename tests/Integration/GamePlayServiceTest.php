<?php

namespace QuizArena\Tests\Integration;

use QuizArena\Exceptions\GameException;
use QuizArena\Services\GamePlayService;
use QuizArena\Tests\Support\DatabaseTestCase;

class GamePlayServiceTest extends DatabaseTestCase
{
    private GamePlayService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GamePlayService($this->db);
    }

    public function testOwnerCanSubmitAnswer(): void
    {
        $owner   = $this->fixtures->createUser('owner');
        $quiz    = $this->fixtures->createPublicQuiz($owner);
        $session = $this->fixtures->startSession($owner, $quiz['quiz_id']);

        $result = $this->service->submitAnswer(
            $owner,
            $session,
            $quiz['question_id'],
            $quiz['correct_index'],
            5000
        );

        $this->assertTrue($result['correct']);
        $this->assertSame($quiz['correct_index'], $result['correct_index']);
        $this->assertSame(100, $result['points_awarded']);
    }

    public function testOtherUserCannotSubmitAnswerToForeignSession(): void
    {
        $owner   = $this->fixtures->createUser('owner2');
        $other   = $this->fixtures->createUser('other');
        $quiz    = $this->fixtures->createPublicQuiz($owner);
        $session = $this->fixtures->startSession($owner, $quiz['quiz_id']);

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Access denied');

        try {
            $this->service->submitAnswer(
                $other,
                $session,
                $quiz['question_id'],
                0,
                1000
            );
        } catch (GameException $e) {
            $this->assertSame(403, $e->statusCode);
            throw $e;
        }
    }

    public function testCannotSubmitQuestionFromDifferentQuiz(): void
    {
        $owner    = $this->fixtures->createUser('owner3');
        $quizA    = $this->fixtures->createPublicQuiz($owner, 'a');
        $quizB    = $this->fixtures->createPublicQuiz($owner, 'b');
        $session  = $this->fixtures->startSession($owner, $quizA['quiz_id']);

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Question not found in this session');

        try {
            $this->service->submitAnswer(
                $owner,
                $session,
                $quizB['question_id'],
                0,
                1000
            );
        } catch (GameException $e) {
            $this->assertSame(404, $e->statusCode);
            throw $e;
        }
    }

    public function testDuplicateAnswerIsRejected(): void
    {
        $owner   = $this->fixtures->createUser('owner4');
        $quiz    = $this->fixtures->createPublicQuiz($owner);
        $session = $this->fixtures->startSession($owner, $quiz['quiz_id']);

        $this->service->submitAnswer(
            $owner,
            $session,
            $quiz['question_id'],
            1,
            1000
        );

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Question already answered');

        try {
            $this->service->submitAnswer(
                $owner,
                $session,
                $quiz['question_id'],
                2,
                1000
            );
        } catch (GameException $e) {
            $this->assertSame(409, $e->statusCode);
            throw $e;
        }
    }

    public function testFinishGrantsXpOnlyOnce(): void
    {
        $owner   = $this->fixtures->createUser('owner5');
        $quiz    = $this->fixtures->createPublicQuiz($owner);
        $session = $this->fixtures->startSession($owner, $quiz['quiz_id']);

        $this->service->submitAnswer(
            $owner,
            $session,
            $quiz['question_id'],
            $quiz['correct_index'],
            2000
        );

        $xpBefore = $this->userXp($owner);

        $first = $this->service->finishSession($owner, $session);
        $xpAfterFirst = $this->userXp($owner);

        $this->assertGreaterThan(0, $first['xp_earned']);
        $this->assertGreaterThan($xpBefore, $xpAfterFirst);
        $this->assertGreaterThanOrEqual($xpBefore + $first['xp_earned'], $xpAfterFirst);

        $second = $this->service->finishSession($owner, $session);
        $xpAfterSecond = $this->userXp($owner);

        $this->assertTrue($second['already_finished']);
        $this->assertSame(0, $second['xp_earned']);
        $this->assertSame($xpAfterFirst, $xpAfterSecond);
    }

    public function testOtherUserCannotFinishForeignSession(): void
    {
        $owner   = $this->fixtures->createUser('owner6');
        $other   = $this->fixtures->createUser('other6');
        $quiz    = $this->fixtures->createPublicQuiz($owner);
        $session = $this->fixtures->startSession($owner, $quiz['quiz_id']);

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Access denied');

        try {
            $this->service->finishSession($other, $session);
        } catch (GameException $e) {
            $this->assertSame(403, $e->statusCode);
            throw $e;
        }
    }

    public function testCannotAnswerAfterSessionFinished(): void
    {
        $owner   = $this->fixtures->createUser('owner7');
        $quiz    = $this->fixtures->createPublicQuiz($owner);
        $session = $this->fixtures->startSession($owner, $quiz['quiz_id']);

        $this->service->submitAnswer(
            $owner,
            $session,
            $quiz['question_id'],
            $quiz['correct_index'],
            1000
        );
        $this->service->finishSession($owner, $session);

        $this->expectException(GameException::class);
        $this->expectExceptionMessage('Session already finished');

        try {
            $this->service->submitAnswer(
                $owner,
                $session,
                $quiz['question_id'],
                0,
                1000
            );
        } catch (GameException $e) {
            $this->assertSame(409, $e->statusCode);
            throw $e;
        }
    }

    public function testRequireOwnedSessionBlocksForeignAccess(): void
    {
        $owner   = $this->fixtures->createUser('owner8');
        $other   = $this->fixtures->createUser('other8');
        $quiz    = $this->fixtures->createPublicQuiz($owner);
        $session = $this->fixtures->startSession($owner, $quiz['quiz_id']);

        $this->service->requireOwnedSession($owner, $session);

        $this->expectException(GameException::class);

        try {
            $this->service->requireOwnedSession($other, $session);
        } catch (GameException $e) {
            $this->assertSame(403, $e->statusCode);
            throw $e;
        }
    }
}
