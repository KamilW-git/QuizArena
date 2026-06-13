-- Dodaje kolumnę is_finished i unikalność odpowiedzi na sesję (dla istniejących baz).
ALTER TABLE game_sessions
    ADD COLUMN IF NOT EXISTS is_finished BOOLEAN NOT NULL DEFAULT FALSE;

CREATE UNIQUE INDEX IF NOT EXISTS idx_game_answers_session_question
    ON game_answers(session_id, question_id);
