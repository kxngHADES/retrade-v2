<?php
ob_start();

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../lib/services/payment_gateways_services.php';
require_once __DIR__ . '/../../lib/services/order_service.php';

use Lib\services\PaymentGatewaysServices;
use Lib\services\order_service;

session_start();

$uid = $_SESSION['uid'] ?? null;
$email = $_SESSION['email'] ?? null;

if (!isset($_GET['amount'], $_GET['cart_id'], $_GET['shop_id'], $_GET['seller_uid']) || !$uid || !$email) {
    ob_end_clean();
    header("Location: /pages/shop/carts/");
    exit;
}

$amount    = (float)$_GET['amount'];
$cartId    = $_GET['cart_id'];
$shopId    = $_GET['shop_id'];
$sellerUid = $_GET['seller_uid'];

// Create pending order as per instructions
$orderService = new order_service();
$orderService->createShopOrder($uid, $sellerUid, $shopId, $cartId, $amount);

$pgService = new PaymentGatewaysServices();

try {
    $_SESSION['payment_metadata'] = [
        'order_type' => 'shop',
        'shop_id'    => $shopId,
        'cart_id'    => $cartId,
        'seller_uid' => $sellerUid,
        'buyer_uid'  => $uid
    ];

    $paymentSessionId = $pgService->createPaymentSession($email, $amount);

    ob_end_clean();
    header("Location: /pages/pay/index.php?payment_session_id=" . urlencode($paymentSessionId));
    exit;

} catch (Exception $e) {
    error_log("Shop Payment Initiation Error: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo htmlspecialchars("Failed to initialize shop payment session.");
}