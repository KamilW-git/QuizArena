<?php

namespace QuizArena\Models;

use PDO;

class Question
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Zapisz pytanie z odpowiedziami
    public function create(string $quizId, string $content, int $correctAnswer, int $timeLimit, int $orderIndex): array
    {
        $stmt = $this->db->prepare('
            INSERT INTO questions (quiz_id, content, correct_answer, time_limit, order_index)
            VALUES (:quiz_id, :content, :correct_answer, :time_limit, :order_index)
            RETURNING id, content, correct_answer, time_limit, order_index
        ');
        $stmt->execute([
            'quiz_id'        => $quizId,
            'content'        => $content,
            'correct_answer' => $correctAnswer,
            'time_limit'     => $timeLimit,
            'order_index'    => $orderIndex,
        ]);

        return $stmt->fetch();
    }

    // Zapisz odpowiedzi do pytania
    public function createAnswers(string $questionId, array $answers): void
    {
        $stmt = $this->db->prepare('
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
    }
}