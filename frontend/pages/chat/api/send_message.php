<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';

use Lib\services\Chat_services;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$uid = $_SESSION['uid'];
$data = json_decode(file_get_contents("php://input"), true);

$roomId = $data['room_id'] ?? null;
$messageText = $data['message_text'] ?? '';
$attachmentUrl = $data['attachment_url'] ?? null;
$fileType = $data['file_type'] ?? null;

if (!$roomId) {
    http_response_code(400);
    echo json_encode(['error' => 'room_id is required']);
    exit;
}

$chatService = new Chat_services();
$msgDetails = $chatService->sendMessage($roomId, $uid, $messageText, $attachmentUrl, $fileType);

if ($msgDetails) {
    echo json_encode(['success' => true, 'message' => $msgDetails]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send message']);
}
