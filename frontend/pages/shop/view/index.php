<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';

use Lib\services\shop_service;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /pages/shop/');
    exit;
}

$shop_id = $_GET['id'];
$shop_service = new shop_service();
$shop = $shop_service->getShopById($shop_id);

if (!$shop) {
    header('Location: /pages/shop/');
    exit;
}

$products = $shop_service->getShopProducts($shop_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($shop['shop_name']) ?> | ReTrade</title>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/shops.css">
    <script src="/assets/js/global.js" defer></script>
</head>
<body class="shop-view-page">
    <?php require_once __DIR__ . '/../../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="shop-view-shell">
            <header class="shop-view-header">
                <div class="shop-view-info">
                    <p class="shop-view-label">Storefront</p>
                    <h1 class="shop-view-title"><?= htmlspecialchars($shop['shop_name']) ?></h1>
                    <p class="shop-view-subtitle"><?= count($products) > 0 ? count($products) . ' product' . (count($products) === 1 ? '' : 's') . ' available' : 'This store currently has no active products.' ?></p>
                </div>
                <div class="shop-view-actions">
                    <a href="/pages/shop/" class="shop-view-secondary-btn">Back to Stores</a>
                    <a href="/pages/shop/carts/" class="shop-view-primary-btn">View Cart</a>
                </div>
            </header>

            <?php if (!empty($products)): ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <?php $isAvailable = $product['is_active'] && $product['stock_quantity'] > 0; ?>
                        <article class="product-card <?= $isAvailable ? '' : 'product-card--disabled' ?>">
                            <div class="product-card-body">
                                <div class="product-card-top">
                                    <h2 class="product-card-title"><?= htmlspecialchars($product['name']) ?></h2>
                                    <span class="product-card-price">R<?= htmlspecialchars(number_format($product['price'], 2)) ?></span>
                                </div>
                                <p class="product-card-description"><?= nl2br(htmlspecialchars($product['description'] ?: 'No description available.')) ?></p>
                                <p class="product-card-meta"><?= $isAvailable ? htmlspecialchars($product['stock_quantity']) . ' left in stock' : 'Not available' ?></p>
                                <div class="product-card-actions">
                                    <?php if ($isAvailable): ?>
                                        <div class="quantity-control">
                                            <button type="button" class="qty-btn" onclick="const input = this.nextElementSibling; if (input.value > 1) input.value = Number(input.value) - 1;">-</button>
                                            <input type="number" id="qty-<?= htmlspecialchars($product['product_id']) ?>" class="qty-input" value="1" min="1" max="<?= htmlspecialchars($product['stock_quantity']) ?>">
                                            <button type="button" class="qty-btn" onclick="const input = this.previousElementSibling; if (Number(input.value) < <?= htmlspecialchars($product['stock_quantity']) ?>) input.value = Number(input.value) + 1;">+</button>
                                        </div>
                                        <button type="button" class="product-add-btn" onclick="CartAPI.add('<?= htmlspecialchars($shop_id) ?>', '<?= htmlspecialchars($product['product_id']) ?>', <?= htmlspecialchars($product['price']) ?>, document.getElementById('qty-<?= htmlspecialchars($product['product_id']) ?>').value)">Add to cart</button>
                                    <?php else: ?>
                                        <span class="text-muted">Currently unavailable</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="shops-empty">
                    <p>No products available in this shop right now.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script src="/assets/js/cart.js"></script>
</body>
</html>
