<?php

namespace QuizArena\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuizArena\Exceptions\GameException;

class GameExceptionTest extends TestCase
{
    public function testStoresStatusCodeAndMessage(): void
    {
        $exception = new GameException('Access denied', 403);

        $this->assertSame('Access denied', $exception->getMessage());
        $this->assertSame(403, $exception->statusCode);
    }

    public function testDefaultsToBadRequestStatusCode(): void
    {
        $exception = new GameException('Invalid input');

        $this->assertSame(400, $exception->statusCode);
    }
}
