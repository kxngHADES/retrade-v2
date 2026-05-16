<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';

use Lib\services\delivery_service;

$deliveryService = new delivery_service();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ref = trim($_POST['ref'] ?? '');
    $pin = trim($_POST['pin'] ?? '');
    $pin = str_pad($pin, 5, '0', STR_PAD_LEFT);

    $result = $deliveryService->deliveredGoods($ref, $pin);
    if (!empty($result['success'])) {
        header('Location: /pages/chat');
        exit();
    }

    $error = $result['error'] ?? 'Invalid Reference/PIN or transaction is already completed';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Confirm Delivery - ReTrade</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/delivery.css">
    <script src="/assets/js/global.js" defer></script>
</head>
<body class="delivery-page">
    <?php require_once __DIR__ . '/../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="delivery-shell">
            <section class="delivery-panel">
                <header class="delivery-header">
                    <div>
                        <p class="delivery-label--small">Delivery</p>
                        <h1 class="delivery-title">Confirm Delivery</h1>
                        <p class="delivery-copy">Enter the order reference and PIN to confirm delivery and complete the transaction.</p>
                    </div>
                    <a href="/pages/chat/" class="delivery-button delivery-button--secondary">Back to Chats</a>
                </header>

                <?php if ($error): ?>
                    <div class="delivery-alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form class="delivery-form" action="" method="post">
                    <div class="delivery-field">
                        <label class="delivery-label" for="ref">Delivery Reference</label>
                        <input id="ref" name="ref" type="text" class="delivery-input" placeholder="REF-123ABC45" required pattern="REF-[A-F0-9]{8}">
                    </div>

                    <div class="delivery-field">
                        <label class="delivery-label" for="pin">Delivery PIN</label>
                        <input id="pin" name="pin" type="text" inputmode="numeric" class="delivery-input" maxlength="5" placeholder="12345" required pattern="\d{5}">
                    </div>

                    <button type="submit" class="delivery-button delivery-button--primary">Confirm Delivery</button>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
