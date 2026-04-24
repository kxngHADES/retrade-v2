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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Carts</title>
</head>
<body>
    <h2>My Store Carts</h2>

    <div>
        <a href="/pages/shop/">Back to All Shops</a>
    </div>
    <br>

    <div>
        <?php if (!empty($carts)): ?>
            <ul>
                <?php foreach ($carts as $cart): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($cart['shop_name']); ?></strong><br>
                        <a href="/pages/shop/carts/view/?id=<?php echo urlencode($cart['cart_id']); ?>">View Cart</a>
                    </li>
                    <br>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You don't have any store carts yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
