<?php

namespace QuizArena\Models;

use PDO;

class AdminLog
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function log(string $adminId, string $action, string $targetType, ?string $targetId = null, ?string $details = null): bool
    {
        $stmt = $this->db->prepare('
            INSERT INTO admin_logs (admin_id, action, target_type, target_id, details)
            VALUES (:admin_id, :action, :target_type, :target_id, :details)
        ');
        return $stmt->execute([
            'admin_id'    => $adminId,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'details'     => $details
        ]);
    }

    public function getRecentLogs(int $limit = 50): array
    {
        $stmt = $this->db->prepare('
            SELECT al.*, u.username as admin_username 
            FROM admin_logs al
            JOIN users u ON u.id = al.admin_id
            ORDER BY al.created_at DESC
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
