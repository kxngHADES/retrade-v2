<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../lib/services/payment_gateways_services.php';

use Lib\services\PaymentGatewaysServices;

if (isset($_GET['debug']) && $_GET['debug']) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

session_start();

$uid = $_SESSION['uid'] ?? null;
$email = $_SESSION['email'] ?? null;

error_log(sprintf(
    'pay/index.php request: uid=%s email=%s amount=%s listing_id=%s order_type=%s seller_uid=%s shop_id=%s cart_id=%s',
    $uid ?? 'null',
    $email ? substr($email, 0, 3) . '***' . strstr($email, '@') : 'null',
    $_GET['amount'] ?? 'null',
    $_GET['listing_id'] ?? 'null',
    $_GET['order_type'] ?? 'null',
    $_GET['seller_uid'] ?? 'null',
    $_GET['shop_id'] ?? 'null',
    $_GET['cart_id'] ?? 'null'
));

if (!$uid || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /pages/chat/");
    exit;
}

$pgService = new PaymentGatewaysServices();
$sessionId = null;
$sessionDetails = null;
$errorMessage = null;

if (isset($_GET['payment_session_id']) && is_numeric($_GET['payment_session_id']) && (int)$_GET['payment_session_id'] > 0) {
    $sessionId = (int)$_GET['payment_session_id'];
    $sessionDetails = $pgService->getPaymentSession($sessionId);
    if (!$sessionDetails || $sessionDetails['status'] !== 'pending') {
        $errorMessage = "Payment session expired or closed.";
    }
} elseif (isset($_GET['amount']) && is_numeric($_GET['amount'])) {
    $amount = (float)$_GET['amount'];
    $orderType = $_GET['order_type'] ?? 'marketplace';
    $sellerUid = $_GET['seller_uid'] ?? null;
    $listingId = $_GET['listing_id'] ?? null;
    $shopId = $_GET['shop_id'] ?? null;
    $cartId = $_GET['cart_id'] ?? null;

    if ($amount <= 0 || !$sellerUid) {
        $errorMessage = "Invalid payment request.";
    } elseif ($orderType === 'shop' && (!$cartId || !$shopId)) {
        $errorMessage = "Incomplete shop payment details.";
    }

    if (!$errorMessage) {
        $_SESSION['payment_metadata'] = [
            'listing_id' => $listingId,
            'order_type' => $orderType,
            'seller_uid' => $sellerUid,
            'buyer_uid'  => $uid,
            'shop_id'    => $shopId,
            'cart_id'    => $cartId,
        ];

        try {
            $paymentSessionId = $pgService->createPaymentSession($email, $amount);
            $sessionId = (int)$paymentSessionId;
            $sessionDetails = $pgService->getPaymentSession($sessionId);
            if (!$sessionDetails) {
                $errorMessage = "Failed to initialize payment session.";
            }
        } catch (\Throwable $e) {
            error_log("Payment Initiation Error: " . get_class($e) . " - " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $errorMessage = "Failed to initialize payment session.";
        }
    }
} else {
    $errorMessage = "Invalid payment request.";
}

if ($errorMessage) {
    http_response_code(400);
    echo htmlspecialchars($errorMessage);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment - ReTrade</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/payment.css">
    <script src="/assets/js/global.js" defer></script>
</head>
<body class="payment-page">
    <main class="payment-shell">
        <header class="payment-header">
            <div class="payment-header-row">
                <a href="/" class="payment-back-link">Back</a>
                <h1 class="payment-brand">ReTrade</h1>
            </div>
        </header>

        <section class="payment-summary">
            <h2 class="payment-title">Secure Payment</h2>
            <div class="payment-amount-card">
                <span class="payment-amount-label">Amount Due</span>
                <strong class="payment-amount-value">R <?= htmlspecialchars(number_format($sessionDetails['amount'], 2)) ?></strong>
            </div>
        </section>

        <form class="payment-form" action="/pages/pay/process.php" method="POST">
            <input type="hidden" name="payment_session_id" value="<?= htmlspecialchars($sessionId) ?>">

            <div class="payment-field">
                <label class="payment-field-label" for="card_name">Cardholder Name</label>
                <input id="card_name" name="card_name" type="text" class="payment-input" required placeholder="Sfiso Nkosi">
            </div>

            <div class="payment-field">
                <label class="payment-field-label" for="card_number">Card Number</label>
                <input id="card_number" name="card_number" type="text" class="payment-input" maxlength="19" required placeholder="5151 0456 9930 4218">
            </div>

            <div class="payment-grid">
                <div class="payment-field">
                    <label class="payment-field-label" for="exp_date">Expiry Date</label>
                    <input id="exp_date" name="exp_date" type="text" class="payment-input" maxlength="5" required placeholder="MM/YY">
                </div>
                <div class="payment-field">
                    <label class="payment-field-label" for="cvv">CVV</label>
                    <input id="cvv" name="cvv" type="password" class="payment-input" maxlength="4" required placeholder="123">
                </div>
            </div>

            <button type="submit" class="payment-submit">Pay R <?= htmlspecialchars(number_format($sessionDetails['amount'], 2)) ?></button>
        </form>

        <div class="payment-footer">
            <span class="payment-footnote-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 10V8C17 5.23858 14.7614 3 12 3C9.23858 3 7 5.23858 7 8V10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="5" y="10" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 15V18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="12" cy="13" r="1" fill="currentColor"/>
                </svg>
            </span>
            <p class="payment-footnote-text">Your payment details are encrypted.</p>
        </div>
    </main>
</body>
</html>