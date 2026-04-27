<?php

require_once __DIR__ . '/../../config/bootstrap.php';
session_start();

$status = $_GET['status'] ?? 'unknown';
$sessionId = $_GET['id'] ?? null;

$isSuccess = ($status === 'success');
$message = $isSuccess ? "Payment Successful! Your funds are now held securely in Escrow." : "Payment Failed. Please try again with a valid card or sufficient funds.";
$color = $isSuccess ? '#128C7E' : '#d9534f';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment <?= $isSuccess ? 'Success' : 'Failed' ?></title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #f0f0f0; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 100%; }
        .icon { font-size: 50px; color: <?= $color ?>; margin-bottom: 20px; }
        h1 { margin: 0 0 10px; color: #333; }
        p { color: #666; margin-bottom: 30px; line-height: 1.5; }
        .btn { display: inline-block; background: #128C7E; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn:hover { background: #075E54; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <?= $isSuccess ? '&#10004;' : '&#10008;' ?>
        </div>
        <h1><?= $isSuccess ? 'Thank You!' : 'Payment Failed' ?></h1>
        <p><?= htmlspecialchars($message) ?></p>
        <a href="/pages/chat/" class="btn">Return to Chat</a>
    </div>
</body>
</html>