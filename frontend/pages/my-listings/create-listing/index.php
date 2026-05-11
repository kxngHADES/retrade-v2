<?php


require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';

use Lib\services\listing_service;
use Lib\services\profile_services;

$uid = $_SESSION['uid'];
$listing_service = new listing_service();
$profile_service = new profile_services();
$isVerified = $profile_service->is_id_verified($uid);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listing_service->createListing($uid, $_POST);
}


?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(trans('Create Listing')) ?></title>
    <script>
        (function() {
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        })();
    </script>
    <link rel="stylesheet" href="/assets/css/variables.css">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/listing.css">
</head>
<body class="antialiased min-h-screen flex listing-page">
    <div class="listing-shell">
        <header class="listing-topbar">
            <a href="/pages/my-listings" class="listing-back-btn" aria-label="Back to my listings">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <div class="listing-header-copy">
                <p class="listing-context"><?= htmlspecialchars(trans('My Listings')) ?></p>
                <h1 class="listing-title"><?= htmlspecialchars(trans('Create Listing')) ?></h1>
            </div>
            <div class="listing-spacer"></div>
        </header>

        <?php if (!$isVerified): ?>
            <div class="listing-alert">
                <svg class="listing-alert-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <circle cx="12" cy="16" r="1"></circle>
                </svg>
                <div>
                    <p class="listing-alert-title"><?= htmlspecialchars(trans('Verify your ID to create more listings')) ?></p>
                    <p class="listing-alert-copy"><?= htmlspecialchars(trans('Your account is not fully verified yet. Listing creation is disabled until ID verification is complete.')) ?></p>
                </div>
            </div>
        <?php endif; ?>

	<form class="listing-form" action="" method="post" enctype="multipart/form-data">
            <section class="listing-section">
                <div class="listing-section-header">
                    <div>
                        <p class="listing-section-label"><?= htmlspecialchars(trans('Listing photos')) ?></p>
                        <p class="listing-section-description"><?= htmlspecialchars(trans('Add a thumbnail and product images to make your listing stand out.')) ?></p>
                    </div>
                </div>

                <div class="listing-photo-grid">
                    <label for="thumbnail" class="listing-upload-card listing-upload-card--large listing-upload-card--preview">
                        <input type="file" id="thumbnail" accept="image/*" class="listing-file-input" required>
                        <img class="listing-preview-img listing-preview-img--thumbnail hidden" alt="<?= htmlspecialchars(trans('Thumbnail preview')) ?>">
                        <div class="listing-upload-card-content">
                            <svg class="listing-upload-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14"></path>
                                <path d="M5 12h14"></path>
                            </svg>
                            <span class="listing-upload-text"><?= htmlspecialchars(trans('Upload thumbnail *')) ?></span>
                            <span class="listing-upload-hint"><?= htmlspecialchars(trans('Recommended size 1200x900')) ?></span>
                        </div>
                    </label>
                    <label for="images" class="listing-upload-card listing-upload-card--small listing-upload-card--secondary listing-upload-card--preview">
                        <input type="file" id="images" accept="image/*" multiple class="listing-file-input">
                        <img class="listing-preview-img listing-preview-img--small hidden" alt="<?= htmlspecialchars(trans('Image preview 1')) ?>">
                        <div class="listing-upload-card-content">
                            <span class="listing-add-text"><?= htmlspecialchars(trans('Add image')) ?></span>
                        </div>
                    </label>
                    <label for="images" class="listing-upload-card listing-upload-card--small listing-upload-card--secondary listing-upload-card--preview">
                        <img class="listing-preview-img listing-preview-img--small hidden" alt="<?= htmlspecialchars(trans('Image preview 2')) ?>">
                        <div class="listing-upload-card-content">
                            <span class="listing-add-text"><?= htmlspecialchars(trans('Add image')) ?></span>
                        </div>
                    </label>
                    <label for="images" class="listing-upload-card listing-upload-card--small listing-upload-card--secondary listing-upload-card--preview">
                        <img class="listing-preview-img listing-preview-img--small hidden" alt="<?= htmlspecialchars(trans('Image preview 3')) ?>">
                        <div class="listing-upload-card-content">
                            <span class="listing-add-text"><?= htmlspecialchars(trans('Add image')) ?></span>
                        </div>
                    </label>
                </div>
            </section>

            <section class="listing-section">
                <div class="listing-section-header">
                    <div>
                        <p class="listing-section-label"><?= htmlspecialchars(trans('Basic details')) ?></p>
                    </div>
                </div>
                <div class="listing-field">
                    <label class="listing-label" for="name"><?= htmlspecialchars(trans('Listing title')) ?></label>
                    <input id="name" name="name" type="text" class="listing-input" placeholder="<?= htmlspecialchars(trans('e.g. Vintage leather jacket')) ?>" required>
                </div>
                <div class="listing-field">
                    <label class="listing-label" for="description"><?= htmlspecialchars(trans('Description')) ?></label>
                    <textarea id="description" name="description" class="listing-textarea" placeholder="<?= htmlspecialchars(trans('Describe the item, condition, and any details buyers should know.')) ?>" rows="4" required></textarea>
                </div>
            </section>

            <section class="listing-section listing-grid-two">
                <div class="listing-field">
                    <label class="listing-label" for="price"><?= htmlspecialchars(trans('Price')) ?></label>
                    <div class="listing-input-icon-wrapper">
                        <span class="listing-input-icon">R</span>
                        <input id="price" name="price" type="number" step="0.01" class="listing-input listing-input--icon" placeholder="0.00" required>
                    </div>
                </div>
                <div class="listing-field">
                    <label class="listing-label" for="stock"><?= htmlspecialchars(trans('Stock')) ?></label>
                    <input id="stock" name="stock" type="number" class="listing-input" placeholder="1" required>
                </div>
            </section>

            <section class="listing-section">
                <div class="listing-section-header">
                    <div>
                        <p class="listing-section-label"><?= htmlspecialchars(trans('Classification')) ?></p>
                    </div>
                </div>
                <div class="listing-field">
                    <label class="listing-label" for="category"><?= htmlspecialchars(trans('Category')) ?></label>
                    <div class="listing-select-wrapper">
                        <select id="category" name="category" class="listing-select" required>
                            <option value="" selected disabled><?= htmlspecialchars(trans('Select category')) ?></option>
                            <option value="electronics"><?= htmlspecialchars(trans('Electronics')) ?></option>
                            <option value="fashion"><?= htmlspecialchars(trans('Fashion')) ?></option>
                            <option value="home"><?= htmlspecialchars(trans('Home & Garden')) ?></option>
                            <option value="sports"><?= htmlspecialchars(trans('Sports')) ?></option>
                            <option value="other"><?= htmlspecialchars(trans('Other')) ?></option>
                        </select>
                        <svg class="listing-select-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </section>

            <section class="listing-section">
                <div class="listing-section-header">
                    <div>
                        <p class="listing-section-label"><?= htmlspecialchars(trans('Logistics')) ?></p>
                    </div>
                </div>
                <div class="listing-field">
                    <label class="listing-label" for="location"><?= htmlspecialchars(trans('Location')) ?></label>
                    <div class="listing-input-icon-wrapper">
                        <span class="listing-input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </span>
                        <input id="location" name="location" type="text" class="listing-input listing-input--icon" placeholder="<?= htmlspecialchars(trans('e.g. Cape Town, WC')) ?>">
                    </div>
                </div>
                <div class="listing-field">
                    <label class="listing-label" for="delivery_method"><?= htmlspecialchars(trans('Delivery method')) ?></label>
                    <input id="delivery_method" name="delivery_method" type="text" class="listing-input" placeholder="<?= htmlspecialchars(trans('e.g. Collection only')) ?>">
                </div>
                <div class="listing-field">
                    <label class="listing-label" for="tags"><?= htmlspecialchars(trans('Tags')) ?></label>
                    <input id="tags" name="tags" type="text" class="listing-input" placeholder="<?= htmlspecialchars(trans('Separate with commas')) ?>">
                </div>
            </section>

            <input type="hidden" name="thumbnail_url" id="thumbnail_url">
            <input type="hidden" name="list_of_image_url" id="list_of_image_url">

            <div class="listing-submit-wrap">
                <button type="submit" class="listing-submit-btn" <?= $isVerified ? '' : 'disabled' ?>><?= htmlspecialchars(trans('Create Listing')) ?></button>
            </div>
        </form>
    </div>

    <script>
        window.UID = "<?= $_SESSION['uid'] ?>";
    </script>

	<script src="/assets/js/upload.js" defer></script>
</body>
</html>