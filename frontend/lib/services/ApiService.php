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

    public function get_user_listings(string $uid): array {
        $apiUrl = $_ENV['BACKEND_INTERNAL_URL'] . '/listings/get_user_listings/' . urlencode($uid);
        
        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("Get User Listings API Error: $curlError");
            return [];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $decode = json_decode($response, true);
            return $decode['listings'] ?? [];
        }

        error_log("Get User Listings API ERROR $httpCode: $response");
        return [];
    }

    public function get_listing(string $id): ?array {
        $apiUrl = $_ENV['BACKEND_INTERNAL_URL'] . '/listings/get_listing/' . urlencode($id);
        
        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $decode = json_decode($response, true);
            return $decode['listing'] ?? null;
        }

        error_log("Get Listing API ERROR $httpCode: $response");
        return null;
    }

    public function update_listing(string $id, array $data): bool {
        $apiUrl = $_ENV['BACKEND_INTERNAL_URL'] . '/listings/update_listing/' . urlencode($id);
        
        $payload = [];
        $allow_keys = ['name', 'description', 'thumbnail_url', 'list_of_image_url', 'price', 'stock', 'condition', 'category', 'location', 'delivery_method', 'tags'];
        
        foreach ($allow_keys as $key) {
            if (isset($data[$key]) && $data[$key] !== '') {
                // handle price and stock specific conversions if needed
                if ($key === 'price') $payload[$key] = (float)$data[$key];
                elseif ($key === 'stock') $payload[$key] = (int)$data[$key];
                else $payload[$key] = $data[$key];
            }
        }

        if (empty($payload)) return true;

        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
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
            error_log("Update Listing API Error: $curlError");
            return false;
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        error_log("Update Listing API ERROR $httpCode: $response");
        return false;
    }

    public function get_recommendations_or_latest(?string $uid, int $page = 1): array {
        $endpoint = $uid ? "/listings/recommendations/" . urlencode($uid) . "?page=" . $page : "/listings/latest";
        $apiUrl = $_ENV['BACKEND_INTERNAL_URL'] . $endpoint;
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            return $data['listings'] ?? [];
        }
        return [];
    }

    public function record_user_view(string $uid, string $listingId): bool {
        $apiUrl = $_ENV['BACKEND_INTERNAL_URL'] . '/listings/record_view';
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['uid' => $uid, 'listing_id' => $listingId]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout for background task
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode >= 200 && $httpCode < 300);
    }
}