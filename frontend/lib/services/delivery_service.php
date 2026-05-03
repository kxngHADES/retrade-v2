<?php


namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';

use PDO;
use Lib\db\Database;

class delivery_service {
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }


    /*
     Orchastrating function
    */
    public function deliveredGoods(string $reference, string $pin) {
        $payment_id = $this->getPayment($reference, $pin);

        if (empty($payment_id)) {
            return false;
        }

        $is_released = $this->releaseEscrow($payment_id);

        if ($is_released){
            $this->notifyRelease($payment_id);
            return true;
        }

        return false;
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