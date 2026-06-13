<?php

namespace QuizArena\Tests\Unit;

use QuizArena\Controllers\AuthController;
use QuizArena\Tests\Support\PdoTestCase;

class AuthControllerValidationTest extends PdoTestCase
{
    private AuthController $controller;

    protected function setUp(): void
    {
        $this->controller = new AuthController($this->createPdoMock());
    }

    public function testRegisterRejectsShortUsername(): void
    {
        $result = $this->controller->register([
            'username'         => 'ab',
            'email'            => 'valid@test.local',
            'password'         => 'password1',
            'password_confirm' => 'password1',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('Username must be at least 3 characters', $result['errors']['username']);
    }

    public function testRegisterRejectsInvalidEmail(): void
    {
        $result = $this->controller->register([
            'username'         => 'validuser',
            'email'            => 'not-an-email',
            'password'         => 'password1',
            'password_confirm' => 'password1',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('Valid email is required', $result['errors']['email']);
    }

    public function testRegisterRejectsShortPassword(): void
    {
        $result = $this->controller->register([
            'username'         => 'validuser',
            'email'            => 'valid@test.local',
            'password'         => 'short',
            'password_confirm' => 'short',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('Password must be at least 8 characters', $result['errors']['password']);
    }

    public function testRegisterRejectsMismatchedPasswords(): void
    {
        $result = $this->controller->register([
            'username'         => 'validuser',
            'email'            => 'valid@test.local',
            'password'         => 'password1',
            'password_confirm' => 'password2',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('Passwords do not match', $result['errors']['password_confirm']);
    }

    public function testLoginRequiresEmail(): void
    {
        $result = $this->controller->login([
            'email'    => '',
            'password' => 'password1',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('Email is required', $result['errors']['email']);
    }

    public function testLoginRequiresPassword(): void
    {
        $result = $this->controller->login([
            'email'    => 'valid@test.local',
            'password' => '',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('Password is required', $result['errors']['password']);
    }
}
