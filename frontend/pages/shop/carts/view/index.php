<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../utils/protected_route.php';

use Lib\services\shop_service;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /pages/shop/carts/');
    exit;
}

$cart_id = $_GET['id'];
$shop_service = new shop_service();

$cart_items = $shop_service->getCartItems($cart_id);

$total_amount = 0.0;
$shop_id = '';
foreach ($cart_items as $item) {
    $total_amount += $item['price_snapshot'] * $item['quantity'];
    if (empty($shop_id)) {
        $product = $shop_service->getProduct($item['product_id']);
        $shop_id = $product['shop_id'];
    }
}

$seller_uid = '';
if (!empty($shop_id)) {
    $db = \Lib\db\Database::getConnection();
    $stmt = $db->prepare("SELECT BIN_TO_UUID(uid) as seller_uid FROM shops WHERE shop_id = UUID_TO_BIN(:shop_id)");
    $stmt->execute(['shop_id' => $shop_id]);
    $shopRow = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($shopRow) {
        $seller_uid = $shopRow['seller_uid'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cart Details - ReTrade</title>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/shops.css">
    <script src="/assets/js/global.js" defer></script>
</head>
<body class="shop-cart-page">
    <?php require_once __DIR__ . '/../../../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="shop-cart-shell">
            <header class="shop-cart-header">
                <div class="shop-cart-title-group">
                    <p class="shop-dashboard-label">Shopping</p>
                    <h1 class="shop-dashboard-title">Cart Details</h1>
                </div>
                <a href="/pages/shop/carts/" class="shop-dashboard-button shop-dashboard-button--secondary">Back to My Carts</a>
            </header>

            <?php if (!empty($cart_items)): ?>
                <div class="shop-cart-table-wrap">
                    <table class="shop-cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td>R<?= number_format($item['price_snapshot'], 2) ?></td>
                                    <td>
                                        <div class="cart-quantity-input">
                                            <input type="number" id="qty-<?= htmlspecialchars($item['item_id']) ?>" value="<?= htmlspecialchars($item['quantity']) ?>" min="1">
                                            <button type="button" class="shop-dashboard-button shop-dashboard-button--secondary shop-cart-action" onclick="CartAPI.update('<?= htmlspecialchars($item['item_id']) ?>', document.getElementById('qty-<?= htmlspecialchars($item['item_id']) ?>').value)">Update</button>
                                        </div>
                                    </td>
                                    <td>R<?= number_format($item['price_snapshot'] * $item['quantity'], 2) ?></td>
                                    <td>
                                        <button type="button" class="shop-dashboard-button shop-dashboard-button--secondary shop-cart-action" onclick="CartAPI.remove('<?= htmlspecialchars($item['item_id']) ?>')">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="shop-cart-summary">
                    <div class="shop-cart-total">
                        <span>Total</span>
                        <strong>R<?= number_format($total_amount, 2) ?></strong>
                    </div>
                    <form action="/pages/pay/initiate-shop.php" method="GET" class="shop-cart-pay-form">
                        <input type="hidden" name="cart_id" value="<?= htmlspecialchars($cart_id) ?>">
                        <input type="hidden" name="shop_id" value="<?= htmlspecialchars($shop_id) ?>">
                        <input type="hidden" name="seller_uid" value="<?= htmlspecialchars($seller_uid) ?>">
                        <input type="hidden" name="amount" value="<?= htmlspecialchars($total_amount) ?>">
                        <button type="submit" class="shop-dashboard-button shop-dashboard-button--primary">Pay Now</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="shops-empty">
                    <p>Your cart is empty.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script src="/assets/js/cart.js"></script>
</body>
</html>
