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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $listingService->deleteListing($listing_id);
    } else {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'stock' => $_POST['stock'] ?? 0,
            'condition' => $_POST['condition'] ?? '',
            'category' => $_POST['category'] ?? '',
            'location' => $_POST['location'] ?? '',
            'delivery_method' => $_POST['delivery_method'] ?? '',
            'tags' => $_POST['tags'] ?? '[]'
        ];
        $listingService->updateListing($listing_id, $data);
    }
}

$listing = $listingService->getListing($listing_id);

if (!$listing) {
    echo "Listing not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(trans('Edit Listing')) ?> - ReTrade</title>
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

    <div id="main-content" class="main-content listing-edit-main">
        <main>
            <div class="listing-edit-container">
                <header class="listing-edit-header">
                    <div class="listing-back-links">
                        <a href="/pages/my-listings/view-listing/?id=<?= urlencode($listing_id) ?>" class="listing-back-link"><?= htmlspecialchars(trans('Back to View Listing')) ?></a>
                        <a href="/pages/my-listings/" class="listing-back-link"><?= htmlspecialchars(trans('Back to My Listings')) ?></a>
                    </div>
                    <h1 class="listing-view-title"><?= htmlspecialchars(trans('Edit Listing')) ?></h1>
                </header>

                <form class="listing-edit-form" method="POST" action="/pages/my-listings/edit-listing/?id=<?= urlencode($listing_id) ?>">
                    <div class="listing-field">
                        <label class="listing-label" for="name"><?= htmlspecialchars(trans('Name')) ?></label>
                        <input id="name" name="name" class="listing-input" type="text" value="<?= htmlspecialchars($listing['name'] ?? '') ?>" required>
                    </div>

                    <div class="listing-field">
                        <label class="listing-label" for="description"><?= htmlspecialchars(trans('Description')) ?></label>
                        <textarea id="description" name="description" class="listing-textarea" rows="4" required><?= htmlspecialchars($listing['description'] ?? '') ?></textarea>
                    </div>

                    <div class="listing-grid-two">
                        <div class="listing-field">
                            <label class="listing-label" for="price"><?= htmlspecialchars(trans('Price')) ?> (R)</label>
                            <input id="price" name="price" class="listing-input" type="number" step="0.01" value="<?= htmlspecialchars($listing['price'] ?? '0.00') ?>" required>
                        </div>
                        <div class="listing-field">
                            <label class="listing-label" for="stock"><?= htmlspecialchars(trans('Stock Quantity')) ?></label>
                            <input id="stock" name="stock" class="listing-input" type="number" value="<?= htmlspecialchars($listing['stock'] ?? '0') ?>" required>
                        </div>
                    </div>

                    <div class="listing-grid-two">
                        <div class="listing-field">
                            <label class="listing-label" for="condition"><?= htmlspecialchars(trans('Condition')) ?></label>
                            <div class="listing-select-wrapper">
                                <select id="condition" name="condition" class="listing-select">
                                    <option value="new" <?= ($listing['condition'] ?? '') === 'new' ? 'selected' : '' ?>><?= htmlspecialchars(trans('New')) ?></option>
                                    <option value="used-good" <?= ($listing['condition'] ?? '') === 'used-good' ? 'selected' : '' ?>><?= htmlspecialchars(trans('Used - Good')) ?></option>
                                    <option value="used-fair" <?= ($listing['condition'] ?? '') === 'used-fair' ? 'selected' : '' ?>><?= htmlspecialchars(trans('Used - Fair')) ?></option>
                                </select>
                                <span class="listing-select-icon">▼</span>
                            </div>
                        </div>
                        <div class="listing-field">
                            <label class="listing-label" for="category"><?= htmlspecialchars(trans('Category')) ?></label>
                            <div class="listing-select-wrapper">
                                <select id="category" name="category" class="listing-select">
                                    <option value="clothing" <?= ($listing['category'] ?? '') === 'clothing' ? 'selected' : '' ?>><?= htmlspecialchars(trans('Clothing')) ?></option>
                                    <option value="electronics" <?= ($listing['category'] ?? '') === 'electronics' ? 'selected' : '' ?>><?= htmlspecialchars(trans('Electronics')) ?></option>
                                    <option value="home" <?= ($listing['category'] ?? '') === 'home' ? 'selected' : '' ?>><?= htmlspecialchars(trans('Home & Garden')) ?></option>
                                </select>
                                <span class="listing-select-icon">▼</span>
                            </div>
                        </div>
                    </div>

                    <div class="listing-grid-two">
                        <div class="listing-field">
                            <label class="listing-label" for="location"><?= htmlspecialchars(trans('Location')) ?></label>
                            <input id="location" name="location" class="listing-input" type="text" value="<?= htmlspecialchars($listing['location'] ?? '') ?>" required>
                        </div>
                        <div class="listing-field">
                            <label class="listing-label" for="delivery_method"><?= htmlspecialchars(trans('Delivery Method')) ?></label>
                            <div class="listing-select-wrapper">
                                <select id="delivery_method" name="delivery_method" class="listing-select">
                                    <option value="postnet-collection" <?= ($listing['delivery_method'] ?? '') === 'postnet-collection' ? 'selected' : '' ?>><?= htmlspecialchars(trans('PostNet or Collection')) ?></option>
                                    <option value="collection-only" <?= ($listing['delivery_method'] ?? '') === 'collection-only' ? 'selected' : '' ?>><?= htmlspecialchars(trans('Collection Only')) ?></option>
                                    <option value="courier" <?= ($listing['delivery_method'] ?? '') === 'courier' ? 'selected' : '' ?>><?= htmlspecialchars(trans('Courier')) ?></option>
                                </select>
                                <span class="listing-select-icon">▼</span>
                            </div>
                        </div>
                    </div>

                    <div class="listing-field">
                        <label class="listing-label" for="tags"><?= htmlspecialchars(trans('Tags (comma-separated)')) ?></label>
                        <input id="tags" name="tags" class="listing-input" type="text" value="<?= htmlspecialchars(is_array($listing['tags'] ?? null) ? implode(', ', $listing['tags']) : ($listing['tags'] ?? '')) ?>">
                    </div>

                    <div class="listing-submit-wrap">
                        <button class="listing-submit-btn" type="submit"><?= htmlspecialchars(trans('Update Listing')) ?></button>
                    </div>
                </form>

                <form method="POST" action="/pages/my-listings/edit-listing/?id=<?= urlencode($listing_id) ?>" onsubmit="return confirm('Are you sure you want to delete this listing?');" style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                    <input type="hidden" name="action" value="delete">
                    <button class="listing-delete-btn" type="submit"><?= htmlspecialchars(trans('Delete Listing')) ?></button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>