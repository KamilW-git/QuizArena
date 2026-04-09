<?php

namespace QuizArena\Models;

use PDO;

class Quiz
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Utwórz nowy quiz
    public function create(string $userId, string $title, string $category, int $difficulty): array
    {
        $stmt = $this->db->prepare('
            INSERT INTO quizzes (user_id, title, category, difficulty)
            VALUES (:user_id, :title, :category, :difficulty)
            RETURNING id, title, category, difficulty, created_at
        ');
        $stmt->execute([
            'user_id'    => $userId,
            'title'      => $title,
            'category'   => $category,
            'difficulty' => $difficulty,
        ]);

        return $stmt->fetch();
    }

    // Pobierz wszystkie publiczne quizy
    public function getAll(?string $category = null): array
    {
        $sql = '
            SELECT q.*, u.username,
                   COUNT(qu.id) as question_count
            FROM quizzes q
            JOIN users u ON u.id = q.user_id
            LEFT JOIN questions qu ON qu.quiz_id = q.id
            WHERE q.is_public = true
        ';

        if ($category) {
            $sql .= ' AND q.category = :category';
        }

        $sql .= ' GROUP BY q.id, u.username ORDER BY q.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($category ? ['category' => $category] : []);

        return $stmt->fetchAll();
    }

    // Pobierz quiz po ID (z pytaniami i odpowiedziami)
    public function getById(string $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT q.*, u.username
            FROM quizzes q
            JOIN users u ON u.id = q.user_id
            WHERE q.id = :id
        ');
        $stmt->execute(['id' => $id]);
        $quiz = $stmt->fetch();

        if (!$quiz) return null;

        // Pobierz pytania
        $stmt = $this->db->prepare('
            SELECT * FROM questions
            WHERE quiz_id = :quiz_id
            ORDER BY order_index ASC
        ');
        $stmt->execute(['quiz_id' => $id]);
        $questions = $stmt->fetchAll();

        // Pobierz odpowiedzi dla każdego pytania
        foreach ($questions as &$question) {
            $stmt = $this->db->prepare('
                SELECT * FROM answers
                WHERE question_id = :question_id
                ORDER BY index ASC
            ');
            $stmt->execute(['question_id' => $question['id']]);
            $question['answers'] = $stmt->fetchAll();
        }

        $quiz['questions'] = $questions;

        return $quiz;
    }

    // Pobierz quizy danego użytkownika
    public function getByUser(string $userId): array
    {
        $stmt = $this->db->prepare('
            SELECT q.*, COUNT(qu.id) as question_count
            FROM quizzes q
            LEFT JOIN questions qu ON qu.quiz_id = q.id
            WHERE q.user_id = :user_id
            GROUP BY q.id
            ORDER BY q.created_at DESC
        ');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // Pobierz dostępne kategorie
    public function getCategories(): array
    {
        $stmt = $this->db->prepare('
            SELECT DISTINCT category FROM quizzes
            WHERE is_public = true
            ORDER BY category ASC
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function findById(PDO $db, string $id): ?array
    {   
        $stmt = $db->prepare('SELECT * FROM quizzes WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}