-- ============================================
-- QuizArena — Database Views
-- PostgreSQL
-- Uruchom po schema.sql (+ opcjonalnie migrate_admin_system.php)
-- ============================================

-- ────────────────────────────────────────────
-- 1. v_quiz_stats
-- Statystyki quizów do katalogu (browse.php)
-- Zastępuje skorelowane subquery: question_count, play_count, avg_rating
-- ────────────────────────────────────────────
CREATE OR REPLACE VIEW v_quiz_stats AS
SELECT
    q.id,
    q.user_id,
    q.title,
    q.category,
    q.difficulty,
    q.is_public,
    q.created_at,
    u.username                                          AS author_username,
    COUNT(DISTINCT qu.id)                               AS question_count,
    COUNT(DISTINCT gs.id)                               AS play_count,
    ROUND(AVG(qr.rating)::numeric, 1)                   AS avg_rating,
    COUNT(DISTINCT qr.id)                               AS rating_count
FROM quizzes q
JOIN users u ON u.id = q.user_id
LEFT JOIN questions qu ON qu.quiz_id = q.id
LEFT JOIN game_sessions gs ON gs.quiz_id = q.id
LEFT JOIN quiz_ratings qr ON qr.quiz_id = q.id
GROUP BY q.id, u.username;

-- ────────────────────────────────────────────
-- 2. v_player_leaderboard
-- Ranking graczy (leaderboard/index.php) — statystyki all-time
-- ────────────────────────────────────────────
CREATE OR REPLACE VIEW v_player_leaderboard AS
SELECT
    u.id                                                AS user_id,
    u.username,
    u.xp,
    FLOOR(u.xp / 1000.0)::int + 1                       AS level,
    COUNT(DISTINCT gs.id)                               AS games_played,
    COALESCE(SUM(gs.score), 0)                          AS total_score,
    COALESCE(AVG(gs.correct_count), 0)                  AS avg_correct,
    COALESCE(
        SUM(gs.correct_count)::float /
        NULLIF(SUM(qc.question_count), 0) * 100,
        0
    )                                                   AS accuracy_pct
FROM users u
JOIN game_sessions gs ON gs.user_id = u.id
    AND gs.is_finished = TRUE
LEFT JOIN (
    SELECT quiz_id, COUNT(id) AS question_count
    FROM questions
    GROUP BY quiz_id
) qc ON qc.quiz_id = gs.quiz_id
GROUP BY u.id, u.username, u.xp
HAVING COUNT(DISTINCT gs.id) > 0;

-- ────────────────────────────────────────────
-- 3. v_session_summary
-- Sesje gry z danymi użytkownika i quizu (results, profil, dashboard)
-- ────────────────────────────────────────────
CREATE OR REPLACE VIEW v_session_summary AS
SELECT
    gs.id                                               AS session_id,
    gs.user_id,
    u.username,
    gs.quiz_id,
    q.title                                             AS quiz_title,
    q.category                                          AS quiz_category,
    q.difficulty                                        AS quiz_difficulty,
    gs.score,
    gs.correct_count,
    gs.time_taken,
    gs.is_finished,
    gs.completed_at,
    (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) AS total_questions
FROM game_sessions gs
JOIN users u ON u.id = gs.user_id
JOIN quizzes q ON q.id = gs.quiz_id;

-- ────────────────────────────────────────────
-- 4. v_user_achievement_progress
-- Osiągnięcia z informacją o odblokowaniu (profile/index.php)
-- ────────────────────────────────────────────
CREATE OR REPLACE VIEW v_user_achievement_progress AS
SELECT
    u.id                                                AS user_id,
    u.username,
    a.id                                                AS achievement_id,
    a.key                                               AS achievement_key,
    a.name                                              AS achievement_name,
    a.description                                       AS achievement_description,
    a.xp_reward,
    ua.unlocked_at,
    (ua.user_id IS NOT NULL)                            AS is_unlocked
FROM users u
CROSS JOIN achievements a
LEFT JOIN user_achievements ua
    ON ua.user_id = u.id AND ua.achievement_id = a.id;
