<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';

use Lib\services\shop_service;

$shop_service = new shop_service();
$current_uid = $_SESSION['uid'] ?? '';
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$hasShop = $shop_service->hasShop($current_uid);
$shops = $shop_service->getAllShopsExcludingUser($offset, $current_uid);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ReTrade - Shops</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/shops.css">
    <script src="/assets/js/global.js" defer></script>
</head>
<body class="shops-page">
    <?php require_once __DIR__ . '/../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="shops-shell">
            <header class="shops-header">
                <div class="shops-header-copy">
                    <p class="shops-label">Available Stores</p>
                    <h1 class="shops-title">Shops</h1>
                </div>
                <a href="<?= $hasShop ? '/pages/shop/my-shop/' : '/pages/shop/create-shop/' ?>" class="shops-cta"><?= $hasShop ? 'My Shop' : 'Create Store' ?></a>
            </header>

            <p class="shops-intro">Discover local sellers and unique items in the marketplace.</p>

            <?php if (!empty($shops)): ?>
                <div class="shops-grid">
                    <?php foreach ($shops as $shop): ?>
                        <?php
                        $initials = '';
                        $parts = preg_split('/\s+/', trim($shop['shop_name']));
                        if (!empty($parts[0])) {
                            $initials .= strtoupper(substr($parts[0], 0, 1));
                        }
                        if (!empty($parts[1])) {
                            $initials .= strtoupper(substr($parts[1], 0, 1));
                        }
                        if ($initials === '' && $shop['shop_name'] !== '') {
                            $initials = strtoupper(substr($shop['shop_name'], 0, 1));
                        }
                        ?>
                        <article class="shop-card">
                            <div class="shop-card-top">
                                <div class="shop-avatar"><?= htmlspecialchars($initials) ?></div>
                                <div class="shop-card-info">
                                    <p class="shop-card-name"><?= htmlspecialchars($shop['shop_name']) ?></p>
                                </div>
                            </div>
                            <a href="/pages/shop/view/?id=<?= urlencode($shop['shop_id']) ?>" class="shop-card-button">Visit Store</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="shops-empty">
                    <p>No other stores are available right now.</p>
                </div>
            <?php endif; ?>

            <div class="shops-pagination">
                <?php if ($offset > 0): ?>
                    <a class="shops-pagination-link" href="?offset=<?= max(0, $offset - 20) ?>">Previous</a>
                <?php else: ?>
                    <span class="shops-pagination-link shops-pagination-link--disabled">Previous</span>
                <?php endif; ?>

                <span class="shops-pagination-info">Page <?= floor($offset / 20) + 1 ?></span>

                <?php if (count($shops) === 20): ?>
                    <a class="shops-pagination-link" href="?offset=<?= $offset + 20 ?>">Next</a>
                <?php else: ?>
                    <span class="shops-pagination-link shops-pagination-link--disabled">Next</span>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
