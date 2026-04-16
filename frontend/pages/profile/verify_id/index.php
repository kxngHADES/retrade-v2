<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Lib\services\Authentication_service;
use Lib\services\ApiService;

$apiService = new ApiService();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['id_image'])) {
    $uid = $_SESSION['uid'] ?? null;

    if (!$uid) {
        $error = "User not logged in";
        header('Location: /pages/login');
    }

    $fileData = $_FILES['id_image'];

    if ($fileData['error'] !== UPLOAD_ERR_OK) {
        $error = "File upload error code: " . $fileData['error'];
    }

    $success = $apiService->validate_id_image(
        uid: $uid,
        imageTmpPath: $fileData['tmp_name'],
        mimeType: $fileData['type'],
        originalName: $fileData['name']
    );

    if ($success) {
        // Call update Id to pending
        $authService = new Authentication_service();
        $authService->set_id_to_pending($uid);
        header('Location: /pages/profile');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang=<?= $_SESSION['lang'] ?? 'en' ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload ID</title>
</head>
<body>
    <h2>Upload ID</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="id_image" accept="image/*" capture="environment" required>
        <br/><br/>
        <button type="submit">Upload & Validate</button>
    </form>

    <p id="statusMessage"></p>

    <script>
        
    </script>
</body>
</html>