<?php

namespace QuizArena\Tests\Support;

use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;

trait CreatesPdoMocks
{
    /**
     * @param callable(PDO&MockObject): void|null $configure
     * @return PDO&MockObject
     */
    protected function createPdoMock(?callable $configure = null): MockObject
    {
        /** @var PDO&MockObject $pdo */
        $pdo = $this->createMock(PDO::class);

        if ($configure !== null) {
            $configure($pdo);
        }

        return $pdo;
    }

    /**
     * @return PDOStatement&MockObject
     */
    protected function createStatementMock(
        mixed $fetchResult = false,
        mixed $fetchColumnResult = false,
        bool $executeReturns = true
    ): MockObject {
        /** @var PDOStatement&MockObject $stmt */
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn($executeReturns);
        $stmt->method('fetch')->willReturn($fetchResult);
        $stmt->method('fetchColumn')->willReturn($fetchColumnResult);

        return $stmt;
    }
}
