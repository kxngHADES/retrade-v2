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
    <title>View Listing</title>
</head>
<body>
    <a href="/pages/my-listings/">Back to My Listings</a>
    <br>
    
    <div>
        <h2><?php echo htmlspecialchars($listing['name'] ?? ''); ?></h2>

        <?php if (!empty($listing['thumbnail_url'])): ?>
            <div>
                <img src="<?php echo htmlspecialchars($listing['thumbnail_url']); ?>" alt="<?php echo htmlspecialchars($listing['name'] ?? ''); ?>" style="max-width: 300px; display: block; margin-bottom: 10px;">
            </div>
        <?php endif; ?>

        <?php if (!empty($listing['list_of_image_url'])): ?>
            <div>
                <strong>Additional Images:</strong><br>
                <?php foreach ($listing['list_of_image_url'] as $img): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Extra Image" style="max-width: 150px; margin-right: 5px; margin-bottom: 5px;">
                <?php endforeach; ?>
            </div>
            <br>
        <?php endif; ?>

        <p><strong>Description:</strong> <br><?php echo nl2br(htmlspecialchars($listing['description'] ?? '')); ?></p>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars(number_format($listing['price'] ?? 0, 2)); ?></p>
        <p><strong>Stock Quantity:</strong> <?php echo htmlspecialchars($listing['stock'] ?? '0'); ?></p>
        <p><strong>Condition:</strong> <?php echo htmlspecialchars($listing['condition'] ?? 'N/A'); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($listing['category'] ?? 'N/A'); ?></p>
        <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['location'] ?? 'N/A'); ?></p>
        <p><strong>Delivery Method:</strong> <?php echo htmlspecialchars($listing['delivery_method'] ?? 'N/A'); ?></p>
        
        <?php if (!empty($listing['tags'])): ?>
            <p><strong>Tags:</strong> 
                <?php foreach ($listing['tags'] as $tag): ?>
                    <span><?php echo htmlspecialchars($tag); ?></span>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>

    </div>
    
    <br>
    <a href="/pages/my-listings/edit-listing/?id=<?php echo urlencode($listing_id); ?>">Edit Listing</a>
</body>
</html>