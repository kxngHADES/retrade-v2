<?php
ob_start(); // prevent header-already-sent issues

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../lib/services/payment_gateways_services.php';

use Lib\services\PaymentGatewaysServices;

session_start();

$uid = $_SESSION['uid'] ?? null;
$email = $_SESSION['email'] ?? null;

// During dev only — remove before production
// $uid = '475d9c31-4094-11f1-beee-32d150405fde';
// $email = 'test@example.com';

if (!isset($_GET['amount'], $_GET['listing_id']) || !$uid || !$email) {
    ob_end_clean();
    header("Location: /pages/chat/");
    exit;
}

$amount    = (float)$_GET['amount'];
$listingId = $_GET['listing_id'];
$orderType = $_GET['order_type'] ?? 'marketplace';
$sellerUid = $_GET['seller_uid'] ?? null; // Bug B fix

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
    error_log("Payment Initiation Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    ob_end_clean();
    http_response_code(500);
    echo htmlspecialchars("Failed to initialize payment session.");
}
