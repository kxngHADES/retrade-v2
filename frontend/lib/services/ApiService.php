<?php

namespace Lib\services;

use CURLFile;

require_once __DIR__ . '/../../config/bootstrap.php';

class ApiService {
    public function validate_id_image(string $uid, string $imageTmpPath, string $mimeType, string $originalName) : bool {
    $apiUrl = $_ENV['BACKEND_INTERNAL_URL'] . '/auth/validate_id';

    $cFile = new CURLFile($imageTmpPath, $mimeType, $originalName);

    $payload = [
        'filename' => $uid,
        'file' => $cFile,
        'uid' => $uid
    ];

    $ch = curl_init($apiUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);

    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("Image Upload API Error: $curlError");
        return false;
    }

    if ($httpCode === 202 || $httpCode === 200 || $httpCode === 201) {
        $decode = json_decode($response, true);
        return isset($decode['success']) && $decode['success'] === true;
    }

    error_log("Image upload failed with HTTP $httpCode: $response");
    return false;
}
}