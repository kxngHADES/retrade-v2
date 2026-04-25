<?php
session_start();
require_once __DIR__ . '/../config/bootstrap.php';

use Lib\services\listing_service;

$listingId = $_GET['listing_id'] ?? null;
$uid = $_SESSION['uid'] ?? null;

$listingService = new listing_service();
$viewData = $listingService->handleViewListingProcess($uid, $listingId);

$listing = $viewData['listing'];
$isOwner = $viewData['isOwner'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($listing['name']) ?> - Retrade</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .view-container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .back { text-decoration: none; color: #128C7E; font-weight: bold; margin-bottom: 20px; display: inline-block; }
        img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 8px; }
        h1 { margin-top: 15px; }
        .price { font-size: 1.5em; font-weight: bold; color: #2E7D32; margin: 10px 0; }
        .details { margin: 20px 0; color: #555; line-height: 1.6; }
        .desc { background: #fafafa; padding: 15px; border-radius: 5px; }
        .start-chat { background: #128C7E; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em; display: block; width: 100%; text-align: center; }
        .start-chat:hover { background: #075E54; }
    </style>
</head>
<body>

    <div class="view-container">
        <a href="/" class="back">&#8592; Back to Home</a>
        <img src="<?= htmlspecialchars($listing['thumbnail_url'] ?? 'https://via.placeholder.com/600x400') ?>" alt="<?= htmlspecialchars($listing['name']) ?>">
        
        <h1><?= htmlspecialchars($listing['name']) ?></h1>
        <p class="price">R<?= htmlspecialchars($listing['price']) ?></p>

        <div class="details">
            <strong>Category:</strong> <?= htmlspecialchars($listing['category'] ?? 'N/A') ?><br>
            <strong>Condition:</strong> <?= htmlspecialchars($listing['condition'] ?? 'N/A') ?><br>
            <strong>Location:</strong> <?= htmlspecialchars($listing['location'] ?? 'N/A') ?><br>
            <strong>Delivery:</strong> <?= htmlspecialchars($listing['delivery_method'] ?? 'N/A') ?><br>
            <strong>Stock:</strong> <?= htmlspecialchars($listing['stock'] ?? '0') ?>
        </div>

        <div class="desc">
            <h3>Description</h3>
            <p><?= nl2br(htmlspecialchars($listing['description'] ?? 'No description.')) ?></p>
        </div>

        <?php if (!$isOwner): ?>
            <form method="POST" style="margin-top: 20px;">
                <button type="submit" name="start_chat" class="start-chat">Message Seller</button>
            </form>
        <?php else: ?>
            <p style="margin-top: 20px; color: #777; font-weight: bold;">This is your listing.</p>
        <?php endif; ?>
    </div>

</body>
</html>
