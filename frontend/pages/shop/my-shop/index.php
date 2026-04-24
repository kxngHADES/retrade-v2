<?php

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';
require_once __DIR__ . '/../../../utils/id_verified_screens.php';

use Lib\services\shop_service;

$shop_service = new shop_service();

$shop = $shop_service->getShop($_SESSION['uid']);

$shop_name = $shop['shop_name'];

$shop_id = $shop['shop_id'];

$products = $shop_service->getShopProducts($shop_id);


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
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

<body>
	<a href="/pages/shop/my-shop/add-product"> Add a product</a>

	<div>
		<?php if (!empty($products)): ?>
			<?php foreach ($products as $product): ?>
				<div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
					<h3><?php echo htmlspecialchars($product['name']); ?></h3>
					<p><?php echo htmlspecialchars($product['description']); ?></p>
					<p>Price: R<?php echo htmlspecialchars($product['price']); ?></p>
					<p>Stock: <?php echo htmlspecialchars($product['stock_quantity']); ?></p>
					<p>Status: <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?></p>

					<a href="/pages/shop/my-shop/view-product.php?id=<?php echo urlencode($product['product_id']); ?>">
						View Product
					</a>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<p>No products found.</p>
			
		<?php endif; ?>
	</div>
</body>
</html>