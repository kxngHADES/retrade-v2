<?php
session_start();
require_once __DIR__ . '/config/bootstrap.php';
use Lib\services\ApiService;

$isLoggedIn = isset($_SESSION['uid']);
$uid = $isLoggedIn ? $_SESSION['uid'] : null;

$query = $_GET['query'] ?? '';
$category = $_GET['category'] ?? '';
$condition = $_GET['condition'] ?? '';
$location = $_GET['location'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$apiService = new ApiService();
$listings = [];

if (!empty($query)) {
    $searchParams = [
        'query' => $query,
        'category' => $category,
        'condition' => $condition,
        'location' => $location,
        'min_price' => $min_price,
        'max_price' => $max_price
    ];
    $listings = $apiService->search_listings($searchParams);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Retrade</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#128C7E">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        header { display: flex; justify-content: space-between; align-items: center; background: #333; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        header a { color: white; margin-left: 10px; text-decoration: none; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .card { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; text-decoration: none; color: black; display: block; }
        .card img { width: 100%; height: 150px; object-fit: cover; border-radius: 5px; }
        .card h3 { margin: 10px 0 5px 0; font-size: 1.1em; }
        .card p.price { font-weight: bold; color: #2E7D32; margin: 0; }
        
        .search-bar { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .search-bar input, .search-bar select, .search-bar button { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .search-bar input[type="text"] { flex-grow: 1; min-width: 200px; }
        .search-bar button { background: #128C7E; color: white; cursor: pointer; border: none; }
        .no-results { text-align: center; padding: 50px; color: #666; font-size: 1.2em; }
    </style>
</head>
<body>
    <header>
        <h2><a href="/">Retrade</a></h2>
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

    <form class="search-bar" action="/search.php" method="GET">
        <input type="text" name="query" placeholder="Search for items..." value="<?= htmlspecialchars($query) ?>" required>
        
        <select name="category">
            <option value="">All Categories</option>
            <option value="Electronics" <?= $category === 'Electronics' ? 'selected' : '' ?>>Electronics</option>
            <option value="Vehicles" <?= $category === 'Vehicles' ? 'selected' : '' ?>>Vehicles</option>
            <option value="Home" <?= $category === 'Home' ? 'selected' : '' ?>>Home</option>
            <option value="Fashion" <?= $category === 'Fashion' ? 'selected' : '' ?>>Fashion</option>
        </select>
        
        <select name="condition">
            <option value="">Any Condition</option>
            <option value="New" <?= $condition === 'New' ? 'selected' : '' ?>>New</option>
            <option value="Used - Good" <?= $condition === 'Used - Good' ? 'selected' : '' ?>>Used - Good</option>
            <option value="Used - Fair" <?= $condition === 'Used - Fair' ? 'selected' : '' ?>>Used - Fair</option>
        </select>

        <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($location) ?>">
        <input type="number" name="min_price" placeholder="Min Price" min="0" value="<?= htmlspecialchars($min_price) ?>">
        <input type="number" name="max_price" placeholder="Max Price" min="0" value="<?= htmlspecialchars($max_price) ?>">
        
        <button type="submit">Search</button>
    </form>

    <?php if (empty($listings)): ?>
        <div class="no-results">
            No listings found for "<?= htmlspecialchars($query) ?>" with the selected filters.
        </div>
    <?php else: ?>
        <div class="grid" id="listing-container">
            <?php foreach ($listings as $index => $item): ?>
                <a href="/view/?listing_id=<?= urlencode($item['id'] ?? $item['_id'] ?? '') ?>" class="card">
                    <img src="<?= htmlspecialchars($item['thumbnail_url'] ?? 'https://via.placeholder.com/200') ?>" alt="Image">
                    <h3><?= htmlspecialchars($item['name'] ?? 'Unknown Item') ?></h3>
                    <p class="price">R<?= htmlspecialchars($item['price'] ?? '0') ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    console.log('ServiceWorker registered on search:', reg.scope);
                }).catch(err => {
                    console.log('ServiceWorker registration failed:', err);
                });
            });
        }
    </script>
</body>
</html>