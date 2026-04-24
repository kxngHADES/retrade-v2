<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../utils/protected_route.php';

use Lib\services\shop_service;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$action = $input['action'] ?? '';
$shop_service = new shop_service();
$uid = $_SESSION['uid'];

try {
    switch ($action) {
        case 'add':
            $shop_id = $input['shop_id'] ?? '';
            $product_id = $input['product_id'] ?? '';
            $amount = (int)($input['amount'] ?? 1);
            $price = (float)($input['price'] ?? 0.0);

            if (!$shop_id || !$product_id || $amount <= 0) {
                echo json_encode(['success' => false, 'error' => 'Missing or invalid data']);
                exit;
            }

            $shop_service->addToCart($uid, $shop_id, $product_id, $amount, $price);
            echo json_encode(['success' => true]);
            break;

        case 'update':
            $item_id = $input['item_id'] ?? '';
            $quantity = (int)($input['quantity'] ?? 0);

            if (!$item_id) {
                echo json_encode(['success' => false, 'error' => 'Missing item ID']);
                exit;
            }

            $shop_service->changeCartItemQuantity($item_id, $quantity);
            echo json_encode(['success' => true]);
            break;

        case 'remove':
            $item_id = $input['item_id'] ?? '';

            if (!$item_id) {
                echo json_encode(['success' => false, 'error' => 'Missing item ID']);
                exit;
            }

            $shop_service->removeFromCart($item_id);
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
