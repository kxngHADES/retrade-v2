<?php
session_start();
require_once __DIR__ . '/config/bootstrap.php';
use Lib\services\ApiService;

$isLoggedIn = isset($_SESSION['uid']);
$uid = $isLoggedIn ? $_SESSION['uid'] : null;

$apiService = new ApiService();
$listings = $apiService->get_recommendations_or_latest($uid, 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retrade</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        header { display: flex; justify-content: space-between; align-items: center; background: #333; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        header a { color: white; margin-left: 10px; text-decoration: none; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .card { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; text-decoration: none; color: black; display: block; }
        .card img { width: 100%; height: 150px; object-fit: cover; border-radius: 5px; }
        .card h3 { margin: 10px 0 5px 0; font-size: 1.1em; }
        .card p.price { font-weight: bold; color: #2E7D32; margin: 0; }
        .hidden { display: none !important; }
        .btn-more { display: block; margin: 30px auto; padding: 10px 20px; background: #128C7E; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <h2>Retrade</h2>
        <div>
            <?php if($isLoggedIn): ?>
                <a href="/pages/chat/">My Chats</a>
                <a href="/pages/profile/">Profile</a>
            <?php else: ?>
                <a href="/pages/login/">Login</a>
                <a href="/pages/register/">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="grid" id="listing-container">
        <?php foreach ($listings as $index => $item): ?>
            <a href="/view/?listing_id=<?= urlencode($item['_id']) ?>" class="card <?= $index >= 20 ? 'hidden' : '' ?>" data-index="<?= $index ?>">
                <img src="<?= htmlspecialchars($item['thumbnail_url'] ?? 'https://via.placeholder.com/200') ?>" alt="Image">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p class="price">R<?= htmlspecialchars($item['price']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (count($listings) > 20): ?>
        <button id="see-more-btn" class="btn-more">See More</button>
    <?php endif; ?>

    <script src="/assets/js/index_listings.js"></script>
</body>
</html>