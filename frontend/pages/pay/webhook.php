<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../lib/services/payment_gateways_services.php';

use Lib\services\PaymentGatewaysServices;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

// 1. Capture Raw Payload Data
$payloadRaw = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

if (empty($signature) || empty($payloadRaw)) {
    http_response_code(400);
    exit("Missing signature or payload");
}

/* Note: 2. Process logic handling HMAC verification */
$pgService = new PaymentGatewaysServices();
$processed = $pgService->processWebhookPayload($signature, $payloadRaw);

if ($processed) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Webhook successfully processed."]);
} else {
    http_response_code(400); // 400 Bad Request
    echo json_encode(["status" => "error", "message" => "Invalid Webhook signature or data payload"]);
}
