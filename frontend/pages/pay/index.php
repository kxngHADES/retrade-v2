<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../lib/services/payment_gateways_services.php';

session_start();

$sessionId = $_GET['payment_session_id'] ?? null;

if (!is_numeric($sessionId) || $sessionId <= 0) {
    echo htmlspecialchars("Invalid or missing session ID.");
    exit;
}

$pgService = new Lib\services\PaymentGatewaysServices();
$sessionDetails = $pgService->getPaymentSession((int)$sessionId);

if (!$sessionDetails || $sessionDetails['status'] !== 'pending') {
    echo htmlspecialchars("Payment session expired or closed.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Payment Checkout</title>
    <style>
        body { font-family: Arial, sans-serif; background: #e5e5e5; display: flex; center; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .payment-card { background: white; padding: 20px; border-radius: 10px; width: 350px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .payment-card h2 { text-align: center; margin-bottom: 20px; }
        .amount-display { font-size: 1.5em; text-align: center; color: #128C7E; margin-bottom: 20px; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .input-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .input-group .row { display: flex; gap: 10px; }
        .row div { width: 50%; }
        .btn-pay { width: 100%; background: #128C7E; color: white; border: none; padding: 15px; border-radius: 5px; cursor: pointer; font-size: 1.1em; }
        .btn-pay:hover { background: #075E54; }
    </style>
</head>
<body>
    <div class="payment-card">
        <h2>Secure Payment</h2>
        <div class="amount-display">Amount Due: R<?= htmlspecialchars(number_format($sessionDetails['amount'], 2)) ?></div>
        
        <form action="/pages/pay/process.php" method="POST">
            <input type="hidden" name="payment_session_id" value="<?= htmlspecialchars($sessionId) ?>">
            
            <div class="input-group">
                <label for="card_name">Cardholder Name</label>
                <input type="text" id="card_name" name="card_name" required placeholder="John Doe">
            </div>
            
            <div class="input-group">
                <label for="card_number">Card Number</label>
                <input type="text" id="card_number" name="card_number" required placeholder="0000 0000 0000 0000" maxlength="19">
            </div>
            
            <div class="input-group row">
                <div>
                    <label for="exp_date">Expiry Date</label>
                    <input type="text" id="exp_date" name="exp_date" required placeholder="MM/YY" maxlength="5">
                </div>
                <div>
                    <label for="cvv">CVV</label>
                    <input type="password" id="cvv" name="cvv" required placeholder="123" maxlength="3">
                </div>
            </div>
            
            <button type="submit" class="btn-pay">Pay R<?= htmlspecialchars(number_format($sessionDetails['amount'], 2)) ?></button>
        </form>
    </div>
</body>
</html>