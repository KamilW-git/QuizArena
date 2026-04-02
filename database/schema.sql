-- ============================================
-- QuizArena — Database Schema
-- PostgreSQL
-- ============================================

-- Extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================
-- 1. USERS
-- ============================================
CREATE TABLE users (
    id            UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_url    TEXT,
    xp            INT          NOT NULL DEFAULT 0,
    created_at    TIMESTAMP    NOT NULL DEFAULT NOW()
);

-- ============================================
-- 2. QUIZZES
-- ============================================
CREATE TABLE quizzes (
    id         UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id    UUID         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title      VARCHAR(100) NOT NULL,
    category   VARCHAR(50)  NOT NULL,
    difficulty SMALLINT     NOT NULL DEFAULT 1 CHECK (difficulty BETWEEN 1 AND 3),
    is_public  BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP    NOT NULL DEFAULT NOW()
);

-- ============================================
-- 3. QUESTIONS
-- ============================================
CREATE TABLE questions (
    id             UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    quiz_id        UUID     NOT NULL REFERENCES quizzes(id) ON DELETE CASCADE,
    content        TEXT     NOT NULL,
    correct_answer SMALLINT NOT NULL CHECK (correct_answer BETWEEN 0 AND 3),
    time_limit     SMALLINT NOT NULL DEFAULT 30,
    order_index    SMALLINT NOT NULL DEFAULT 0
);

-- ============================================
-- 4. ANSWERS (4 per question)
-- ============================================
CREATE TABLE answers (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    question_id UUID     NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
    content     TEXT     NOT NULL,
    index       SMALLINT NOT NULL CHECK (index BETWEEN 0 AND 3)
);

-- ============================================
-- 5. GAME SESSIONS
-- ============================================
CREATE TABLE game_sessions (
    id            UUID      PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id       UUID      NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    quiz_id       UUID      NOT NULL REFERENCES quizzes(id) ON DELETE CASCADE,
    score         INT       NOT NULL DEFAULT 0,
    correct_count SMALLINT  NOT NULL DEFAULT 0,
    time_taken    INT       NOT NULL DEFAULT 0,
    completed_at  TIMESTAMP NOT NULL DEFAULT NOW()
);

-- ============================================
-- 6. GAME ANSWERS (one row per question per session)
-- ============================================
CREATE TABLE game_answers (
    id           UUID     PRIMARY KEY DEFAULT uuid_generate_v4(),
    session_id   UUID     NOT NULL REFERENCES game_sessions(id) ON DELETE CASCADE,
    question_id  UUID     NOT NULL REFERENCES questions(id) ON DELETE CASCADE,
    chosen_index SMALLINT NOT NULL CHECK (chosen_index BETWEEN 0 AND 3),
    is_correct   BOOLEAN  NOT NULL DEFAULT FALSE,
    time_spent   SMALLINT NOT NULL DEFAULT 0
);

-- ============================================
-- 7. ACHIEVEMENTS
-- ============================================
CREATE TABLE achievements (
    id          UUID         PRIMARY KEY DEFAULT uuid_generate_v4(),
    key         VARCHAR(50)  NOT NULL UNIQUE,
    name        VARCHAR(100) NOT NULL,
    description TEXT         NOT NULL,
    xp_reward   INT          NOT NULL DEFAULT 0
);

-- ============================================
-- 8. USER ACHIEVEMENTS (junction table)
-- ============================================
CREATE TABLE user_achievements (
    user_id        UUID      NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    achievement_id UUID      NOT NULL REFERENCES achievements(id) ON DELETE CASCADE,
    unlocked_at    TIMESTAMP NOT NULL DEFAULT NOW(),
    PRIMARY KEY (user_id, achievement_id)
);

-- ============================================
-- INDEXES (przyspieszają zapytania)
-- ============================================
CREATE INDEX idx_quizzes_user_id      ON quizzes(user_id);
CREATE INDEX idx_questions_quiz_id    ON questions(quiz_id);
CREATE INDEX idx_answers_question_id  ON answers(question_id);
CREATE INDEX idx_sessions_user_id     ON game_sessions(user_id);
CREATE INDEX idx_sessions_quiz_id     ON game_sessions(quiz_id);
CREATE INDEX idx_game_answers_session ON game_answers(session_id);

-- ============================================
-- SAMPLE DATA — kilka rekordów do testów
-- ============================================

-- Testowy użytkownik (hasło: "test1234" — w PHP zastąpisz przez password_hash())
INSERT INTO users (username, email, password_hash) VALUES
    ('player_one', 'player@quizarena.com', '$2y$10$examplehashexamplehashexamplehashexamplehashexampleh');

-- Przykładowe achievementy
INSERT INTO achievements (key, name, description, xp_reward) VALUES
    ('first_game',    'First Blood',    'Complete your first quiz',           50),
    ('perfect_score', 'Perfect Score',  'Get 100% correct answers in a quiz', 200),
    ('quiz_creator',  'Quiz Master',    'Create your first quiz',             100),
    ('ten_games',     'Dedicated',      'Complete 10 quizzes',                150);