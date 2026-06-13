<?php

namespace QuizArena\Tests\Support;

use PDO;
use PHPUnit\Framework\TestCase;
use QuizArena\Config\Database;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $db;
    protected Fixtures $fixtures;

    protected function setUp(): void
    {
        $this->db       = Database::connect();
        $this->fixtures = new Fixtures($this->db);
        $this->fixtures->ensureSchema();
        $this->db->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    protected function userXp(string $userId): int
    {
        $stmt = $this->db->prepare('SELECT xp FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);

        return (int) $stmt->fetchColumn();
    }
}
