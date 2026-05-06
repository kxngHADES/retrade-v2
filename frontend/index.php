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
    <!-- Apply saved theme immediately before paint to avoid flash -->
    <script>
        (function() {
            var t = localStorage.getItem('theme');
            if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
            else document.documentElement.removeAttribute('data-theme');
        })();
    </script>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#128C7E">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="/assets/css/global.css">
    <style>
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .card { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; text-decoration: none; color: black; display: block; }
        .card img { width: 100%; height: 150px; object-fit: cover; border-radius: 5px; }
        .card h3 { margin: 10px 0 5px 0; font-size: 1.1em; }
        .card p.price { font-weight: bold; color: #2E7D32; margin: 0; }
        .hidden { display: none !important; }
        .btn-more { display: block; margin: 30px auto; padding: 10px 20px; background: #128C7E; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .search-bar { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .search-bar input, .search-bar select, .search-bar button { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .search-bar input[type="text"] { flex-grow: 1; min-width: 200px; }
        .search-bar button { background: #128C7E; color: white; cursor: pointer; border: none; }
    </style>
</head>
<body class="bg-surface-container-lowest text-light-text-primary antialiased min-h-screen flex">
    <!-- Include the Navbar -->
    <?php include __DIR__ . '/templates/partial/navbar.php'; ?>
    
    <div id="main-content" class="main-content min-h-screen relative overflow-hidden bg-surface-container-lowest transition-all duration-300">
        <main class="w-full h-full overflow-y-auto pt-[40px] pb-[72px] md:pt-0 md:pb-0 p-4">

    <form class="search-bar" action="/search.php" method="GET">
        <input type="text" name="query" placeholder="Search for items..." required>
        
        <select name="category">
            <option value="">All Categories</option>
            <option value="Electronics">Electronics</option>
            <option value="Vehicles">Vehicles</option>
            <option value="Home">Home</option>
            <option value="Fashion">Fashion</option>
        </select>
        
        <select name="condition">
            <option value="">Any Condition</option>
            <option value="New">New</option>
            <option value="Used - Good">Used - Good</option>
            <option value="Used - Fair">Used - Fair</option>
        </select>

        <input type="text" name="location" placeholder="Location">
        <input type="number" name="min_price" placeholder="Min Price" min="0">
        <input type="number" name="max_price" placeholder="Max Price" min="0">
        
        <button type="submit">Search</button>
    </form>

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
    </main>
    </div>
    
    <script src="/assets/js/index_listings.js"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    console.log('ServiceWorker registered:', reg.scope);
                }).catch(err => {
                    console.log('ServiceWorker registration failed:', err);
                });
            });
        }
    </script>
</body>
</html>