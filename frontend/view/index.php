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
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <script src="/assets/js/global.js" defer></script>
</head>
<body class="home-page">
    <?php require_once __DIR__ . '/../templates/partial/navbar.php'; ?>

    <main class="main-content" id="main-content">
        <div class="home-shell listing-view-shell">
            <section class="listing-view-card listing-view-card-bleed">
                <div class="listing-view-hero">
                    <div class="home-feature-header">
                        <a href="/" class="home-hero-cta">← Back to Home</a>
                    </div>

                    <div class="listing-view-image">
                        <img src="<?= htmlspecialchars($listing['thumbnail_url'] ?? 'https://via.placeholder.com/600x400') ?>" alt="<?= htmlspecialchars($listing['name']) ?>">
                    </div>

                    <div class="listing-view-intro">
                        <h1 class="listing-view-title"><?= htmlspecialchars($listing['name']) ?></h1>
                        <p class="listing-view-price">R<?= htmlspecialchars($listing['price']) ?></p>
                    </div>

                    <div class="listing-view-meta">
                        <div class="listing-view-meta-item">
                            <span class="listing-view-meta-label">Category</span>
                            <span class="listing-view-meta-value"><?= htmlspecialchars($listing['category'] ?? 'N/A') ?></span>
                        </div>
                        <div class="listing-view-meta-item">
                            <span class="listing-view-meta-label">Condition</span>
                            <span class="listing-view-meta-value"><?= htmlspecialchars($listing['condition'] ?? 'N/A') ?></span>
                        </div>
                        <div class="listing-view-meta-item">
                            <span class="listing-view-meta-label">Location</span>
                            <span class="listing-view-meta-value"><?= htmlspecialchars($listing['location'] ?? 'N/A') ?></span>
                        </div>
                        <div class="listing-view-meta-item">
                            <span class="listing-view-meta-label">Delivery</span>
                            <span class="listing-view-meta-value"><?= htmlspecialchars($listing['delivery_method'] ?? 'N/A') ?></span>
                        </div>
                        <div class="listing-view-meta-item">
                            <span class="listing-view-meta-label">Stock</span>
                            <span class="listing-view-meta-value"><?= htmlspecialchars($listing['stock'] ?? '0') ?> unit<?= htmlspecialchars($listing['stock'] ?? '0') === '1' ? '' : 's' ?></span>
                        </div>
                    </div>

                    <div class="listing-view-description">
                        <h2>Description</h2>
                        <p><?= nl2br(htmlspecialchars($listing['description'] ?? 'No description.')) ?></p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="listing-view-action">
        <?php if (!$isOwner): ?>
            <form method="POST" class="listing-view-action-form">
                <button type="submit" name="start_chat" class="view-action-button">Message Seller</button>
            </form>
        <?php else: ?>
            <div class="view-action-note">This is your listing.</div>
        <?php endif; ?>
    </footer>
</body>
</html>
