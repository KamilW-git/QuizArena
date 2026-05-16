<?php
require_once __DIR__ . '/../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../.env');

try {
    $db = Database::connect();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS notifications (
        id         UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
        user_id    UUID         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        title      VARCHAR(100) NOT NULL,
        message    TEXT         NOT NULL,
        type       VARCHAR(50)  NOT NULL DEFAULT 'system',
        is_read    BOOLEAN      NOT NULL DEFAULT FALSE,
        created_at TIMESTAMP    NOT NULL DEFAULT NOW()
    );
    CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id);
    ";
    
    $db->exec($sql);
    echo "<h1>SUCCESS!</h1><p>The notifications table has been successfully created.</p><a href='/dashboard.php'>Go to Dashboard</a>";
} catch (Exception $e) {
    echo "<h1>ERROR</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
