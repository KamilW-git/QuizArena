<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use QuizArena\Config\Database;
use QuizArena\Controllers\AdminController;
use QuizArena\Helpers\Auth;
use QuizArena\Helpers\Env;

Env::load(__DIR__ . '/../../../.env');
Auth::start();

header('Content-Type: application/json');

if (!Auth::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$action = $_POST['action'] ?? '';
$targetType = $_POST['target_type'] ?? '';
$targetId = $_POST['target_id'] ?? '';
$adminId = Auth::user()['id'];

$db = Database::connect();
$adminController = new AdminController($db);
$success = false;

try {
    switch ($action) {
        case 'SUSPEND_USER':
        case 'BAN_USER':
        case 'ACTIVATE_USER':
            if ($targetType === 'USER') {
                $status = $_POST['status'] ?? 'ACTIVE';
                $success = $adminController->updateUserStatus($adminId, $targetId, $status);
            }
            break;
            
        case 'DELETE_USER':
            if ($targetType === 'USER') {
                $success = $adminController->deleteUser($adminId, $targetId);
            }
            break;

        case 'HIDE_QUIZ':
        case 'PUBLISH_QUIZ':
            if ($targetType === 'QUIZ') {
                $status = $_POST['status'] ?? 'PUBLISHED';
                $success = $adminController->updateQuizStatus($adminId, $targetId, $status);
            }
            break;
            
        case 'DELETE_QUIZ':
            if ($targetType === 'QUIZ') {
                $success = $adminController->deleteQuiz($adminId, $targetId);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            exit;
    }

    echo json_encode(['success' => $success]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
