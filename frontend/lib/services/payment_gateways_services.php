<?php

namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/supabase_service.php';

use Exception;
use PDO;
use Lib\db\Database;

class PaymentGatewaysServices
{
    private PDO $db;
    private supabase_service $supabase;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->supabase = new supabase_service();
    }

    /**
     * Step 1: Initiate Payment Session
     */
    public function createPaymentSession(string $email, float $amount, array $metaData = []): string
    {
        $sql = "INSERT INTO payment_sessions (user_email, amount, status, expiresat) 
                VALUES (:email, :amount, 'pending', DATE_ADD(NOW(), INTERVAL 15 MINUTE))";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':amount' => $amount
        ]);

        return (string)$this->db->lastInsertId();
    }

    /**
     * Fetch session details
     */
    public function getPaymentSession(int $sessionId): ?array
    {
        $sql = "SELECT * FROM payment_sessions WHERE paymentSession_id = :id AND expiresat > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $sessionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Mark session as processing strictly to prevent double submission
     */
    public function lockSessionForProcessing(int $sessionId): bool
    {
        $sql = "UPDATE payment_sessions SET status = 'processing' 
                WHERE paymentSession_id = :id AND status = 'pending' AND expiresat > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $sessionId]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Fake Bank logic utilizing the SQL database & Supabase logic
     */
    public function processFakeBankPayment(string $uid, int $sessionId, float $amount, array $cardDetails): bool
    {
        // 1. Verify the card in the local SQL Bank table
        $sql = "SELECT * FROM bank WHERE uid = :uid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => hex2bin(str_replace('-', '', $uid))]); 
        $bankRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$bankRecord) {
            return false; // No bank account linked
        }

        // Compare secure hashes for card number and CVV
        if (!password_verify($cardDetails['number'], $bankRecord['card_number_hash']) ||
            !password_verify($cardDetails['cvv'], $bankRecord['cvv_hash'])) {
            return false; // Invalid card details
        }

        // Expiry Date check
        if ($cardDetails['exp'] !== $bankRecord['exp_date']) {
            return false; // Card expired or mismatch
        }

        // 2. Call Supabase to check and deduct the balance using Supabase Service directly
        return $this->supabase->chargeFakeBank($uid, $amount);
    }

    /*
      Update session status
     */
    public function updateSessionStatus(int $sessionId, string $status): void
    {
        $sql = "UPDATE payment_sessions SET status = :status, completed_at = IF(:status2 IN ('success','failed'), NOW(), completed_at) WHERE paymentSession_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $status, ':status2' => $status, ':id' => $sessionId]);
    }

    /*
      Trigger the webhook event
     */
    public function fireWebhook(int $sessionId, string $status, array $payloadData): bool
    {
        $stmt = $this->db->prepare("SELECT paymentSession_id FROM payment_sessions WHERE paymentSession_id = :id");
        $stmt->execute([':id' => $sessionId]);
        $psId = $stmt->fetchColumn();

        if (!$psId) return false;

        // Generate Webhook HMAC Signature
        $payloadJson = json_encode($payloadData);
        $secretKey = $_ENV['WEBHOOK_SECRET_KEY'] ?? 'temp_dev_secret_key_12345';
        $signature = hash_hmac('sha256', $payloadJson, $secretKey);
        
        // Log the event locally
        $eventType = ($status === 'success') ? 'payment.success' : 'payment.failed';
        $sql = "INSERT INTO webhook_events (paymentSession_id, event_type, payload, signature) 
                VALUES (:psid, :event, :payload, :signature)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':psid' => $psId,
            ':event' => $eventType,
            ':payload' => $payloadJson,
            ':signature' => $signature
        ]);

        
        $webhookUrl = "http://" . $_SERVER['HTTP_HOST'] . "/pages/pay/webhook.php";
        
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Signature: ' . $signature
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 sec timeout
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $success = ($httpCode >= 200 && $httpCode < 300);
        
        if ($success) {
            $this->db->prepare("UPDATE payment_sessions SET webhook_delivered = 1 WHERE paymentSession_id = ?")->execute([$sessionId]);
        }
        
        return $success;
    }

    /*
     Webhook Escrow Integration
     */
    public function generatePaymentRecord(string $orderIdBytes, string $paymentIdBytes, float $amount, int $status): ?array 
    {
        $reference = 'REF-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $pin = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        
        $sql = "INSERT INTO payment (payment_id, order_id, status, amount, reference, pin, paid_at) 
                VALUES (:payment_id, :order_id, :status, :amount, :reference, :pin, NOW())";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([
            ':payment_id' => $paymentIdBytes,
            ':order_id' => $orderIdBytes,
            ':status' => $status,
            ':amount' => $amount,
            ':reference' => $reference,
            ':pin' => $pin
        ])) {
            return ['reference' => $reference, 'pin' => $pin];
        }
        return null;
    }

    public function processWebhookPayload(string $signature, string $payloadJson): bool
    {
        $secretKey = $_ENV['WEBHOOK_SECRET_KEY'] ?? 'temp_dev_secret_key_12345';
        $expectedSignature = hash_hmac('sha256', $payloadJson, $secretKey);
        
        if (!hash_equals($expectedSignature, $signature)) {
            error_log("Webhook signature mismatch.");
            return false; // signature invalid
        }
        
        $payload = json_decode($payloadJson, true);
        if (!$payload || !isset($payload['paymentSession_id'], $payload['status'])) {
            return false;
        }

        if ($payload['status'] === 'success') {
            // Proceed to establish Order & Escrow
            if (isset($payload['buyer_uid'], $payload['amount'])) {
                try {
                    $this->db->beginTransaction();

                    $buyerUidBytes = hex2bin(str_replace('-', '', $payload['buyer_uid']));
                    $sellerUidBytes = isset($payload['seller_uid']) && !empty($payload['seller_uid']) ? hex2bin(str_replace('-', '', $payload['seller_uid'])) : $buyerUidBytes; // fallback or safe fail

                    // Generate generic random UUID locally to inject into MySQL BINARY(16)
                    $orderIdBytes = random_bytes(16);
                    $paymentIdBytes = random_bytes(16);

                    // Insert into Orders (status 1 = paid)
                    $sqlOrder = "INSERT INTO orders (order_id, buyer_uid, seller_uid, listing_id, order_type, total_amount, status) 
                                 VALUES (:order_id, :buyer_uid, :seller_uid, :listing_id, :order_type, :amount, 1)";
                    $stmtOrder = $this->db->prepare($sqlOrder);
                    $stmtOrder->execute([
                        ':order_id' => $orderIdBytes,
                        ':buyer_uid' => $buyerUidBytes,
                        ':seller_uid' => $sellerUidBytes,
                        ':listing_id' => $payload['listing_id'] ?? null,
                        ':order_type' => $payload['order_type'] ?? 'marketplace',
                        ':amount' => $payload['amount']
                    ]);

                    // Generate Payment Record mapping straight back to the new order
                    $paymentInfo = $this->generatePaymentRecord($orderIdBytes, $paymentIdBytes, $payload['amount'], 1); // 1 = success

                    // Insert into Escrow
                    $sql = "INSERT INTO escrow (payment_id, uid, amount, status) VALUES (:payment_id, :uid, :amount, 'held')";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        ':payment_id' => $paymentIdBytes,
                        ':uid' => $sellerUidBytes, // Typically Escrow is meant for the Seller/Beneficiary
                        ':amount' => $payload['amount']
                    ]);

                    if ($paymentInfo) {
                        $stmtEmail = $this->db->prepare("SELECT BIN_TO_UUID(uid) as uid, email FROM users WHERE uid IN (:b_uid, :s_uid)");
                        $stmtEmail->execute([':b_uid' => $buyerUidBytes, ':s_uid' => $sellerUidBytes]);
                        $users = $stmtEmail->fetchAll(PDO::FETCH_ASSOC);
                        
                        $b_email = null;
                        $s_email = null;
                        foreach ($users as $u) {
                            $u_hex = strtolower(str_replace('-', '', $u['uid']));
                            if ($u_hex === strtolower(bin2hex($buyerUidBytes))) $b_email = $u['email'];
                            if ($u_hex === strtolower(bin2hex($sellerUidBytes))) $s_email = $u['email'];
                        }

                        if ($b_email && $s_email) {
                            require_once __DIR__ . '/ApiService.php';
                            $apiService = new \Lib\services\ApiService();
                            $apiService->send_escrow_notifications($b_email, $s_email, $paymentInfo['reference'], $paymentInfo['pin']);
                        }
                    }

                    // Decrease listing stock if this is a marketplace order
                    if (isset($payload['order_type']) && $payload['order_type'] === 'marketplace' && !empty($payload['listing_id'])) {
                        require_once __DIR__ . '/ApiService.php';
                        $apiService = new \Lib\services\ApiService();
                        $listingData = $apiService->get_listing($payload['listing_id']);
                        $listing = $listingData['listing'] ?? $listingData; 
                        if ($listing && isset($listing['stock']) && $listing['stock'] > 0) {
                            $apiService->update_listing($payload['listing_id'], ['stock' => $listing['stock'] - 1]);
                        }
                    }

                    // For shop orders, reduce stock and clear the cart
                    if (isset($payload['cart_id']) && !empty($payload['cart_id'])) {
                        $cartIdBytes = hex2bin(str_replace('-', '', $payload['cart_id']));
                        $stmtGetCartItems = $this->db->prepare("SELECT product_id, quantity FROM cart_items WHERE cart_id = :cart_id");
                        $stmtGetCartItems->execute([':cart_id' => $cartIdBytes]);
                        $cartItems = $stmtGetCartItems->fetchAll(PDO::FETCH_ASSOC);

                        $stmtUpdateStock = $this->db->prepare("UPDATE shop_products SET stock_quantity = GREATEST(0, stock_quantity - :qty) WHERE product_id = :product_id");
                        foreach ($cartItems as $item) {
                            $stmtUpdateStock->execute([':qty' => $item['quantity'], ':product_id' => $item['product_id']]);
                        }

                        $stmtDeleteCart = $this->db->prepare("DELETE FROM carts WHERE cart_id = :cart_id");
                        $stmtDeleteCart->execute([':cart_id' => $cartIdBytes]);
                    }

                    $this->db->commit();
                } catch (\Exception $e) {
                    $this->db->rollBack();
                    error_log("Webhook Commit Failed: " . $e->getMessage());
                    return false;
                }
            }
        }
        
        return true;
    }

    /*
     Completely bypass the webhook and fake bank, filling the DB tables directly.
     */
    public function bypassPaymentDirectly(array $payload): bool
    {
        if (!isset($payload['buyer_uid'], $payload['amount'])) {
            return false;
        }
        
        try {
            $this->db->beginTransaction();

            $buyerUidBytes = hex2bin(str_replace('-', '', $payload['buyer_uid']));
            $sellerUidBytes = isset($payload['seller_uid']) && !empty($payload['seller_uid']) ? hex2bin(str_replace('-', '', $payload['seller_uid'])) : $buyerUidBytes;

            // Generate generic random UUID locally to inject into MySQL BINARY(16)
            $orderIdBytes = random_bytes(16);
            $paymentIdBytes = random_bytes(16);

            // Insert into Orders (status 1 = paid)
            $sqlOrder = "INSERT INTO orders (order_id, buyer_uid, seller_uid, shop_id, cart_id, listing_id, order_type, total_amount, status) 
                         VALUES (:order_id, :buyer_uid, :seller_uid, :shop_id, :cart_id, :listing_id, :order_type, :amount, 1)";
            $stmtOrder = $this->db->prepare($sqlOrder);
            
            $shopIdBytes = isset($payload['shop_id']) && !empty($payload['shop_id']) ? hex2bin(str_replace('-', '', $payload['shop_id'])) : null;
            $cartIdBytes = isset($payload['cart_id']) && !empty($payload['cart_id']) ? hex2bin(str_replace('-', '', $payload['cart_id'])) : null;

            $stmtOrder->execute([
                ':order_id' => $orderIdBytes,
                ':buyer_uid' => $buyerUidBytes,
                ':seller_uid' => $sellerUidBytes,
                ':shop_id' => $shopIdBytes,
                ':cart_id' => $cartIdBytes,
                ':listing_id' => $payload['listing_id'] ?? null,
                ':order_type' => $payload['order_type'] ?? 'marketplace',
                ':amount' => $payload['amount']
            ]);

            // Generate Payment Record mapping straight back to the new order
            $paymentInfo = $this->generatePaymentRecord($orderIdBytes, $paymentIdBytes, $payload['amount'], 1); // 1 = success

            // Insert into Escrow
            $sql = "INSERT INTO escrow (payment_id, uid, amount, status) VALUES (:payment_id, :uid, :amount, 'held')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':payment_id' => $paymentIdBytes,
                ':uid' => $sellerUidBytes,
                ':amount' => $payload['amount']
            ]);

            if ($paymentInfo) {
                // Fetch emails
                $stmtEmail = $this->db->prepare("SELECT BIN_TO_UUID(uid) as uid, email FROM users WHERE uid IN (:b_uid, :s_uid)");
                $stmtEmail->execute([':b_uid' => $buyerUidBytes, ':s_uid' => $sellerUidBytes]);
                $users = $stmtEmail->fetchAll(PDO::FETCH_ASSOC);
                
                $b_email = null;
                $s_email = null;
                foreach ($users as $u) {
                    $u_hex = strtolower(str_replace('-', '', $u['uid']));
                    if ($u_hex === strtolower(bin2hex($buyerUidBytes))) $b_email = $u['email'];
                    if ($u_hex === strtolower(bin2hex($sellerUidBytes))) $s_email = $u['email'];
                }

                if ($b_email && $s_email) {
                    require_once __DIR__ . '/ApiService.php';
                    $apiService = new \Lib\services\ApiService();
                    $apiService->send_escrow_notifications($b_email, $s_email, $paymentInfo['reference'], $paymentInfo['pin']);
                }
            }

            // Decrease listing stock if this is a marketplace order
            if (isset($payload['order_type']) && $payload['order_type'] === 'marketplace' && !empty($payload['listing_id'])) {
                require_once __DIR__ . '/ApiService.php';
                $apiService = new \Lib\services\ApiService();
                $listingData = $apiService->get_listing($payload['listing_id']);
                $listing = $listingData['listing'] ?? $listingData; 
                if ($listing && isset($listing['stock']) && $listing['stock'] > 0) {
                    $apiService->update_listing($payload['listing_id'], ['stock' => $listing['stock'] - 1]);
                }
            }

            // If it is a shop order with a cart, clear the cart after successful payment
            if (isset($payload['cart_id']) && !empty($payload['cart_id'])) {
                $stmtGetCartItems = $this->db->prepare("SELECT product_id, quantity FROM cart_items WHERE cart_id = :cart_id");
                $stmtGetCartItems->execute([':cart_id' => $cartIdBytes]);
                $cartItems = $stmtGetCartItems->fetchAll(PDO::FETCH_ASSOC);

                $stmtUpdateStock = $this->db->prepare("UPDATE shop_products SET stock_quantity = GREATEST(0, stock_quantity - :qty) WHERE product_id = :product_id");
                foreach ($cartItems as $item) {
                    $stmtUpdateStock->execute([':qty' => $item['quantity'], ':product_id' => $item['product_id']]);
                }

                $stmtDeleteCart = $this->db->prepare("DELETE FROM carts WHERE cart_id = :cart_id");
                $stmtDeleteCart->execute([':cart_id' => $cartIdBytes]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Direct Payment Bypass Failed: " . $e->getMessage());
            return false;
        }
    }
}