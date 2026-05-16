<?php
require_once __DIR__ . '/vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/.env');

try {
    $db = Database::connect();
    
    $sql = '
    CREATE TABLE IF NOT EXISTS quiz_ratings (
        id         UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
        user_id    UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        quiz_id    UUID NOT NULL REFERENCES quizzes(id) ON DELETE CASCADE,
        rating     SMALLINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
        created_at TIMESTAMP NOT NULL DEFAULT NOW(),
        UNIQUE(user_id, quiz_id)
    );
    ';
    
    $db->exec($sql);
    echo "SUCCESS: quiz_ratings table created.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
