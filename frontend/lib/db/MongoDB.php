<?php

declare(strict_types=1);

namespace Lib\db;

use MongoDB\Client;
use MongoDB\Database;

class MongoDB {
	private static ?Client $client = null;
	private static ?Database $db = null;

	private function __construct() {}
	private function __clone() {}

	private static function getDatabase(): Database {
		if (self::$db === null) {
			$uri = $_ENV['MONGO_URI'];

			try {
				self::$client = new Client($uri, [
					'driver' => [
						'name' => 'ReTrade-PHP', 
					'version' => '0.0.1',],
				]);
				self::$db = self::$client->selectDatabase('retrade-docs');
			} catch (\Exception $e) {
				error_log("MongoDB connection Failed: " . $e->getMessage());
				throw new \RuntimeException("Document store connection failed.");
			}
		}
		return self::$db;
	}

	public static function getClient(): Client {
		self::getDatabase();
		return self::$client;
	}
}