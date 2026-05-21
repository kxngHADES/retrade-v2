<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';
require_once __DIR__ . '/../../lib/services/users_interaction.php';

use Lib\services\users_interaction;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $reason = $_POST['reason'] ?? null;

    if (!$userId || !$reason) {
        $_SESSION['error'] = "Missing user ID or ban reason.";
        header('Location: index.php');
        exit;
    }

    $userInteraction = new users_interaction();
    $success = $userInteraction->banUser($userId, $reason);

    if ($success) {
        $_SESSION['message'] = "User has been banned successfully.";
    } else {
        $_SESSION['error'] = "Failed to ban user. They might already be banned or an error occurred.";
    }

    // Redirect back to user management
    header('Location: index.php');
    exit;
}

header('Location: index.php');
exit;
