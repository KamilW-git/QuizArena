<?php

namespace QuizArena\Tests\Support;

use PHPUnit\Framework\TestCase;

abstract class PdoTestCase extends TestCase
{
    use CreatesPdoMocks;
}
