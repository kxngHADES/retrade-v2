<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';

use Lib\services\ApiService;

$apiService = new ApiService();
$listings = $apiService->get_user_listings($_SESSION['uid']);

?>
<!DOCTYPE html>
<html lang=<?= $_SESSION['lang'] ?? 'end' ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <h1>My Listings</h1>

        <?php if (empty($listings)): ?>
            <p class="text-muted">You haven't listed any items yet.</p>
            <a href="/pages/my-listings/create-listing/" class="btn btn-primary">Create Your First Listing</a>
        <?php else: ?>
            <div class="listings-grid">
                <?php foreach ($listings as $listing): ?>
                    <div class="listing-card">
                        <!-- Thumbnail -->
                        <img src="<?= htmlspecialchars($listing['thumbnail_url'] ?? '/assets/placeholder.jpg') ?>" 
                             alt="<?= htmlspecialchars($listing['name']) ?>" 
                             class="listing-thumbnail">
                        
                        <!-- Content -->
                        <div class="listing-info">
                            <h3><?= htmlspecialchars($listing['name']) ?></h3>
                            <p class="price">$<?= number_format($listing['price'], 2) ?></p>
                            <p class="condition">Condition: <?= htmlspecialchars($listing['condition']) ?></p>
                            <p class="location"> <?= htmlspecialchars($listing['location']) ?></p>
                            
                            <!-- Tags -->
                            <?php if (!empty($listing['tags'])): ?>
                                <div class="tags">
                                    <?php foreach ($listing['tags'] as $tag): ?>
                                        <span class="badge"><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Actions -->
                            <div>
                                <a href="/pages/my-listings/view-listing/?id=<?= $listing['_id'] ?>">View</a>
                                <a href="/pages/my-listings/edit-listing/?id=<?= $listing['_id'] ?>">Edit</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>