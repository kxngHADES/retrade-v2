<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Lib\services\Authentication_service;
use Lib\services\ApiService;

$apiService = new ApiService();
$error = "";
$phoneNumber = $_SESSION['phoneNumber'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = $_SESSION['uid'] ?? null;

    if (!$uid) {
        header('Location: /pages/login');
        exit();
    }

    if (!isset($_FILES['id_image'])) {
        $error = "Please select an ID image file.";
    } else {
        $fileData = $_FILES['id_image'];

        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            $error = "File upload error code: " . $fileData['error'];
        } else {
            $success = $apiService->validate_id_image(
                uid: $uid,
                imageTmpPath: $fileData['tmp_name'],
                mimeType: $fileData['type'],
                originalName: $fileData['name']
            );

            if ($success) {
                $authService = new Authentication_service();
                $authService->set_id_to_pending($uid);
                header('Location: /pages/profile');
                exit();
            } else {
                $error = "Failed to upload ID image. Please try again.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title><?= htmlspecialchars(trans('Verify ID')) ?> - ReTrade</title>
    <link rel="stylesheet" href="../../../assets/css/variables.css">
    <link rel="stylesheet" href="../../../assets/css/global.css">
    <link rel="stylesheet" href="../../../assets/css/forms.css">
</head>
<body class="auth-layout">
    <main class="auth-card verify-id-card">
        <div class="verify-id-content">
            <div class="verify-id-header">
                <a href="/pages/profile" class="auth-back-btn" aria-label="Go back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
                <h1 class="auth-title verify-id-title"><?= htmlspecialchars(trans('Verify ID')) ?></h1>
            </div>

            <div>
                <p class="text-h1 font-display verify-id-heading"><?= htmlspecialchars(trans('Upload your ID')) ?></p>
                <p class="text-body auth-subtitle"><?= htmlspecialchars(trans('Take a clear photo of your South African ID card or document. Ensure all text is readable.')) ?></p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <span class="form-error"><?= htmlspecialchars($error) ?></span>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="auth-form" novalidate>
            <div class="form-group id-upload-group">
                <input class="form-input id-image-input" type="file" id="id_image" name="id_image" accept="image/*" capture="environment" required>
                <label for="id_image" id="idUploadArea" class="id-upload-area" tabindex="0">
                    <div class="id-upload-placeholder" id="idUploadPlaceholder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                        <span class="text-body auth-subtitle"><?= htmlspecialchars(trans('Tap to take a photo or upload your ID')) ?></span>
                    </div>
                    <img class="id-preview-image hidden" id="idPreviewImage" src="" alt="<?= htmlspecialchars(trans('ID preview')) ?>">
                </label>
            </div>

            <button type="submit" class="auth-btn"><?= htmlspecialchars(trans('Submit for Verification')) ?></button>
        </form>
    </main>
    <script>
        const idInput = document.getElementById('id_image');
        const idPreviewImage = document.getElementById('idPreviewImage');
        const idUploadPlaceholder = document.getElementById('idUploadPlaceholder');

        idInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                idPreviewImage.classList.add('hidden');
                idUploadPlaceholder.classList.remove('hidden');
                idPreviewImage.src = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                idPreviewImage.src = event.target.result;
                idPreviewImage.classList.remove('hidden');
                idUploadPlaceholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>