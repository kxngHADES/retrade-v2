<?php

namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\db\Supabase;
use Exception;

class supabase_service {
    private $client;

	public function __construct() {
		$this->client = Supabase::getInstance();
	}

    //Generic GET
    public function fetch(string $table, array $query = []): array {
		$response = $this->client->get($table, [
			'query' => $query
		]);

		$this->handleError($response);

		return json_decode($response->getBody(), true) ?? [];
	}

	// Generic INSERT
	public function insert(string $table, array $data): array {
		$response = $this->client->post($table, [
			'json' => $data
		]);

		$this->handleError($response);

		return json_decode($response->getBody(), true) ?? [];
	}

	// Generic UPDATE
	public function update(string $table, array $filters, array $data): array {
		$response = $this->client->patch($table, [
			'query' => $filters,
			'json' => $data
		]);

		$this->handleError($response);

		return json_decode($response->getBody(), true) ?? [];
	}

	// Generic DELETE
	public function delete(string $table, array $filters): bool {
		$response = $this->client->delete($table, [
			'query' => $filters
		]);

		$this->handleError($response);

		return $response->getStatusCode() === 204;
	}

	// Get user balance
	public function getUserBalance(string $userId): ?float {
		$data = $this->fetch('users', [
			'select' => 'balance',
			'id' => 'eq.' . $userId,
			'limit' => 1
		]);

		if (empty($data)) {
			return null;
		}

		return (float) $data[0]['balance'];
	}

	// Example: Update user balance
	public function updateUserBalance(string $userId, float $amount): bool {
		$response = $this->update('users', [
			'id' => 'eq.' . $userId
		], [
			'balance' => $amount
		]);

		return !empty($response);
	}

    /**
     * Handle the fake bank charge directly inside Supabase Service
     */
    public function chargeFakeBank(string $userId, float $amount): bool {
        try {
            $currentBalance = $this->getUserBalance($userId);

            if ($currentBalance === null || $currentBalance < $amount) {
                // Insufficient funds
                return false;
            }

            // Deduct funds
            $newBalance = $currentBalance - $amount;
            return $this->updateUserBalance($userId, $newBalance);
            
        } catch (Exception $e) {
            error_log("Supabase deduction failed: " . $e->getMessage());
            return false;
        }
    }

	// Centralized error handler
	private function handleError($response): void {
		$status = $response->getStatusCode();

		if ($status >= 400) {
			$body = $response->getBody()->getContents();
			error_log("Supabase Error [$status]: $body");

			throw new Exception("Supabase request failed with status $status");
		}
	}
}