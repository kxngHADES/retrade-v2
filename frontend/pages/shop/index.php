<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';

use Lib\services\shop_service;

$shop_service = new shop_service();

$current_uid = $_SESSION['uid'] ?? '';
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;


$shops = $shop_service->getAllShopsExcludingUser($offset, $current_uid);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Shops</title>
</head>
<body>
    <h2>Available Stores</h2>
    
    <div>
        <a href="/pages/shop/my-shop/">Go to My Shop</a>
    </div>
    <br>

    <div>
        <?php if (!empty($shops)): ?>
            <ul>
                <?php foreach ($shops as $shop): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($shop['shop_name']); ?></strong><br>
                        <a href="/pages/shop/view/?id=<?php echo urlencode($shop['shop_id']); ?>">Visit Store</a>
                    </li>
                    <br>
                <?php endforeach; ?>
            </ul>

            <div>
                <?php if ($offset > 0): ?>
                    <a href="?offset=<?php echo max(0, $offset - 20); ?>">Previous</a>
                <?php endif; ?>
                <?php if (count($shops) == 20): ?>
                    <a href="?offset=<?php echo $offset + 20; ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>No other stores are available at the moment.</p>
        <?php endif; ?>
    </div>
</body>
</html>
