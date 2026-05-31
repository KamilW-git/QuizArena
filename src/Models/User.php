<?php

namespace QuizArena\Models;

use PDO;

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE username = :username LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(string $username, string $email, string $password): array
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare('
            INSERT INTO users (username, email, password_hash)
            VALUES (:username, :email, :hash)
            RETURNING id, username, email, xp, role, status, created_at
        ');
        $stmt->execute([
            'username' => $username,
            'email'    => $email,
            'hash'     => $hash,
        ]);

        return $stmt->fetch();
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    // Admin: Wyszukiwanie i lista użytkowników
    public function searchUsers(?string $query = null): array
    {
        $sql = "
            SELECT u.id, u.username, u.email, u.created_at, u.status, u.role, u.xp,
                   (SELECT COUNT(*) FROM quizzes q WHERE q.user_id = u.id) as quiz_count,
                   (SELECT COUNT(*) FROM game_sessions gs WHERE gs.user_id = u.id) as games_played
            FROM users u
        ";
        
        $params = [];
        if ($query) {
            $sql .= " WHERE u.username ILIKE :q OR u.email ILIKE :q OR u.id::text = :q_exact";
            $params['q'] = '%' . $query . '%';
            $params['q_exact'] = $query;
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Admin: Szczegóły użytkownika
    public function getUserDetails(string $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.id, u.username, u.email, u.created_at, u.status, u.role, u.xp,
                   (SELECT COUNT(*) FROM quizzes q WHERE q.user_id = u.id) as quiz_count,
                   (SELECT COUNT(*) FROM game_sessions gs WHERE gs.user_id = u.id) as games_played
            FROM users u WHERE u.id = :id
        ');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // Admin: Zmiana statusu
    public function updateUserStatus(string $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET status = :status WHERE id = :id');
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    // Admin: Zmiana roli
    public function updateUserRole(string $id, string $role): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET role = :role WHERE id = :id');
        return $stmt->execute(['role' => $role, 'id' => $id]);
    }

    // Admin: Usunięcie użytkownika
    public function deleteUser(string $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}