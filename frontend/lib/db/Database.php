<?php
namespace Lib\db;
require_once __DIR__ . '/../../config/bootstrap.php';

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database {
	public static function getConnection(): PDO {
		$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
		$dotenv->load();

		$dsn = sprintf(
			"mysql:host=%s;dbname=%s;charset=%s",
			$_ENV['DB_HOST'],
			$_ENV['DB_NAME'],
			$_ENV['DB_CHARSET']
		);

		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];

		try {
			return new PDO(
				$dsn,
				$_ENV['DB_USER'],
				$_ENV['DB_PASS'],
				$options
			);
		} catch (PDOException $e) {
			error_log("Database Connection Failed " . $e->getMessage());
			error_log("Available PDO drivers: " . implode(',', PDO::getAvailableDrivers()));

			throw new PDOException("Failed to connect to database.", (int)$e->getCode());
		}
	}
}