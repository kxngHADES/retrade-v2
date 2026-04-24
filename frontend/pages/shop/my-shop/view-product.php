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
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>View Product</title>
</head>
<body>
	<a href="/pages/shop/my-shop/">Back to My Shop</a>
	<br>
	<div>
		<h2><?php echo htmlspecialchars($product['name'] ?? ''); ?></h2>
		<p><strong>Description:</strong> <br><?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?></p>
		<p><strong>Price:</strong> R<?php echo htmlspecialchars($product['price'] ?? '0.00'); ?></p>
		<p><strong>Stock Quantity:</strong> <?php echo htmlspecialchars($product['stock_quantity'] ?? '0'); ?></p>
		<p><strong>Status:</strong> <?php echo !empty($product['is_active']) ? 'Active' : 'Inactive'; ?></p>

        <a href="/pages/shop/my-shop/edit-product/?id=<?php echo urlencode($product_id); ?>">Edit Product</a>
	</div>
</body>
</html>
