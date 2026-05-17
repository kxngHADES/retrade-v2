<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';

use Lib\services\Report_service;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['error' => 'Method not allowed']);
	exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$targetUserId = $input['target_user_id'] ?? null;
$reason = $input['reason'] ?? null;
$description = $input['description'] ?? '';
$reporterId = $_SESSION['uid'] ?? null;

if (!$reporterId || !$targetUserId || !$reason) {
	http_response_code(400);
	echo json_encode(['error' => 'Missing required fields (target_user_id, reason)']);
	exit;
}

$reportService = new Report_service();
$success = $reportService->report_user($reporterId, $targetUserId, $reason, $description);

if ($success) {
	echo json_encode(['success' => true]);
} else {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to submit report.']);
}
