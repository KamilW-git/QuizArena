<?php

namespace QuizArena\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuizArena\Helpers\Env;

class EnvTest extends TestCase
{
    private string $envFile;

    protected function setUp(): void
    {
        $this->envFile = sys_get_temp_dir() . '/quizarena_env_test_' . uniqid('', true) . '.env';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->envFile)) {
            unlink($this->envFile);
        }
    }

    public function testLoadParsesKeyValuePairs(): void
    {
        file_put_contents($this->envFile, implode(PHP_EOL, [
            '# comment',
            'APP_ENV=testing',
            'DB_HOST=db',
            'DB_PORT=5432',
        ]));

        Env::load($this->envFile);

        $this->assertSame('testing', $_ENV['APP_ENV']);
        $this->assertSame('db', $_ENV['DB_HOST']);
        $this->assertSame('5432', $_ENV['DB_PORT']);
    }

    public function testLoadIgnoresMissingFile(): void
    {
        $missing = sys_get_temp_dir() . '/quizarena_missing_' . uniqid('', true) . '.env';

        Env::load($missing);

        $this->assertTrue(true);
    }
}
