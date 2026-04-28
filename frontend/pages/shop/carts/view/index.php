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
    // calculate total 
    $total_amount += $item['price_snapshot'] * $item['quantity'];
    if (empty($shop_id)) {
        // we can fetch shop_id from the first item since all items in a cart belong to the same shop
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Details</title>
</head>
<body>
    <h2>Cart Details</h2>
    <div>
        <a href="/pages/shop/carts/">Back to My Carts</a>
    </div>
    <br>

    <div>
        <?php if (!empty($cart_items)): ?>
            <table border="1" cellpadding="5">
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
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>R<?php echo number_format($item['price_snapshot'], 2); ?></td>
                            <td>
                                <input type="number" id="qty-<?php echo htmlspecialchars($item['item_id']); ?>" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" style="width: 50px;">
                                <button onclick="CartAPI.update('<?php echo htmlspecialchars($item['item_id']); ?>', document.getElementById('qty-<?php echo htmlspecialchars($item['item_id']); ?>').value)">Update</button>
                            </td>
                            <td>R<?php echo number_format($item['price_snapshot'] * $item['quantity'], 2); ?></td>
                            <td>
                                <button onclick="CartAPI.remove('<?php echo htmlspecialchars($item['item_id']); ?>')">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <br>
            <h3>Total: R<?php echo number_format($total_amount, 2); ?></h3>

            <div style="margin-top: 20px;">
                <form action="/pages/pay/initiate-shop.php" method="GET">
                    <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($cart_id); ?>">
                    <input type="hidden" name="shop_id" value="<?php echo htmlspecialchars($shop_id); ?>">
                    <input type="hidden" name="seller_uid" value="<?php echo htmlspecialchars($seller_uid); ?>">
                    <input type="hidden" name="amount" value="<?php echo htmlspecialchars($total_amount); ?>">
                    <button type="submit" style="padding: 10px 20px; font-size: 16px; background-color: #128C7E; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Pay Now
                    </button>
                </form>
            </div>

        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <!-- Inject JS for cart operations -->
    <script src="/assets/js/cart.js"></script>
</body>
</html>