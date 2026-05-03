<?php
require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\services\delivery_service;

$deliveryService = new delivery_service();
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ref = $_POST['ref'] ?? '';
    $pin = $_POST['pin'] ?? '';

    $pin = str_pad($pin, 5, '0', STR_PAD_LEFT);

    if ($deliveryService->deliveredGoods($ref, $pin)) {
        header('Location: /pages/chat');
        exit();
    } else {
        $error = "Invalid Reference/PIN or transaction is already completed";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Delivery</title>
</head>
<body>
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color: green;">Delivery confirmed and funds released.</p>
    <?php endif; ?>

    <form action="" method="post">
        <!-- CSRF Token would go here -->
        
        <label>Enter Reference: </label>
        <input type="text" name="ref" placeholder="REF-#" required pattern="REF-[A-F0-9]{8}">
        <br/><br/>
        
        <label>Enter Pin: </label>
        <!-- Use text type to preserve leading zeros -->
        <input type="text" name="pin" placeholder="12345" required pattern="\d{5}" maxlength="5">
        <br/><br/>
        
        <button type="submit">Confirm Delivery</button>
    </form>
</body>
</html>