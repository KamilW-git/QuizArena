<?php

namespace QuizArena\Controllers;

use PDO;
use QuizArena\Models\User;
use QuizArena\Models\Quiz;
use QuizArena\Models\AdminLog;

class AdminController
{
    private User $user;
    private Quiz $quiz;
    private AdminLog $adminLog;
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->user = new User($db);
        $this->quiz = new Quiz($db);
        $this->adminLog = new AdminLog($db);
    }

    // -- DASHBOARD STATS --
    public function getDashboardStats(): array
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        $totalUsers = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM quizzes");
        $totalQuizzes = $stmt->fetchColumn();

        $recentLogs = $this->adminLog->getRecentLogs(10);

        return [
            'totalUsers' => $totalUsers,
            'totalQuizzes' => $totalQuizzes,
            'recentLogs' => $recentLogs
        ];
    }

    // -- USERS --
    public function searchUsers(?string $query = null): array
    {
        return $this->user->searchUsers($query);
    }

    public function getUserDetails(string $id): ?array
    {
        return $this->user->getUserDetails($id);
    }

    public function updateUserStatus(string $adminId, string $targetUserId, string $status): bool
    {
        $success = $this->user->updateUserStatus($targetUserId, $status);
        if ($success) {
            $this->adminLog->log($adminId, "CHANGE_USER_STATUS_TO_$status", 'USER', $targetUserId);
        }
        return $success;
    }

    public function deleteUser(string $adminId, string $targetUserId): bool
    {
        $success = $this->user->deleteUser($targetUserId);
        if ($success) {
            $this->adminLog->log($adminId, 'DELETE_USER', 'USER', $targetUserId);
        }
        return $success;
    }

    // -- QUIZZES --
    public function searchQuizzes(?string $query = null): array
    {
        return $this->quiz->searchQuizzes($query);
    }

    public function updateQuizStatus(string $adminId, string $targetQuizId, string $status): bool
    {
        $success = $this->quiz->updateQuizStatus($targetQuizId, $status);
        if ($success) {
            $this->adminLog->log($adminId, "CHANGE_QUIZ_STATUS_TO_$status", 'QUIZ', $targetQuizId);
        }
        return $success;
    }

    public function deleteQuiz(string $adminId, string $targetQuizId): bool
    {
        $success = $this->quiz->deleteQuiz($targetQuizId);
        if ($success) {
            $this->adminLog->log($adminId, 'DELETE_QUIZ', 'QUIZ', $targetQuizId);
        }
        return $success;
    }

    public function updateQuiz(string $adminId, string $targetQuizId, string $title, string $category, int $difficulty): bool
    {
        $success = $this->quiz->updateQuiz($targetQuizId, $title, $category, $difficulty);
        if ($success) {
            $this->adminLog->log($adminId, 'UPDATE_QUIZ', 'QUIZ', $targetQuizId, "Updated title: $title, category: $category, difficulty: $difficulty");
        }
        return $success;
    }
}
