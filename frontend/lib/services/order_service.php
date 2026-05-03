<?php

namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\db\Database;
use Lib\services\profile_services;
use MongoDB\Builder\Search\FacetOperator;
use PDO;
use PDOException;
use Stringable;

class order_service {
    private PDO $db;

    public function __construct() {
		$this->db = Database::getConnection();
    }

    public function createShopOrder(string $buyer_uid, string $seller_uid, string $shop_id, string $cart_id, float $total_amount): array {
        try {
            $sql = "INSERT INTO orders (buyer_uid, seller_uid, shop_id, cart_id, order_type, total_amount) 
                    VALUES (UUID_TO_BIN(:buyer_uid), UUID_TO_BIN(:seller_uid), UUID_TO_BIN(:shop_id), UUID_TO_BIN(:cart_id), 'shop', :total_amount)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'buyer_uid' => $buyer_uid,
                'seller_uid' => $seller_uid,
                'shop_id' => $shop_id,
                'cart_id' => $cart_id,
                'total_amount' => $total_amount
            ]);

            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Failed to create shop order: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database Issue'];
        }
    }

    public function createMarketplaceOrder(string $buyer_uid, string $seller_uid, string $listing_id, float $total_amount): array {
        try {
            $sql = "INSERT INTO orders (buyer_uid, seller_uid, listing_id, order_type, total_amount) 
                    VALUES (UUID_TO_BIN(:buyer_uid), UUID_TO_BIN(:seller_uid), :listing_id, 'marketplace', :total_amount)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'buyer_uid' => $buyer_uid,
                'seller_uid' => $seller_uid,
                'listing_id' => $listing_id,
                'total_amount' => $total_amount
            ]);

            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Failed to create marketplace order: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database Issue'];
        }
    }
}