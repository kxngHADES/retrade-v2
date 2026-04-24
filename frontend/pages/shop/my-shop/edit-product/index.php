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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => floatval($_POST['price'] ?? 0),
        'stock_quantity' => intval($_POST['stock_quantity'] ?? 0),
        'product_id' => $product_id
    ];
    $shop_service->updateProduct($data);
}

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
	<title>Edit Product</title>
</head>
<body>
	<a href="/pages/shop/my-shop/view-product.php?id=<?php echo urlencode($product_id); ?>">Back to Product</a>
	<br>
	<div>
		<h2>Edit Product</h2>
        <form method="POST" action="/pages/shop/my-shop/edit-product/?id=<?php echo urlencode($product_id); ?>">
            <div>
                <label>Name:</label><br>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
            </div>
            <br>
            <div>
                <label>Description:</label><br>
                <textarea name="description" required><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>
            <br>
            <div>
                <label>Price (R):</label><br>
                <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price'] ?? '0.00'); ?>" required>
            </div>
            <br>
            <div>
                <label>Stock Quantity:</label><br>
                <input type="number" name="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity'] ?? '0'); ?>" required>
            </div>
            <br>
            <button type="submit">Update Product</button>
        </form>
	</div>
</body>
</html>
