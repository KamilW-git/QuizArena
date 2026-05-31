<?php
require_once __DIR__ . '/vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/.env');

try {
    $db = Database::connect();
    
    // Begin Transaction
    $db->beginTransaction();

    echo "Running migrations...\n";

    // 1. Alter users table
    echo "Adding role and status to users table...\n";
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(20) NOT NULL DEFAULT 'USER'");
    $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE'");

    // Utwórz konto administratora
    echo "Configuring admin account...\n";
    $adminEmail = 'admin@quizarena.com';
    $adminPassword = 'admin1209';
    $adminHash = password_hash($adminPassword, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $adminEmail]);
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password_hash, role, status)
            VALUES ('admin', :email, :hash, 'ADMIN', 'ACTIVE')
        ");
        $stmt->execute(['email' => $adminEmail, 'hash' => $adminHash]);
        echo "Admin user created successfully.\n";
    } else {
        $stmt = $db->prepare("
            UPDATE users SET password_hash = :hash, role = 'ADMIN', status = 'ACTIVE' WHERE email = :email
        ");
        $stmt->execute(['email' => $adminEmail, 'hash' => $adminHash]);
        echo "Admin user updated successfully.\n";
    }

    // 2. Alter quizzes table
    echo "Adding status to quizzes table...\n";
    $db->exec("ALTER TABLE quizzes ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'PUBLISHED'");

    // Update existing quizzes based on is_public (if it was false, mark as HIDDEN)
    // Note: If is_public was already in schema, this logic handles backward compatibility.
    $db->exec("UPDATE quizzes SET status = 'HIDDEN' WHERE is_public = FALSE AND status = 'PUBLISHED'");

    // 3. Create admin_logs table
    echo "Creating admin_logs table...\n";
    $db->exec('
    CREATE TABLE IF NOT EXISTS admin_logs (
        id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
        admin_id    UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        action      VARCHAR(100) NOT NULL,
        target_type VARCHAR(50) NOT NULL,
        target_id   UUID,
        details     TEXT,
        created_at  TIMESTAMP NOT NULL DEFAULT NOW()
    );
    ');

    // 4. Create indexes for admin_logs
    echo "Creating indexes...\n";
    $db->exec("CREATE INDEX IF NOT EXISTS idx_admin_logs_admin_id ON admin_logs(admin_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_admin_logs_target ON admin_logs(target_type, target_id)");

    $db->commit();
    echo "SUCCESS: Admin system migrations completed successfully.\n";

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo "ERROR: " . $e->getMessage() . "\n";
}
