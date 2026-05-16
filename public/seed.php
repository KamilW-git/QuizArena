<?php

require_once __DIR__ . '/../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Helpers\Env;

// Konfiguracja
Env::load(__DIR__ . '/../.env');
$db = Database::connect();

echo "<pre>";
echo "====================================\n";
echo "  QuizArena Database Seeder\n";
echo "====================================\n\n";

try {
    $db->beginTransaction();

    echo "1. Resetting database schema...\n";
    $schemaSql = file_get_contents(__DIR__ . '/../database/schema.sql');
    
    // Z uwagi na to, że schema.sql może wywalać błędy jeśli tabele już istnieją, 
    // zrobimy najpierw awaryjne DROP kaskadowe najważniejszych tabel
    $db->exec("DROP TABLE IF EXISTS user_achievements CASCADE");
    $db->exec("DROP TABLE IF EXISTS achievements CASCADE");
    $db->exec("DROP TABLE IF EXISTS game_answers CASCADE");
    $db->exec("DROP TABLE IF EXISTS game_sessions CASCADE");
    $db->exec("DROP TABLE IF EXISTS answers CASCADE");
    $db->exec("DROP TABLE IF EXISTS questions CASCADE");
    $db->exec("DROP TABLE IF EXISTS quizzes CASCADE");
    $db->exec("DROP TABLE IF EXISTS users CASCADE");

    // Uruchom oryginalny schema.sql
    $db->exec($schemaSql);
    
    // NAPRAWA: schema.sql wstawia niedziałający hash. Naprawiamy go na poprawny dla 'test1234'
    $realHash = password_hash('test1234', PASSWORD_DEFAULT);
    $db->exec("UPDATE users SET password_hash = '{$realHash}' WHERE username = 'player_one'");

    echo "   [OK] Schema recreated and player_one password fixed.\n\n";

    echo "2. Loading Sample Quizzes (sample_data.sql)...\n";
    $sampleDataSql = file_get_contents(__DIR__ . '/../database/seeds/sample_data.sql');
    $db->exec($sampleDataSql);
    echo "   [OK] 15 quizzes loaded from sample_data.sql.\n\n";

    echo "3. Generating Dummy Users...\n";
    $names = ['ShadowSniper', 'NeonNinja', 'QuantumCoder', 'ByteMe', 'CyberPunk2026', 'CodeBreaker', 'TriviaKing', 'Brainiac', 'PixelPioneer', 'GlitchHunter', 'ZeroCool', 'DataJunkie', 'LogicLord', 'SynthWave', 'GhostInTheShell'];
    $userIds = [];

    $stmtUser = $db->prepare("INSERT INTO users (username, email, password_hash, xp) VALUES (?, ?, ?, ?) RETURNING id");
    
    // Hash do fałszywych haseł "test1234"
    $hash = password_hash('test1234', PASSWORD_DEFAULT);

    foreach ($names as $idx => $name) {
        $email = strtolower($name) . "@example.com";
        // Generujemy losowe XP - by tabela liderów była ciekawa (np. 500 - 5000)
        $xp = rand(500, 5000); 
        $stmtUser->execute([$name, $email, $hash, $xp]);
        $userIds[] = $stmtUser->fetchColumn();
    }
    echo "   [OK] " . count($userIds) . " dummy users created.\n\n";

    echo "4. Generating Random Game Sessions...\n";
    // Pobierzmy wszystkie quiz_id 
    $stmtQuizzes = $db->query("SELECT id FROM quizzes");
    $quizzes = $stmtQuizzes->fetchAll(PDO::FETCH_COLUMN);

    $stmtSession = $db->prepare("INSERT INTO game_sessions (user_id, quiz_id, score, correct_count, time_taken, completed_at) VALUES (?, ?, ?, ?, ?, ?)");
    $sessionCount = 0;

    // Każdy użytkownik zagra w od 3 do 8 quizów
    foreach ($userIds as $uid) {
        $gamesToPlay = rand(3, 8);
        $quizzesPlayed = (array)array_rand(array_flip($quizzes), $gamesToPlay);

        foreach ($quizzesPlayed as $qid) {
            // Generujemy losowy wynik
            // Powiedzmy, że quiz ma 10 pytań, każdy trafiony to np. xp bazowe * czas
            $correctCount = rand(3, 10);
            $score = $correctCount * 100 + rand(50, 500); // Fałszywy wynik
            $timeTaken = rand(40, 300); // 40 do 300 sekund
            
            // Cofamy datę w przeszłość o kilka dni (max 30 dni)
            $daysAgo = rand(0, 30);
            $completedAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));

            $stmtSession->execute([$uid, $qid, $score, $correctCount, $timeTaken, $completedAt]);
            $sessionCount++;
        }
    }
    echo "   [OK] Generated $sessionCount random game sessions.\n\n";

    echo "5. Awarding Random Achievements...\n";
    $stmtAchiev = $db->query("SELECT id FROM achievements");
    $achievements = $stmtAchiev->fetchAll(PDO::FETCH_COLUMN);

    $stmtUserAchiev = $db->prepare("INSERT INTO user_achievements (user_id, achievement_id, unlocked_at) VALUES (?, ?, ?) ON CONFLICT DO NOTHING");
    $achievCount = 0;

    foreach ($userIds as $uid) {
        $achievToGive = rand(1, 4);
        $given = (array)array_rand(array_flip($achievements), $achievToGive);
        foreach ($given as $aid) {
            $daysAgo = rand(0, 30);
            $unlockedAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));
            $stmtUserAchiev->execute([$uid, $aid, $unlockedAt]);
            $achievCount++;
        }
    }
    echo "   [OK] Awarded $achievCount random achievements.\n\n";

    $db->commit();
    echo "====================================\n";
    echo " SUCCESS! Database seeded successfully.\n";
    echo " You can now login as 'player@quizarena.com'\n";
    echo " Password: 'test1234'\n";
    echo " \n WARNING: You should delete this seed.php file now!\n";
    echo "====================================\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "\n[ERROR] Seeding failed: " . $e->getMessage() . "\n";
}
echo "</pre>";
