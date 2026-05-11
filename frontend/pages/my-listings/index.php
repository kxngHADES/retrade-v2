<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';

use Lib\services\ApiService;

$apiService = new ApiService();
$listings = $apiService->get_user_listings($_SESSION['uid']);

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(trans('My Listings')) ?> - ReTrade</title>
    <script>
        (function() {
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        })();
    </script>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/listing.css">
</head>
<body class="antialiased min-h-screen flex listings-page">
    <?php include __DIR__ . '/../../templates/partial/navbar.php'; ?>

    <div id="main-content" class="main-content listings-main">
        <main>
            <div class="listings-container">
                <div class="listings-header">
                    <div>
                        <p class="listings-kicker"><?= htmlspecialchars(trans('Listings')) ?></p>
                        <h1 class="listings-title"><?= htmlspecialchars(trans('My Listings')) ?></h1>
                    </div>
                    <a href="/pages/my-listings/create-listing/" class="listings-create-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span><?= htmlspecialchars(trans('Create Listing')) ?></span>
                    </a>
                </div>

                <?php if (empty($listings)): ?>
                    <div class="listings-empty">
                        <p class="empty-title"><?= htmlspecialchars(trans('You haven\'t listed any items yet.')) ?></p>
                        <a href="/pages/my-listings/create-listing/" class="listings-empty-button"><?= htmlspecialchars(trans('Create Listing')) ?></a>
                    </div>
                <?php else: ?>
                    <div class="listings-grid">
                        <?php foreach ($listings as $listing): ?>
                            <article class="listing-card group">
                                <div class="listing-card-image">
                                    <img src="<?= htmlspecialchars($listing['thumbnail_url'] ?? '/assets/placeholder.jpg') ?>" alt="<?= htmlspecialchars($listing['name'] ?? trans('Listing')) ?>">
                                    <?php if (!empty($listing['condition'])): ?>
                                        <span class="listing-card-badge"><?= htmlspecialchars($listing['condition']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="listing-card-body">
                                    <h2 class="listing-card-title"><?= htmlspecialchars($listing['name'] ?? trans('Listing')) ?></h2>
                                    <?php if (isset($listing['price'])): ?>
                                        <p class="listing-card-price">R <?= number_format((float)$listing['price'], 2) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($listing['location'])): ?>
                                        <div class="listing-card-meta">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                            <span><?= htmlspecialchars($listing['location']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="listing-card-actions">
                                    <a href="/pages/my-listings/view-listing/?id=<?= urlencode($listing['_id']) ?>" class="listing-card-action"><?= htmlspecialchars(trans('View')) ?></a>
                                    <a href="/pages/my-listings/edit-listing/?id=<?= urlencode($listing['_id']) ?>" class="listing-card-action listing-card-action--primary"><?= htmlspecialchars(trans('Edit')) ?></a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>