<?php

namespace QuizArena\Tests\Unit;

use QuizArena\Models\User;
use QuizArena\Tests\Support\PdoTestCase;

class UserTest extends PdoTestCase
{
    public function testVerifyPasswordMatchesHashedPassword(): void
    {
        $hash = password_hash('secret-password', PASSWORD_BCRYPT);
        $user = new User($this->createPdoMock());

        $this->assertTrue($user->verifyPassword('secret-password', $hash));
        $this->assertFalse($user->verifyPassword('wrong-password', $hash));
    }
}
