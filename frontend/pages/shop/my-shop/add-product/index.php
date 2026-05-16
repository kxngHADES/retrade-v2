<?php

require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../utils/protected_route.php';
require_once __DIR__ . '/../../../../utils/id_verified_screens.php';

use Lib\services\shop_service;

$uid = $_SESSION['uid'];
$shop_service = new shop_service();
$shop = $shop_service->getShop($uid);
$shop_id = $shop['shop_id'];
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $quantity = $_POST['quantity'] ?? '';

    if ($name === '' || $price === '' || $quantity === '') {
        $error_msg = 'Please fill in all required fields.';
    } else {
        $shop_service->addShopProduct($shop_id, $name, $description, $price, $quantity);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Add Product - ReTrade</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/shops.css">
    <script src="/assets/js/global.js" defer></script>
</head>

<body class="shop-product-page">
    <?php require_once __DIR__ . '/../../../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="shop-product-shell">
            <section class="shop-product-panel">
                <header class="shop-product-header">
                    <div>
                        <p class="shop-dashboard-label">My Shop</p>
                        <h1 class="shop-product-header-title">Add Product</h1>
                        <p class="shop-dashboard-copy">Add a new item to your shop so buyers can discover your listings.</p>
                    </div>
                    <a href="/pages/shop/my-shop/" class="shop-product-back-link">&larr; Back to My shop</a>
                </header>

                <?php if ($error_msg): ?>
                    <div class="shop-dashboard-error"><?= htmlspecialchars($error_msg) ?></div>
                <?php endif; ?>

                <form class="shop-product-form" action="" method="post">
                    <div class="shop-product-field">
                        <label class="shop-dashboard-field-label" for="name">Product Name</label>
                        <input id="name" name="name" type="text" class="shop-product-input" placeholder="Product name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>

                    <div class="shop-product-field">
                        <label class="shop-dashboard-field-label" for="description">Description</label>
                        <textarea id="description" name="description" class="shop-product-textarea" rows="5" placeholder="Describe the item"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="shop-product-grid shop-product-grid--form">
                        <div class="shop-product-field">
                            <label class="shop-dashboard-field-label" for="price">Price (R)</label>
                            <input id="price" name="price" type="number" step="0.01" min="0" class="shop-product-input" placeholder="0.00" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                        </div>
                        <div class="shop-product-field">
                            <label class="shop-dashboard-field-label" for="quantity">Quantity</label>
                            <input id="quantity" name="quantity" type="number" step="1" min="0" class="shop-product-input" value="<?= htmlspecialchars($_POST['quantity'] ?? '1') ?>">
                        </div>
                    </div>

                    <div class="shop-product-actions">
                        <button type="submit" class="shop-dashboard-button shop-dashboard-button--primary">Submit Product</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
