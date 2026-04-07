<?php
namespace Lib\cache;

use Predis\Client;
use Predis\Connection\ConnectionException;

class Redis {
	
	// Singleton instance
	private static ?Redis $instance = null;
	
	// Redis client
	private Client $client;
	
	// Private constructor (Singleton pattern)
	private function __construct() {
		// Load .env variables
		$host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
		$port = $_ENV['REDIS_PORT'] ?? 6379;
		$password = $_ENV['REDIS_PASSWORD'] ?? null;
		$database = $_ENV['REDIS_DATABASE'] ?? 0;
		
		// Build connection parameters
		$params = [
			'scheme' => 'tcp',
			'host' => $host,
			'port' => $port,
			'database' => $database,
		];
		
		// Add password if set
		if (!empty($password)) {
			$params['password'] = $password;
		}
		
		// Create Redis client
		$this->client = new Client($params);
		
		// Test connection
		try {
			$this->client->ping();
			error_log("✅ Redis connected successfully!");
		} catch (ConnectionException $e) {
			error_log("❌ Redis connection failed: " . $e->getMessage());
			throw $e;
		}
	}
	
	// Get singleton instance
	public static function getInstance(): Redis {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	// Prevent cloning
	private function __clone() {}
	
	// Prevent unserialization
	public function __wakeup() {
		throw new \Exception("Cannot unserialize singleton");
	}
	
	// Get the raw Predis client
	public function getClient(): Client {
		return $this->client;
	}


	
	
	// ==========================================
	// Helper Methods for Common Use Cases
	// ==========================================
	
	//Set a key with expiration (useful for OTP)
	public function setEx(string $key, string $value, int $ttl): bool {
		return $this->client->setex($key, $ttl, $value) === 'OK';
	}
	
	//Get a value by key
	public function get(string $key): ?string {
		$value = $this->client->get($key);
		return $value ?? null;
	}
	
	//Delete a key
	public function delete(string $key): int {
		return $this->client->del($key);
	}
	
	//Check if key exists
	public function exists(string $key): bool {
		return $this->client->exists($key) === 1;
	}
	
	//Increment a counter (useful for rate limiting)
	public function increment(string $key): int {
		return $this->client->incr($key);
	}
	
	//Store session data
	public function setSession(string $sessionId, array $data, int $ttl = 3600): bool {
		return $this->client->setex("session:$sessionId", $ttl, json_encode($data)) === 'OK';
	}
	
	//Get session data
	public function getSession(string $sessionId): ?array {
		$value = $this->client->get("session:$sessionId");
		return $value ? json_decode($value, true) : null;
	}
	
	//store OTP
	public function setOTP(string $identifier, string $otp, int $ttl = 300): bool {
		return $this->setEx("otp:$identifier", $otp, $ttl);
	}
	
	//Verify OTP
	public function verifyOTP(string $identifier, string $otp): bool {
		$storedOTP = $this->get("otp:$identifier");
		if ($storedOTP === $otp) {
			$this->delete("otp:$identifier"); // Delete after successful verification
			return true;
		}
		return false;
	}



	// Store user data temporarily
	public function storeUserTemp(array $userData, int $ttl = (60*15)): bool {
		$result = $this->client->hmset($userData['phoneNumber'], $userData);
		if ($result == 'ok') {
			$this->client->expire($userData['phoneNumber'], $ttl);
			return true;
		}

		return false;
	}
	
	// Publish message to channel
	public function publish(string $channel, string $message): int {
		return $this->client->publish($channel, $message);
	}
	
	public function flush(): bool {
		return $this->client->flushdb() === 'OK';
	}
}