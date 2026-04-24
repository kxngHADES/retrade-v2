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
$products = $shop_service->getShopProducts($shop_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Products</title>
</head>
<body>
    <h2>Shop Products</h2>
    <div>
        <a href="/pages/shop/">Back to Shops</a>
        <br><br>
        <a href="/pages/shop/carts/">View My Carts</a>
    </div>
    <br>

    <div>
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <p>Price: R<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>
                    <p>Stock: <?php echo htmlspecialchars($product['stock_quantity']); ?></p>

                    <?php if ($product['is_active'] && $product['stock_quantity'] > 0): ?>
                        <div>
                            <input type="number" id="qty-<?php echo htmlspecialchars($product['product_id']); ?>" value="1" min="1" max="<?php echo htmlspecialchars($product['stock_quantity']); ?>">
                            <button onclick="CartAPI.add('<?php echo htmlspecialchars($shop_id); ?>', '<?php echo htmlspecialchars($product['product_id']); ?>', <?php echo htmlspecialchars($product['price']); ?>, document.getElementById('qty-<?php echo htmlspecialchars($product['product_id']); ?>').value)">
                                Add to Cart
                            </button>
                        </div>
                    <?php else: ?>
                        <p><i>Out of stock or unavailable</i></p>
                    <?php endif; ?>
                </div>
                <br>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products available in this shop right now.</p>
        <?php endif; ?>
    </div>

    <script src="/assets/js/cart.js"></script>
</body>
</html>
