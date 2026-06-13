<?php

namespace QuizArena\Tests\Unit;

use QuizArena\Exceptions\GameException;
use QuizArena\Helpers\Auth;
use QuizArena\Tests\Support\SessionTestCase;

class AuthTest extends SessionTestCase
{
    public function testCheckReturnsFalseWhenNotLoggedIn(): void
    {
        $this->assertFalse(Auth::check());
    }

    public function testLoginStoresUserInSession(): void
    {
        Auth::login([
            'id'       => 'user-1',
            'username' => 'player',
            'email'    => 'player@test.local',
            'xp'       => 120,
            'role'     => 'USER',
            'status'   => 'ACTIVE',
        ]);

        $this->assertTrue(Auth::check());
        $this->assertSame('user-1', Auth::user()['id']);
        $this->assertSame('player', Auth::user()['username']);
    }

    public function testLoginDefaultsRoleAndStatus(): void
    {
        Auth::login([
            'id'       => 'user-2',
            'username' => 'guest',
            'email'    => 'guest@test.local',
            'xp'       => 0,
        ]);

        $this->assertSame('USER', Auth::user()['role']);
        $this->assertSame('ACTIVE', Auth::user()['status']);
    }

    public function testIsAdminReturnsTrueForAdminRole(): void
    {
        Auth::login([
            'id'       => 'admin-1',
            'username' => 'admin',
            'email'    => 'admin@test.local',
            'xp'       => 0,
            'role'     => 'ADMIN',
        ]);

        $this->assertTrue(Auth::isAdmin());
    }

    public function testIsAdminReturnsFalseForRegularUser(): void
    {
        Auth::login([
            'id'       => 'user-3',
            'username' => 'user',
            'email'    => 'user@test.local',
            'xp'       => 0,
            'role'     => 'USER',
        ]);

        $this->assertFalse(Auth::isAdmin());
    }

    public function testLogoutClearsSession(): void
    {
        Auth::login([
            'id'       => 'user-4',
            'username' => 'temp',
            'email'    => 'temp@test.local',
            'xp'       => 0,
        ]);

        Auth::logout();

        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::user());
    }

    public function testGenerateCsrfTokenIsStableWithinSession(): void
    {
        $first  = Auth::generateCsrfToken();
        $second = Auth::generateCsrfToken();

        $this->assertNotEmpty($first);
        $this->assertSame($first, $second);
        $this->assertSame(64, strlen($first));
    }

    public function testValidateCsrfTokenAcceptsMatchingToken(): void
    {
        $token = Auth::generateCsrfToken();

        $this->assertTrue(Auth::validateCsrfToken($token));
    }

    public function testValidateCsrfTokenRejectsInvalidToken(): void
    {
        Auth::generateCsrfToken();

        $this->assertFalse(Auth::validateCsrfToken('invalid-token'));
        $this->assertFalse(Auth::validateCsrfToken(null));
    }
}
