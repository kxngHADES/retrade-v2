<?php

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';
require_once __DIR__ . '/../../../utils/id_verified_screens.php';

use Lib\services\shop_service;
use Lib\services\profile_services;

$uid = $_SESSION['uid'];
$profile_service = new profile_services();
$isVerified = $profile_service->is_id_verified($uid);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $shop_name = trim($_POST['shop_name'] ?? '');
    $shop_service = new shop_service();
    $shop_service->createShop($uid, $shop_name);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Create Your Shop - ReTrade</title>
    <script>
        (function() {
            var theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        })();
    </script>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/shops.css">
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

<body class="shop-create-page">
    <?php require_once __DIR__ . '/../../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="shop-create-shell">
            <header class="shop-create-topbar">
                <a href="/pages/shop/" class="shop-create-back-btn" aria-label="Back to shops">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
                <div class="shop-create-header-copy">
                    <p class="shop-create-context">ReTrade</p>
                    <h1 class="shop-create-title">Create Shop</h1>
                </div>
            </header>

            <div class="shop-create-copy">
                <p>Having a shop allows you to list multiple items and build trust in the ReTrade community.</p>
            </div>

            <div class="shop-create-banner shop-create-banner--notice">
                <div class="shop-create-banner-icon" aria-hidden="true">!</div>
                <div>
                    <p class="shop-create-banner-title">Consumer-to-Consumer Platform</p>
                    <p class="shop-create-banner-text">Shops are for local sellers only; registered businesses and commercial trade are not supported.</p>
                </div>
            </div>

            <?php if (!$isVerified): ?>
                <div class="shop-create-banner shop-create-banner--warning">
                    <div class="shop-create-banner-icon" aria-hidden="true">i</div>
                    <div>
                        <p class="shop-create-banner-title">ID Verification Required</p>
                        <p class="shop-create-banner-text">Complete ID verification before your shop becomes visible to buyers.</p>
                    </div>
                </div>
            <?php endif; ?>

            <form class="shop-create-form" action="" method="post">
                <div class="shop-create-field">
                    <label class="shop-create-label" for="shop_name">Shop Name</label>
                    <input id="shop_name" name="shop_name" type="text" class="shop-create-input" placeholder="Sfiso's spaza" required value="<?= htmlspecialchars($_POST['shop_name'] ?? '') ?>">
                    <p class="shop-create-helper">This is how buyers will recognize your shop and listings.</p>
                </div>

                <div class="shop-create-submit-wrap">
                    <button type="submit" class="shop-create-button" <?= $isVerified ? '' : 'disabled' ?>>Register Shop</button>
                    <?php if (!$isVerified): ?>
                        <p class="shop-create-note">Your account must complete ID verification before shop registration is allowed.</p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </main>
    <script src="/assets/js/global.js" defer></script>
</body>
</html>
