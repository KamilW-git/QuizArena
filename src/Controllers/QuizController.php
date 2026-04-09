<?php

namespace QuizArena\Controllers;

use PDO;
use QuizArena\Models\Quiz;
use QuizArena\Models\Question;
use QuizArena\Models\Achievement;

class QuizController
{
    private Quiz $quiz;
    private Question $question;
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db       = $db;
        $this->quiz     = new Quiz($db);
        $this->question = new Question($db);
    }

    // Utwórz quiz z pytaniami
    public function create(string $userId, array $data): array
    {
        $errors = $this->validateQuiz($data);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Transakcja — albo wszystko albo nic
        $this->db->beginTransaction();

        try {
            $quiz = $this->quiz->create(
                $userId,
                trim($data['title']),
                trim($data['category']),
                (int) $data['difficulty']
            );

            $questions = $data['questions'] ?? [];

            foreach ($questions as $index => $q) {
                $question = $this->question->create(
                    $quiz['id'],
                    trim($q['content']),
                    (int) $q['correct_answer'],
                    (int) ($q['time_limit'] ?? 30),
                    $index
                );

                $this->question->createAnswers($question['id'], $q['answers']);
            }

            $this->db->commit();

            // Sprawdź achievementy po utworzeniu quizu
            $achievement   = new Achievement($this->db);
            $newlyUnlocked = $achievement->checkAndAward($userId);

            return ['success' => true, 'quiz_id' => $quiz['id'], 'achievements' => $newlyUnlocked];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'errors' => ['general' => 'Failed to save quiz. Try again.']];
        }
    }

    // Pobierz listę quizów
    public function getAll(?string $category = null): array
    {
        return $this->quiz->getAll($category);
    }

    // Pobierz quiz z pytaniami
    public function getById(string $id): ?array
    {
        return $this->quiz->getById($id);
    }

    // Pobierz kategorie
    public function getCategories(): array
    {
        return $this->quiz->getCategories();
    }

    // Walidacja
    private function validateQuiz(array $data): array
    {
        $errors = [];

        if (empty($data['title']) || strlen(trim($data['title'])) < 3) {
            $errors['title'] = 'Title must be at least 3 characters';
        }

        if (empty($data['category'])) {
            $errors['category'] = 'Category is required';
        }

        if (empty($data['difficulty']) || !in_array((int)$data['difficulty'], [1, 2, 3])) {
            $errors['difficulty'] = 'Difficulty must be 1, 2 or 3';
        }

        $questions = $data['questions'] ?? [];

        if (count($questions) < 1) {
            $errors['questions'] = 'Add at least one question';
        }

        foreach ($questions as $i => $q) {
            if (empty($q['content'])) {
                $errors["question_{$i}"] = "Question " . ($i + 1) . " cannot be empty";
            }

            if (count($q['answers'] ?? []) !== 4) {
                $errors["answers_{$i}"] = "Question " . ($i + 1) . " must have exactly 4 answers";
            }

            if (!isset($q['correct_answer']) || !in_array((int)$q['correct_answer'], [0, 1, 2, 3])) {
                $errors["correct_{$i}"] = "Question " . ($i + 1) . " must have a correct answer selected";
            }
        }

        return $errors;
    }
}