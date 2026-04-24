<?php

namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\services\ApiService;

class listing_service{


    public function __construct() {}

    public function createListing(string $uid, array $data) {

        $data['uid'] = $uid;

        if (isset($data['list_of_image_url'])) {
            $data['list_of_image_url'] = json_decode($data['list_of_image_url'], true) ?? [];
        }

        if (isset($data['tags'])) {
            $data['tags'] = json_decode($data['tags'], true) ?? [];
        }

        $apiService = new ApiService();
        $apiService->send_listings($data);

        header('Location: /pages/my-listings');
        exit();
    }

    public function getListing(string $id) {
        $apiService = new ApiService();
        return $apiService->get_listing($id);
    }

    public function updateListing(string $id, array $data) {
        if (isset($data['list_of_image_url']) && is_string($data['list_of_image_url'])) {
            $data['list_of_image_url'] = json_decode($data['list_of_image_url'], true) ?? [];
        }

        if (isset($data['tags']) && is_string($data['tags'])) {
            $data['tags'] = json_decode($data['tags'], true) ?? [];
        }
        
        $apiService = new ApiService();
        $apiService->update_listing($id, $data);

        header('Location: /pages/my-listings');
        exit();
    }
}