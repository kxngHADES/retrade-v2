<?php


namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';

use PDO;
use Lib\db\Database;
use Lib\cache\Redis;

class delivery_service {
    private PDO $db;
    private ?Redis $redis = null;
    private const RATE_LIMIT_PREFIX = 'delivery:attempts:';
    private const RATE_LIMIT_MAX_ATTEMPTS = 5;
    private const RATE_LIMIT_WINDOW = 900; // 15 minutes

    public function __construct()
    {
        $this->db = Database::getConnection();

        try {
            $this->redis = Redis::getInstance();
        } catch (\Throwable $e) {
            $this->redis = null;
            error_log('Delivery rate limiter unavailable: ' . $e->getMessage());
        }
    }

    /*
     Orchastrating function
    */
    public function deliveredGoods(string $reference, string $pin): array {
        if ($this->isRateLimited($reference)) {
            return [
                'success' => false,
                'error' => $this->getRateLimitMessage($reference),
            ];
        }

        $payment_id = $this->getPayment($reference, $pin);

        if (empty($payment_id)) {
            $this->incrementRateLimit($reference);
            return [
                'success' => false,
                'error' => 'Invalid Reference/PIN or transaction is already completed',
            ];
        }

        $is_released = $this->releaseEscrow($payment_id);

        if ($is_released) {
            $this->resetRateLimit($reference);
            $this->notifyRelease($payment_id);
            return ['success' => true];
        }

        $this->incrementRateLimit($reference);
        return [
            'success' => false,
            'error' => 'Invalid Reference/PIN or transaction is already completed',
        ];
    }

    /*
    Step 1: Get the reference and Pin
    */

    public function getPayment(string $reference, string $pin) {
        $sql = "SELECT BIN_TO_UUID(payment_id) as payment_id FROM payment WHERE reference = :reference AND pin = :pin";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'reference' => $reference,
            'pin' => $pin
        ]);

        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        return $payment['payment_id'] ?? null;
    }

    private function getRateLimitKey(string $reference): ?string {
        if (!$this->redis) {
            return null;
        }

        $normalized = preg_replace('/[^A-Za-z0-9_-]/', '', $reference);
        return self::RATE_LIMIT_PREFIX . ($normalized ?: 'unknown');
    }

    private function isRateLimited(string $reference): bool {
        $key = $this->getRateLimitKey($reference);
        if (!$key) {
            return false;
        }

        $count = $this->redis->getClient()->get($key);
        if ($count === null) {
            return false;
        }

        return (int)$count >= self::RATE_LIMIT_MAX_ATTEMPTS;
    }

    private function incrementRateLimit(string $reference): void {
        $key = $this->getRateLimitKey($reference);
        if (!$key) {
            return;
        }

        $client = $this->redis->getClient();
        $current = $client->incr($key);
        if ($current === 1) {
            $client->expire($key, self::RATE_LIMIT_WINDOW);
        } else {
            $ttl = $client->ttl($key);
            if ($ttl === -1) {
                $client->expire($key, self::RATE_LIMIT_WINDOW);
            }
        }
    }

    private function resetRateLimit(string $reference): void {
        $key = $this->getRateLimitKey($reference);
        if (!$key) {
            return;
        }

        $this->redis->delete($key);
    }

    private function getRateLimitMessage(string $reference): string {
        $key = $this->getRateLimitKey($reference);
        if (!$key) {
            return 'Too many delivery attempts. Please wait and try again later.';
        }

        $ttl = $this->redis->getClient()->ttl($key);
        if ($ttl < 0) {
            $ttl = self::RATE_LIMIT_WINDOW;
        }

        $minutes = ceil($ttl / 60);
        return "Too many delivery attempts. Please try again in {$minutes} minute(s).";
    }

    /*
     Step 2: Release the escrow funds
     */
    public function releaseEscrow(string $payment_id): bool {
        $sql = "UPDATE escrow SET status = 'released', paid_out_date = NOW() WHERE payment_id = UUID_TO_BIN(:payment_id) AND status = 'held'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'payment_id' => $payment_id
        ]);

        return $stmt->rowCount() > 0;
    }

    /*
     Step 3: Send a email to notify the release of funds
    */
    public function notifyRelease(string $payment_id) {
        $sql = "SELECT u.email, e.amount 
                FROM escrow e 
                JOIN users u ON e.uid = u.uid 
                WHERE e.payment_id = UUID_TO_BIN(:payment_id) LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['payment_id' => $payment_id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            require_once __DIR__ . '/ApiService.php';
            $apiService = new \Lib\services\ApiService();
            $apiService->send_payout_notification($data['email'], (float)$data['amount']);
        }
    }
}