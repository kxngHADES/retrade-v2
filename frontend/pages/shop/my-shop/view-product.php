<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';
require_once __DIR__ . '/../../../utils/id_verified_screens.php';

use Lib\services\shop_service;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /pages/shop/my-shop/');
    exit;
}

$product_id = $_GET['id'];
$shop_service = new shop_service();
$product = $shop_service->getProduct($product_id);

if (!$product) {
    echo "Product not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>View Product - ReTrade</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/shops.css">
    <script src="/assets/js/global.js" defer></script>
</head>
<body class="shop-product-page">
    <?php require_once __DIR__ . '/../../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="shop-product-shell">
            <section class="shop-product-panel">
                <header class="shop-product-header">
                    <div>
                        <p class="shop-dashboard-label">Product Details</p>
                        <h1 class="shop-product-header-title"><?= htmlspecialchars($product['name'] ?? '') ?></h1>
                        <p class="shop-dashboard-copy">Review product details and make changes when needed.</p>
                    </div>
                    <div class="shop-product-actions">
                        <a href="/pages/shop/my-shop/" class="shop-dashboard-button shop-dashboard-button--secondary">Back to Shop</a>
                        <a href="/pages/shop/my-shop/edit-product/?id=<?= urlencode($product_id) ?>" class="shop-dashboard-button shop-dashboard-button--primary">Edit Product</a>
                    </div>
                </header>

                <div class="shop-product-detail-card">
                    <div class="shop-product-detail-row">
                        <span class="shop-product-detail-label">Price</span>
                        <span class="shop-product-detail-value">R<?= htmlspecialchars(number_format($product['price'], 2)) ?></span>
                    </div>
                    <div class="shop-product-detail-row">
                        <span class="shop-product-detail-label">Stock Quantity</span>
                        <span class="shop-product-detail-value"><?= htmlspecialchars($product['stock_quantity'] ?? '0') ?></span>
                    </div>
                    <div class="shop-product-detail-row">
                        <span class="shop-product-detail-label">Status</span>
                        <span class="shop-product-detail-value"><?= !empty($product['is_active']) ? 'Active' : 'Inactive' ?></span>
                    </div>
                    <div class="shop-product-detail-row">
                        <span class="shop-product-detail-label">Description</span>
                        <span class="shop-product-detail-value"><?= nl2br(htmlspecialchars($product['description'] ?? 'No description available.')) ?></span>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
