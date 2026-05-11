<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';

use Lib\services\listing_service;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /pages/my-listings/');
    exit;
}

$listing_id = $_GET['id'];
$listingService = new listing_service();
$listing = $listingService->getListing($listing_id);

if (!$listing) {
    echo "Listing not found.";
    exit;
}

$images = [];
if (!empty($listing['thumbnail_url'])) {
    $images[] = $listing['thumbnail_url'];
}
if (!empty($listing['list_of_image_url']) && is_array($listing['list_of_image_url'])) {
    foreach ($listing['list_of_image_url'] as $img) {
        if (!empty($img)) {
            $images[] = $img;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(trans('View Listing')) ?> - ReTrade</title>
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
<body class="antialiased min-h-screen flex">
    <?php include __DIR__ . '/../../../templates/partial/navbar.php'; ?>

    <div id="main-content" class="main-content listing-view-main">
        <main>
            <div class="listing-view-container">
                <div class="listing-view-header">
                    <div>
                        <p class="listings-kicker"><?= htmlspecialchars(trans('Listings')) ?></p>
                        <h1 class="listing-view-title"><?= htmlspecialchars($listing['name'] ?? trans('Listing')) ?></h1>
                        <p class="listing-view-meta">
                            <?= htmlspecialchars($listing['category'] ?? trans('Category')) ?>
                            <?php if (!empty($listing['location'])): ?>
                                · <?= htmlspecialchars($listing['location']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="/pages/my-listings/edit-listing/?id=<?= urlencode($listing_id) ?>" class="listing-edit-btn"><?= htmlspecialchars(trans('Edit Listing')) ?></a>
                </div>

                <div class="listing-view-grid">
                    <section class="listing-gallery">
                        <div class="listing-main-image-wrap">
                            <?php if (!empty($images)): ?>
                                <img class="listing-main-image" src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($listing['name'] ?? trans('Listing')) ?>">
                            <?php else: ?>
                                <div class="listing-image-placeholder"><?= htmlspecialchars(trans('No image available')) ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if (count($images) > 1): ?>
                            <div class="listing-thumb-scroll" role="list">
                                <?php foreach ($images as $index => $src): ?>
                                    <button type="button" class="listing-thumb-button<?= $index === 0 ? ' listing-thumb-button--active' : '' ?>" data-src="<?= htmlspecialchars($src) ?>">
                                        <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($listing['name'] ?? trans('Listing image')) ?>">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="listing-details">
                        <div class="listing-detail-row">
                            <div class="listing-detail-block">
                                <span class="detail-label"><?= htmlspecialchars(trans('Price')) ?></span>
                                <p class="detail-value">R <?= number_format((float)($listing['price'] ?? 0), 2) ?></p>
                            </div>
                            <div class="listing-detail-block">
                                <span class="detail-label"><?= htmlspecialchars(trans('Stock')) ?></span>
                                <p class="detail-value"><?= htmlspecialchars($listing['stock'] ?? '0') ?></p>
                            </div>
                        </div>

                        <div class="listing-detail-section">
                            <h2 class="listing-section-heading"><?= htmlspecialchars(trans('Description')) ?></h2>
                            <p class="listing-section-copy"><?= nl2br(htmlspecialchars($listing['description'] ?? trans('No description provided.'))) ?></p>
                        </div>

                        <div class="listing-detail-grid">
                            <div class="listing-detail-item">
                                <span class="detail-label"><?= htmlspecialchars(trans('Condition')) ?></span>
                                <p class="detail-value"><?= htmlspecialchars($listing['condition'] ?? trans('N/A')) ?></p>
                            </div>
                            <div class="listing-detail-item">
                                <span class="detail-label"><?= htmlspecialchars(trans('Delivery Method')) ?></span>
                                <p class="detail-value"><?= htmlspecialchars($listing['delivery_method'] ?? trans('N/A')) ?></p>
                            </div>
                            <div class="listing-detail-item">
                                <span class="detail-label"><?= htmlspecialchars(trans('Location')) ?></span>
                                <p class="detail-value"><?= htmlspecialchars($listing['location'] ?? trans('N/A')) ?></p>
                            </div>
                        </div>

                        <?php if (!empty($listing['tags']) && is_array($listing['tags'])): ?>
                            <div class="listing-tags">
                                <?php foreach ($listing['tags'] as $tag): ?>
                                    <span class="listing-tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mainImage = document.querySelector('.listing-main-image');
            const thumbButtons = Array.from(document.querySelectorAll('.listing-thumb-button'));

            thumbButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const src = this.dataset.src;
                    if (!mainImage || !src) return;
                    mainImage.src = src;

                    thumbButtons.forEach(button => button.classList.remove('listing-thumb-button--active'));
                    this.classList.add('listing-thumb-button--active');
                });
            });
        });
    </script>
</body>
</html>
