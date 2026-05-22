<?php

namespace QuizArena\Config;

use PDO;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../.env');



class Database
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        if (self::$connection === null) {
            $host = Env::get('DB_HOST', 'db');
            $port = Env::get('DB_PORT', '5432');
            $name = Env::get('DB_NAME', 'quizarena');
            $user = Env::get('DB_USER', 'postgres');
            $pass = Env::get('DB_PASS', 'secret');

            $dsn = "pgsql:host=$host;port=$port;dbname=$name";

            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return self::$connection;
    }
}