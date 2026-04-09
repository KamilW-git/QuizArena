<?php

namespace QuizArena\Models;

use PDO;

class Achievement
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Sprawdź i przyznaj wszystkie należne achievementy dla użytkownika.
     * Zwraca listę nowo odblokowanych achievementów (może być pusta).
     */
    public function checkAndAward(string $userId): array
    {
        $newlyUnlocked = [];

        // Pobierz już odblokowane klucze
        $stmt = $this->db->prepare('
            SELECT a.key FROM achievements a
            JOIN user_achievements ua ON ua.achievement_id = a.id
            WHERE ua.user_id = :uid
        ');
        $stmt->execute(['uid' => $userId]);
        $alreadyUnlocked = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $alreadyUnlocked = array_flip($alreadyUnlocked); // dla szybkiego isset()

        // Pobierz statystyki gracza potrzebne do warunków
        $stmt = $this->db->prepare('
            SELECT
                COUNT(DISTINCT gs.id)                               AS games_played,
                COALESCE(MAX(
                    CASE WHEN gs.correct_count = (
                        SELECT COUNT(*) FROM game_answers ga WHERE ga.session_id = gs.id
                    ) AND gs.correct_count > 0 THEN 1 ELSE 0 END
                ), 0)                                               AS has_perfect,
                COUNT(DISTINCT q.id)                                AS quizzes_created
            FROM users u
            LEFT JOIN game_sessions gs ON gs.user_id = u.id
            LEFT JOIN quizzes q        ON q.user_id  = u.id
            WHERE u.id = :uid
        ');
        $stmt->execute(['uid' => $userId]);
        $s = $stmt->fetch();

        // Definicje warunków — klucz => callable zwracające bool
        $conditions = [
            'first_game'    => (int)$s['games_played']    >= 1,
            'ten_games'     => (int)$s['games_played']    >= 10,
            'perfect_score' => (int)$s['has_perfect']     >= 1,
            'quiz_creator'  => (int)$s['quizzes_created'] >= 1,
        ];

        foreach ($conditions as $key => $met) {
            if (!$met || isset($alreadyUnlocked[$key])) continue;

            // Pobierz achievement z bazy
            $stmt = $this->db->prepare('SELECT * FROM achievements WHERE key = :key');
            $stmt->execute(['key' => $key]);
            $achievement = $stmt->fetch();

            if (!$achievement) continue;

            // Przyznaj
            $stmt = $this->db->prepare('
                INSERT INTO user_achievements (user_id, achievement_id)
                VALUES (:uid, :aid)
                ON CONFLICT DO NOTHING
            ');
            $stmt->execute(['uid' => $userId, 'aid' => $achievement['id']]);

            // Dodaj XP
            if ((int)$achievement['xp_reward'] > 0) {
                $stmt = $this->db->prepare('
                    UPDATE users SET xp = xp + :xp WHERE id = :uid
                ');
                $stmt->execute(['xp' => $achievement['xp_reward'], 'uid' => $userId]);
            }

            $newlyUnlocked[] = $achievement;
        }

        return $newlyUnlocked;
    }

    /**
     * Pobierz wszystkie achievementy z flagą czy użytkownik je odblokował.
     */
    public function getAllForUser(string $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT
                a.*,
                ua.unlocked_at,
                CASE WHEN ua.user_id IS NOT NULL THEN true ELSE false END AS unlocked
            FROM achievements a
            LEFT JOIN user_achievements ua
                ON ua.achievement_id = a.id AND ua.user_id = :uid
            ORDER BY unlocked DESC, a.xp_reward DESC
        ');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}