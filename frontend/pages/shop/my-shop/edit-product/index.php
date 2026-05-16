<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../utils/protected_route.php';
require_once __DIR__ . '/../../../../utils/id_verified_screens.php';

use Lib\services\shop_service;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /pages/shop/my-shop/');
    exit;
}

$product_id = $_GET['id'];
$shop_service = new shop_service();
$product = $shop_service->getProduct($product_id);
$error_msg = '';

if (!$product) {
    echo "Product not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'price' => floatval($_POST['price'] ?? 0),
        'stock_quantity' => intval($_POST['stock_quantity'] ?? 0),
        'product_id' => $product_id
    ];
    $shop_service->updateProduct($data);
    $product = $shop_service->getProduct($product_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Product - ReTrade</title>
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
                        <h1 class="shop-product-header-title">Edit Product</h1>
                        <p class="shop-dashboard-copy">Update your product details and inventory information.</p>
                    </div>
                    <a href="/pages/shop/my-shop/view-product.php?id=<?= urlencode($product_id) ?>" class="shop-product-back-link">&larr; Back to product</a>
                </header>

                <?php if ($error_msg): ?>
                    <div class="shop-dashboard-error"><?= htmlspecialchars($error_msg) ?></div>
                <?php endif; ?>

                <form class="shop-product-form" method="POST" action="/pages/shop/my-shop/edit-product/?id=<?= urlencode($product_id) ?>">
                    <div class="shop-product-field">
                        <label class="shop-dashboard-field-label" for="name">Name</label>
                        <input id="name" name="name" type="text" class="shop-product-input" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
                    </div>
                    <div class="shop-product-field">
                        <label class="shop-dashboard-field-label" for="description">Description</label>
                        <textarea id="description" name="description" class="shop-product-textarea" rows="5" required><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>
                    <div class="shop-product-grid shop-product-grid--form">
                        <div class="shop-product-field">
                            <label class="shop-dashboard-field-label" for="price">Price (R)</label>
                            <input id="price" name="price" type="number" step="0.01" min="0" class="shop-product-input" required value="<?= htmlspecialchars($product['price'] ?? '0.00') ?>">
                        </div>
                        <div class="shop-product-field">
                            <label class="shop-dashboard-field-label" for="stock_quantity">Stock Quantity</label>
                            <input id="stock_quantity" name="stock_quantity" type="number" min="0" class="shop-product-input" required value="<?= htmlspecialchars($product['stock_quantity'] ?? '0') ?>">
                        </div>
                    </div>
                    <div class="shop-product-actions">
                        <button type="submit" class="shop-dashboard-button shop-dashboard-button--primary">Update Product</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
