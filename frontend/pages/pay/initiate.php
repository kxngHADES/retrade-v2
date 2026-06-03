<?php
ob_start();

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../lib/services/payment_gateways_services.php';

use Lib\services\PaymentGatewaysServices;

session_start();

$uid = $_SESSION['uid'] ?? null;
$email = $_SESSION['email'] ?? null;

error_log(sprintf(
    "Payment Initiation Request: amount=%s listing_id=%s order_type=%s seller_uid=%s uid=%s email=%s",
    $_GET['amount'] ?? 'null',
    $_GET['listing_id'] ?? 'null',
    $_GET['order_type'] ?? 'null',
    $_GET['seller_uid'] ?? 'null',
    $uid ?? 'null',
    $email ?? 'null'
));

if (!isset($_GET['amount'], $_GET['listing_id']) || !$uid || !$email) {
    ob_end_clean();
    header("Location: /pages/chat/");
    exit;
}

$amount    = (float)$_GET['amount'];
$listingId = $_GET['listing_id'];
$orderType = $_GET['order_type'] ?? 'marketplace';
$sellerUid = $_GET['seller_uid'] ?? null; 

$pgService = new PaymentGatewaysServices();

try {
    $_SESSION['payment_metadata'] = [
        'listing_id' => $listingId,
        'order_type' => $orderType,
        'seller_uid' => $sellerUid,
        'buyer_uid'  => $uid
    ];

    $paymentSessionId = $pgService->createPaymentSession($email, $amount);

    ob_end_clean();
    header("Location: /pages/pay/index.php?payment_session_id=" . urlencode($paymentSessionId));
    exit;

} catch (\Throwable $e) {
    error_log("Payment Initiation Error: " . get_class($e) . " - " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
    ob_end_clean();
    http_response_code(500);
    echo htmlspecialchars("Failed to initialize payment session.");
}
