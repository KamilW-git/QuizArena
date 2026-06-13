<?php

namespace QuizArena\Exceptions;

class GameException extends \Exception
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 400
    ) {
        parent::__construct($message);
    }
}
