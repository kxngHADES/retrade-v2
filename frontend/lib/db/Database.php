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
			$_ENV['DB_HOST'] ?? '',
			$_ENV['DB_NAME'] ?? '',
			$_ENV['DB_CHARSET'] ?? 'utf8mb4'
		);

		if (empty($_ENV['DB_HOST']) || empty($_ENV['DB_NAME']) || empty($_ENV['DB_USER'])) {
			error_log(sprintf(
				"Database config incomplete: DB_HOST=%s DB_NAME=%s DB_USER=%s",
				$_ENV['DB_HOST'] ?? 'missing',
				$_ENV['DB_NAME'] ?? 'missing',
				$_ENV['DB_USER'] ?? 'missing'
			));
		}

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
			error_log("Database DSN: " . $dsn . " user=" . ($_ENV['DB_USER'] ?? 'missing'));
			error_log("Available PDO drivers: " . implode(',', PDO::getAvailableDrivers()));

			throw new PDOException("Failed to connect to database.", (int)$e->getCode());
		}
	}
}