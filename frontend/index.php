<?php
session_start();
require_once __DIR__ . '/config/bootstrap.php';
use Lib\services\ApiService;

$isLoggedIn = isset($_SESSION['uid']);
$uid = $isLoggedIn ? $_SESSION['uid'] : null;

$apiService = new ApiService();
$listings = $apiService->get_recommendations_or_latest($uid, 1);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(trans('Home') ?? 'ReTrade Home') ?></title>
    <script>
        (function() {
            var t = localStorage.getItem('theme');
            if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
            else document.documentElement.removeAttribute('data-theme');
        })();
    </script>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/home.css">
</head>
<body class="antialiased min-h-screen flex home-page">
    <?php include __DIR__ . '/templates/partial/navbar.php'; ?>

    <div id="main-content" class="main-content home-main">
        <main>
            <div class="home-shell">
                <section class="home-search-panel">
                    <form class="home-form-grid" action="/search.php" method="GET">
                        <div class="home-search-row">
                            <div class="home-input-group">
                                <label class="home-input-label" for="query"><?= htmlspecialchars(trans('Search') ?? 'Search') ?></label>
                                <div class="home-input-with-icon">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2" fill="none" />
                                        <path d="M16 16l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                    <input id="query" name="query" class="home-input" type="text" placeholder="<?= htmlspecialchars(trans('Search for items...')) ?>" required>
                                </div>
                            </div>
                            <div class="home-search-actions">
                                <button type="button" class="home-filter-toggle" data-target="home-search-filters"><?= htmlspecialchars(trans('Filters') ?? 'Filters') ?></button>
                                <button type="submit" class="home-search-button"><?= htmlspecialchars(trans('Search')) ?></button>
                            </div>
                        </div>

                        <div class="home-search-filters home-search-filters--collapsed" id="home-search-filters">
                            <div class="home-filter-grid">
                                <div class="home-input-group">
                                    <label class="home-input-label" for="category"><?= htmlspecialchars(trans('Category') ?? 'Category') ?></label>
                                    <select id="category" name="category" class="home-select">
                                        <option value=""><?= htmlspecialchars(trans('All Categories') ?? 'All Categories') ?></option>
                                        <option value="Electronics"><?= htmlspecialchars(trans('Electronics')) ?></option>
                                        <option value="Vehicles"><?= htmlspecialchars(trans('Vehicles') ?? 'Vehicles') ?></option>
                                        <option value="Home"><?= htmlspecialchars(trans('Home') ?? 'Home') ?></option>
                                        <option value="Fashion"><?= htmlspecialchars(trans('Fashion') ?? 'Fashion') ?></option>
                                    </select>
                                </div>

                                <div class="home-input-group">
                                    <label class="home-input-label" for="condition"><?= htmlspecialchars(trans('Condition') ?? 'Condition') ?></label>
                                    <select id="condition" name="condition" class="home-select">
                                        <option value=""><?= htmlspecialchars(trans('Any Condition') ?? 'Any Condition') ?></option>
                                        <option value="New"><?= htmlspecialchars(trans('New')) ?></option>
                                        <option value="Used - Good"><?= htmlspecialchars(trans('Used - Good')) ?></option>
                                        <option value="Used - Fair"><?= htmlspecialchars(trans('Used - Fair')) ?></option>
                                    </select>
                                </div>

                                <div class="home-input-group">
                                    <label class="home-input-label" for="location"><?= htmlspecialchars(trans('Location') ?? 'Location') ?></label>
                                    <div class="home-input-with-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 21s-6-5.686-6-10a6 6 0 1 1 12 0c0 4.314-6 10-6 10z" fill="none" stroke="currentColor" stroke-width="2" />
                                            <circle cx="12" cy="11" r="2" fill="currentColor" />
                                        </svg>
                                        <input id="location" name="location" class="home-input" type="text" placeholder="<?= htmlspecialchars(trans('City or Postal Code') ?? 'City or Postal Code') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="home-filter-grid">
                                <div class="home-input-group">
                                    <label class="home-input-label" for="min_price"><?= htmlspecialchars(trans('Min Price') ?? 'Min Price') ?></label>
                                    <input id="min_price" name="min_price" class="home-input" type="number" min="0" placeholder="0">
                                </div>
                                <div class="home-input-group">
                                    <label class="home-input-label" for="max_price"><?= htmlspecialchars(trans('Max Price') ?? 'Max Price') ?></label>
                                    <input id="max_price" name="max_price" class="home-input" type="number" min="0" placeholder="<?= htmlspecialchars(trans('Max') ?? 'Max') ?>">
                                </div>
                            </div>
                        </div>
                    </form>
                </section>

                <section>
                    <div class="home-feature-header">
                        <h2 class="home-feature-title"><?= htmlspecialchars(trans('Featured items') ?? 'Featured items') ?></h2>
                    </div>
                    <div class="home-grid" id="listing-container">
                        <?php foreach ($listings as $index => $item): ?>
                            <a href="/view/?listing_id=<?= urlencode($item['_id']) ?>" class="home-card <?= $index >= 20 ? 'hidden' : '' ?>" data-index="<?= $index ?>">
                                <div class="home-card-image">
                                    <img src="<?= htmlspecialchars($item['thumbnail_url'] ?? '/assets/placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['name'] ?? trans('Listing')) ?>">
                                </div>
                                <div class="home-card-content">
                                    <h3 class="home-card-title"><?= htmlspecialchars($item['name'] ?? trans('Listing')) ?></h3>
                                    <p class="home-card-price">R <?= number_format((float)($item['price'] ?? 0), 2) ?></p>
                                    <?php if (!empty($item['location'])): ?>
                                        <div class="home-card-meta">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 21s-6-5.686-6-10a6 6 0 1 1 12 0c0 4.314-6 10-6 10z" fill="none" stroke="currentColor" stroke-width="2" />
                                                <circle cx="12" cy="11" r="2" fill="currentColor" />
                                            </svg>
                                            <span><?= htmlspecialchars($item['location']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <?php if (count($listings) > 20): ?>
                    <div class="home-more-wrapper">
                        <button id="see-more-btn" class="home-see-more"><?= htmlspecialchars(trans('See More') ?? 'See More') ?></button>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="/assets/js/index_listings.js"></script>
    <script src="/assets/js/search_filters.js"></script>
</body>
</html>
