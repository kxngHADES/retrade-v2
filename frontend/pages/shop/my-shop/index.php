<?php

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';
require_once __DIR__ . '/../../../utils/id_verified_screens.php';

use Lib\services\shop_service;

$shop_service = new shop_service();
$uid = $_SESSION['uid'];

$has_shop = $shop_service->hasShop($uid);
$error_msg = "";

if (!$has_shop && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_shop'])) {
    $shop_name = trim($_POST['shop_name'] ?? '');
    if (!empty($shop_name)) {
        $result = $shop_service->createShop($uid, $shop_name);
        if (isset($result['success']) && $result['success']) {
            header('Location: /pages/shop/my-shop/');
            exit();
        } else {
            $error_msg = $result['error'] ?? 'Failed to create shop. Please try again.';
        }
    } else {
        $error_msg = "Shop name cannot be empty.";
    }
}

if ($has_shop || (isset($result['success']) && $result['success'])) {
    $shop = $shop_service->getShop($uid);
    $shop_name = $shop['shop_name'];
    $shop_id = $shop['shop_id'];
    $products = $shop_service->getShopProducts($shop_id);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ReTrade - My Shop Dashboard</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/shops.css">
    <script src="/assets/js/global.js" defer></script>
</head>

<?php if (!empty($_SESSION['flash_verify'])): ?>
    <?php unset($_SESSION['flash_verify']); ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            alert('You need to verify your ID before accessing this page.');
            window.location.href = '/pages/profile/verify_id';
        });
    </script>
<?php endif; ?>

<body class="shop-dashboard-page">
    <?php require_once __DIR__ . '/../../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="shop-dashboard-shell">
            <?php if (!$has_shop && empty($shop)): ?>
                <section class="shop-dashboard-panel shop-dashboard-panel--form">
                    <header class="shop-dashboard-header">
                        <div>
                            <p class="shop-dashboard-label">My Shop</p>
                            <h1 class="shop-dashboard-title">Create your store</h1>
                        </div>
                    </header>

                    <p class="shop-dashboard-copy">Create a shop to start listing products and selling in the ReTrade marketplace.</p>

                    <?php if (!empty($error_msg)): ?>
                        <div class="shop-dashboard-error"><?= htmlspecialchars($error_msg) ?></div>
                    <?php endif; ?>

                    <form class="shop-dashboard-form" method="POST" action="">
                        <label class="shop-dashboard-field-label" for="shop_name">Shop Name</label>
                        <input id="shop_name" name="shop_name" type="text" class="shop-dashboard-input" placeholder="Sfiso's spaza" required>
                        <button type="submit" name="create_shop" class="shop-dashboard-button shop-dashboard-button--primary">Create Shop</button>
                    </form>
                </section>
            <?php else: ?>
                <section class="shop-dashboard-overview">
                    <div class="shop-dashboard-header shop-dashboard-header--space">
                        <div>
                            <p class="shop-dashboard-label">My Shop</p>
                            <h1 class="shop-dashboard-title"><?= htmlspecialchars($shop_name) ?></h1>
                            <p class="shop-dashboard-copy">Manage your products, view stock levels, and keep your store updated.</p>
                        </div>
                        <div class="shop-dashboard-actions">
                            <a href="/pages/shop/my-shop/add-product/" class="shop-dashboard-button shop-dashboard-button--primary">Add Product</a>
                        </div>
                    </div>

                    <div class="shop-dashboard-summary-grid">
                        <article class="shop-dashboard-stat-card">
                            <p class="shop-dashboard-stat-label">Products</p>
                            <p class="shop-dashboard-stat-value"><?= count($products) ?></p>
                        </article>
                        <article class="shop-dashboard-stat-card">
                            <p class="shop-dashboard-stat-label">Active items</p>
                            <p class="shop-dashboard-stat-value"><?= count(array_filter($products, fn($item) => $item['is_active'])) ?></p>
                        </article>
                    </div>
                </section>

                <section class="shop-dashboard-products">
                    <div class="shop-dashboard-products-header">
                        <div>
                            <h2 class="shop-dashboard-section-title">Your Products</h2>
                            <p class="shop-dashboard-copy">Use this dashboard to keep track of your shop inventory.</p>
                        </div>
                    </div>

                    <?php if (!empty($products)): ?>
                        <div class="shop-product-grid">
                            <?php foreach ($products as $product): ?>
                                <article class="shop-product-card <?= $product['is_active'] ? '' : 'shop-product-card--inactive' ?>">
                                    <div class="shop-product-card-body">
                                        <div class="shop-product-card-top">
                                            <h3 class="shop-product-card-title"><?= htmlspecialchars($product['name']) ?></h3>
                                            <span class="shop-product-price">R<?= htmlspecialchars(number_format($product['price'], 2)) ?></span>
                                        </div>
                                        <p class="shop-product-card-description"><?= htmlspecialchars($product['description'] ?: 'No description provided.') ?></p>
                                        <div class="shop-product-meta">
                                            <span><?= htmlspecialchars($product['stock_quantity']) ?> in stock</span>
                                            <span><?= $product['is_active'] ? 'Active' : 'Inactive' ?></span>
                                        </div>
                                    </div>
                                    <div class="shop-product-card-footer">
                                        <a href="/pages/shop/my-shop/view-product.php?id=<?= urlencode($product['product_id']) ?>" class="shop-product-card-link">View Product</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="shop-dashboard-empty">
                            <p>No products have been added yet.</p>
                            <a href="/pages/shop/my-shop/add-product/" class="shop-dashboard-button shop-dashboard-button--secondary">Add your first product</a>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
