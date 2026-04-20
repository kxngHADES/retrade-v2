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

    public function send_listings(array $data) {
        $apiUrl = $_ENV['BACKEND_INTERNAL_URL'] . '/listings/create_user_listing';
        
        if (is_string($data['tags'])) {
            $data['tags'] = json_decode($data['tags'], true) ?? [];
        }

        if (is_string($data['list_of_image_url'])) {
            $data['list_of_image_url'] = json_decode($data['list_of_image_url'], true) ?? [];
        }

        $payload = [
            "uid" => (string)$data['uid'],
            "name" => (string)$data['name'],
            "description" => (string)$data['description'],
            "thumbnail_url" => (string)$data['thumbnail_url'],

            "list_of_image_url" => array_values($data['list_of_image_url'] ?? []),

            "price" => (float)($data['price'] ?? 0),
            "stock" => (int)($data['stock'] ?? 0),

            "condition" => (string)($data['condition'] ?? ""),
            "category" => (string)($data['category'] ?? ""),
            "location" => (string)($data['location'] ?? ""),
            "delivery_method" => (string)($data['delivery_method'] ?? ""),

            "tags" => array_values($data['tags'] ?? [])
        ];

        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log($curlError);
            return false;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        error_log("API ERROR $httpCode: $response");
        return false;
    }
}