<?php

namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\db\Database;
use Lib\services\profile_services;
use MongoDB\Builder\Search\FacetOperator;
use PDO;
use PDOException;
use Stringable;

class shop_service {
    private PDO $db;
    private $profile_service;

    public function __construct() {
		$this->db = Database::getConnection();
        $this->profile_service = new profile_services();
	}

    






    public function createShop(string $uid, string $shop_name)  {

        if (!$this->profile_service->is_id_verified($uid)){
            return [
                'success'=> false,
                'error'=> 'ID is not verified'
            ];
        }


        if ($this->hasShop($uid)){
            return [
                'success' => false,
                'error' => 'Already have a shop'
            ];
        }


        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO shops (uid, shop_name) VALUES (UUID_TO_BIN(:uid), :shop_name) ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'uid' => $uid,
                'shop_name' => $shop_name
            ]);

            $stmt = $this->db->prepare("SELECT * FROM shops WHERE uid = UUID_TO_BIN(:uid)");
            $stmt->execute(['uid'=> $uid]);

            $shop = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($shop) {
                $this->db->commit();
                return [
                    'success' => true,
                    'shop' => $shop
                ];
            }

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
				$this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'Transaction failed'
                ];
			}

			if ($e->getCode() == 23000){
				error_log("Duplicate shop name: $shop_name");
			} else {
				error_log("Registration failed: " . $e->getMessage());
			}
			
			return [
                'success' => false,
                'error' => 'Database Issue'
            ];
        }
    }










    public function hasShop(string $uid): bool {
        $query = "SELECT * FROM shops WHERE uid = UUID_TO_BIN(:uid)";
		$stmt = $this->db->prepare($query);
		$stmt->execute(["uid" => $uid]);
		return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getShop(string $uid) {
        $query = "SELECT * FROM shops WHERE uid = UUID_TO_BIN(:uid)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['uid' => $uid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getAllShops(int $offset) {
        $offset = (int) $offset;
        $sql = "SELECT * FROM shops LIMIT 20 OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllShopsExcludingUser(int $offset, string $uid) {
        $offset = (int) $offset;
        $sql = "SELECT BIN_TO_UUID(shop_id) as shop_id, BIN_TO_UUID(uid) as uid, shop_name FROM shops WHERE uid != UUID_TO_BIN(:uid) LIMIT 20 OFFSET $offset";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $uid]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




    public function getShopProducts(string $shop_id){
        $sql = "SELECT BIN_TO_UUID(product_id) as product_id, BIN_TO_UUID(shop_id) as shop_id, name, description, stock_quantity, price, is_active FROM shop_products WHERE shop_id = UUID_TO_BIN(:shop_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['shop_id'=>$shop_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProduct(string $product_id) {
        $sql = "SELECT BIN_TO_UUID(product_id) as product_id, BIN_TO_UUID(shop_id) as shop_id, name, description, price, stock_quantity, is_active FROM shop_products WHERE product_id = UUID_TO_BIN(:product_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['product_id' => $product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addShopProduct(string $shop_id, string $name, string $description, float $price, int $quantity) {
        $sql = "INSERT INTO shop_products (shop_id, name, description, stock_quantity, is_active ,price VALUES (UUID_TO_BIN(:shop_id), :name, :description, :stock_quantity, 1, :price))";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'shop_id' => $shop_id,
            'name' => $name,
            'description' => $description,
            'stock_quantity' => $quantity,
            'price' => $price
        ]);
        header('Location: /pages/shop/my-shop');
        exit();
    }





    
    # check if user has a cart for the store
    private function hasStoreCart(string $uid, string $shop_id): bool {
        $sql = "SELECT * FROM carts WHERE uid = UUID_TO_BIN(:uid) AND shop_id = UUID_TO_BIN(:shop_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'uid' => $uid,
            'shop_id' => $shop_id
        ]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function addToCart(string $uid, string $shop_id, string $product_id, int $amount, float $price){
        # check if user has a cart
        if (!$this->hasStoreCart($uid, $shop_id)){
            $this->createCart($uid, $shop_id);
        }

        $cart_id = $this->getStoreCart($uid, $shop_id);
        $sql = "
        INSERT INTO cart_items (cart_id, shop_id, product_id, quantity, price_snapshot) 
        VALUES (UUID_TO_BIN(:cart_id), UUID_TO_BIN(:shop_id), UUID_TO_BIN(:product_id), :quantity, :price_snapshot)
        ON DUPLICATE KEY UPDATE 
            quantity = quantity + VALUES(quantity),
            price_snapshot = VALUES(price_snapshot)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'cart_id'=>$cart_id,
            'shop_id'=>$shop_id,
            'product_id'=>$product_id,
            'quantity'=>$amount,
            'price_snapshot'=>$price
        ]);


    }

    public function removeFromCart(string $item_id){
        # DELETE FROM cart_items WHERE item_id = item_id
        $del_query = "DELETE FROM cart_items WHERE item_id = UUID_TO_BIN(:item_id)";
        $stmt = $this->db->prepare($del_query);
        $stmt->execute([
            'item_id'=>$item_id
        ]);
    }

    private function createCart(string $uid, string $shop_id) {
        # INSERT INTO carts (uid, shop_id) VALUES (:uid, :shop_id)
        $sql = "INSERT INTO carts (uid, shop_id) VALUES (UUID_TO_BIN(:uid),UUID_TO_BIN(:shop_id))";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $uid, 'shop_id' => $shop_id]);
    }

    public function getCarts(string $uid) {
        $sql = "SELECT BIN_TO_UUID(c.cart_id) as cart_id, BIN_TO_UUID(c.shop_id) as shop_id, BIN_TO_UUID(c.uid) as uid, s.shop_name 
                FROM carts c 
                JOIN shops s ON c.shop_id = s.shop_id 
                WHERE c.uid = UUID_TO_BIN(:uid)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'uid'=>$uid
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCartItems(string $cart_id) {
        $sql = "SELECT BIN_TO_UUID(ci.item_id) as item_id, BIN_TO_UUID(ci.product_id) as product_id, ci.quantity, ci.price_snapshot, p.name 
                FROM cart_items ci 
                JOIN shop_products p ON ci.product_id = p.product_id 
                WHERE ci.cart_id = UUID_TO_BIN(:cart_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cart_id'=>$cart_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStoreCart(string $uid, string $shop_id) {
        $sql = "SELECT BIN_TO_UUID(cart_id) as id FROM carts WHERE shop_id = UUID_TO_BIN(:shop_id) AND uid = UUID_TO_BIN(:uid) LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'shop_id' => $shop_id,
            'uid' => $uid
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;  
    }

    public function changeCartItemQuantity(string $item_id, int $quantity) {

        if ($quantity <= 0) {
            $this->removeFromCart($item_id);
            return;
        }

        $sql = "UPDATE cart_items SET quantity = :quantity WHERE item_id = UUID_TO_BIN(:item_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'quantity'=>$quantity,
            'item_id'=>$item_id
        ]);
    }



    public function updateProduct(array $data){
        $sql = "UPDATE shop_products SET name = :name, description = :description, price = :price, stock_quantity = :stock_quantity WHERE product_id = UUID_TO_BIN(:product_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name'=>$data['name'],
            'description'=>$data['description'],
            'price'=>$data['price'],
            'stock_quantity'=>$data['stock_quantity'],
            'product_id'=>$data['product_id']
        ]);

        header('Location: /pages/shop/my-shop');
        exit();
    }

}