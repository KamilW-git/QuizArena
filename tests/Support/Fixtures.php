<?php

namespace QuizArena\Tests\Support;

use PDO;

class Fixtures
{
    public function __construct(private PDO $db) {}

    public function ensureSchema(): void
    {
        $migration = file_get_contents(__DIR__ . '/../../database/migrations/001_game_session_security.sql');
        $this->db->exec($migration);
    }

    public function createUser(string $suffix): string
    {
        $username = 'test_' . $suffix;
        $email    = $username . '@test.local';
        $hash     = password_hash('password', PASSWORD_BCRYPT);

        $stmt = $this->db->prepare('
            INSERT INTO users (username, email, password_hash)
            VALUES (:username, :email, :hash)
            RETURNING id
        ');
        $stmt->execute([
            'username' => $username,
            'email'    => $email,
            'hash'     => $hash,
        ]);

        return $stmt->fetchColumn();
    }

    /**
     * @return array{quiz_id: string, question_id: string, correct_index: int}
     */
    public function createPublicQuiz(string $ownerId, string $suffix = 'quiz'): array
    {
        $stmt = $this->db->prepare('
            INSERT INTO quizzes (user_id, title, category, difficulty, is_public)
            VALUES (:user_id, :title, :category, 1, TRUE)
            RETURNING id
        ');
        $stmt->execute([
            'user_id'  => $ownerId,
            'title'    => 'Test Quiz ' . $suffix,
            'category' => 'Science',
        ]);
        $quizId = $stmt->fetchColumn();

        $stmt = $this->db->prepare('
            INSERT INTO questions (quiz_id, content, correct_answer, time_limit, order_index)
            VALUES (:quiz_id, :content, 2, 30, 0)
            RETURNING id
        ');
        $stmt->execute([
            'quiz_id' => $quizId,
            'content' => 'Test question?',
        ]);
        $questionId = $stmt->fetchColumn();

        $answers = ['A', 'B', 'C', 'D'];
        $stmt    = $this->db->prepare('
            INSERT INTO answers (question_id, content, index)
            VALUES (:question_id, :content, :index)
        ');
        foreach ($answers as $index => $content) {
            $stmt->execute([
                'question_id' => $questionId,
                'content'     => $content,
                'index'       => $index,
            ]);
        }

        return [
            'quiz_id'       => $quizId,
            'question_id'   => $questionId,
            'correct_index' => 2,
        ];
    }

    public function startSession(string $userId, string $quizId): string
    {
        $stmt = $this->db->prepare('
            INSERT INTO game_sessions (user_id, quiz_id)
            VALUES (:user_id, :quiz_id)
            RETURNING id
        ');
        $stmt->execute([
            'user_id' => $userId,
            'quiz_id' => $quizId,
        ]);

        return $stmt->fetchColumn();
    }
}
