<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';

use Lib\services\listing_service;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: /pages/my-listings/');
    exit;
}

$listing_id = $_GET['id'];
$listingService = new listing_service();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? 0,
        'stock' => $_POST['stock'] ?? 0,
        'condition' => $_POST['condition'] ?? '',
        'category' => $_POST['category'] ?? '',
        'location' => $_POST['location'] ?? '',
        'delivery_method' => $_POST['delivery_method'] ?? '',
        'tags' => $_POST['tags'] ?? '[]'
    ];
    $listingService->updateListing($listing_id, $data);
}

$listing = $listingService->getListing($listing_id);

if (!$listing) {
    echo "Listing not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing</title>
</head>
<body>
    <a href="/pages/my-listings/view-listing/?id=<?php echo urlencode($listing_id); ?>">Back to View Listing</a>
    <br>
    <a href="/pages/my-listings/">Back to My Listings</a>
    
    <h2>Edit Listing</h2>
    <form method="POST" action="/pages/my-listings/edit-listing/?id=<?php echo urlencode($listing_id); ?>">
        <div>
            <label>Name:</label><br>
            <input type="text" name="name" value="<?php echo htmlspecialchars($listing['name'] ?? ''); ?>" required>
        </div>
        <br>
        <div>
            <label>Description:</label><br>
            <textarea name="description" required><?php echo htmlspecialchars($listing['description'] ?? ''); ?></textarea>
        </div>
        <br>
        <div>
            <label>Price:</label><br>
            <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($listing['price'] ?? '0.00'); ?>" required>
        </div>
        <br>
        <div>
            <label>Stock Quantity:</label><br>
            <input type="number" name="stock" value="<?php echo htmlspecialchars($listing['stock'] ?? '0'); ?>" required>
        </div>
        <br>
        <div>
            <label>Condition:</label><br>
            <input type="text" name="condition" value="<?php echo htmlspecialchars($listing['condition'] ?? ''); ?>" required>
        </div>
        <br>
        <div>
            <label>Category:</label><br>
            <input type="text" name="category" value="<?php echo htmlspecialchars($listing['category'] ?? ''); ?>" required>
        </div>
        <br>
        <div>
            <label>Location:</label><br>
            <input type="text" name="location" value="<?php echo htmlspecialchars($listing['location'] ?? ''); ?>" required>
        </div>
        <br>
        <div>
            <label>Delivery Method:</label><br>
            <input type="text" name="delivery_method" value="<?php echo htmlspecialchars($listing['delivery_method'] ?? ''); ?>">
        </div>
        <br>
        <div>
            <label>Tags (JSON format ["tag1", "tag2"]):</label><br>
            <input type="text" name="tags" value='<?php echo htmlspecialchars(json_encode($listing['tags'] ?? [])); ?>'>
        </div>
        
        <button type="submit">Update Listing</button>
    </form>
</body>
</html>