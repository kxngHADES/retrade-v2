<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';

use Lib\services\shop_service;

$shop_service = new shop_service();
$uid = $_SESSION['uid'];
$carts = $shop_service->getCarts($uid);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Carts - ReTrade</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/shops.css">
    <script src="/assets/js/global.js" defer></script>
</head>
<body class="shop-cart-page">
    <?php require_once __DIR__ . '/../../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="shop-cart-shell">
            <header class="shop-cart-header">
                <div class="shop-cart-title-group">
                    <p class="shop-dashboard-label">Shopping</p>
                    <h1 class="shop-dashboard-title">My Store Carts</h1>
                </div>
                <a href="/pages/shop/" class="shop-dashboard-button shop-dashboard-button--secondary">Back to Shops</a>
            </header>

            <?php if (!empty($carts)): ?>
                <div class="shop-cart-grid">
                    <?php foreach ($carts as $cart): ?>
                        <article class="shop-cart-card">
                            <div>
                                <p class="shop-cart-card-label">Shop</p>
                                <h2 class="shop-cart-card-title"><?= htmlspecialchars($cart['shop_name']) ?></h2>
                            </div>
                            <a href="/pages/shop/carts/view/?id=<?= urlencode($cart['cart_id']) ?>" class="shop-dashboard-button shop-dashboard-button--primary">View Cart</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="shops-empty">
                    <p>You don't have any store carts yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
