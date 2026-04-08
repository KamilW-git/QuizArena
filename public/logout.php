<?php

require_once __DIR__ . '/../vendor/autoload.php';

use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../.env');
Auth::logout();

header('Location: /login.php');
exit;