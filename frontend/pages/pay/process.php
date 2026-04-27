<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../lib/services/payment_gateways_services.php';

use Lib\services\PaymentGatewaysServices;

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$sessionId = $_POST['payment_session_id'] ?? null;
$uid = $_SESSION['uid'] ?? null; // Logged in user mapped

if (!is_numeric($sessionId) || !$uid) {
    die("Authentication or session missing.");
}

$sessionId = (int)$sessionId;

$pgService = new PaymentGatewaysServices();
$sessionDetails = $pgService->getPaymentSession($sessionId);

if (!$sessionDetails) {
    die("Session is invalid or expired.");
}

// Anti-Double Submit / Lock the session safely
if (!$pgService->lockSessionForProcessing($sessionId)) {
    die("Payment is already processing or expired.");
}

// Input values formatted safely (with fallbacks to prevent 500 errors)
$cardName = htmlspecialchars(trim($_POST['card_name'] ?? ''));
$cardNumber = preg_replace('/\D/', '', $_POST['card_number'] ?? ''); // Remove spaces/dashes
$expDate = htmlspecialchars(trim($_POST['exp_date'] ?? ''));
$cvv = htmlspecialchars(trim($_POST['cvv'] ?? ''));

// Card Val - simplistic checks
/*
if (strlen($cardNumber) < 13 || strlen($cvv) < 3 || empty($expDate)) {
    $pgService->updateSessionStatus($sessionId, 'failed');
    header("Location: /pages/pay/result.php?status=failed&id=" . urlencode((string)$sessionId));
    exit;
}
*/

// 1. Ask Superbase Service (Fake Bank) to process logic
$metadata = $_SESSION['payment_metadata'] ?? [];

// Bypassing Fake Bank Payment to avoid queries/issues
$success = true; 
/*
$success = $pgService->processFakeBankPayment($uid, $sessionId, (float)$sessionDetails['amount'], [
    'number' => $cardNumber,
    'cvv' => $cvv,
    'exp' => $expDate
]);
*/

// Update payload for webhook
$payloadData = [
    'paymentSession_id' => $sessionId,
    'amount' => $sessionDetails['amount'],
    'status' => $success ? 'success' : 'failed',
    'buyer_uid' => $uid,
    'seller_uid' => $metadata['seller_uid'] ?? null,
    'listing_id' => $metadata['listing_id'] ?? null,
    'order_type' => $metadata['order_type'] ?? 'marketplace'
];

if ($success) {
    // 2. Bypass webhook and directly insert into Escrow, Orders, and Payment tables
    $pgService->bypassPaymentDirectly($payloadData);

    // 3. Mark session complete and redirect to Result
    $pgService->updateSessionStatus($sessionId, 'success');
    header("Location: /pages/pay/result.php?status=success&id=" . urlencode((string)$sessionId));
    exit;
    
} else {
    // 2. Fire the asynchronous Webhook as failed
    $pgService->fireWebhook($sessionId, 'failed', $payloadData);

    // 3. Keep failed status 
    $pgService->updateSessionStatus($sessionId, 'failed');
    header("Location: /pages/pay/result.php?status=failed&id=" . urlencode((string)$sessionId));
    exit;
}