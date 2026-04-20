<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env once
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Start session if not already
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Logging setup
$logsDir = __DIR__ . '/../logs';
if (!is_dir($logsDir)) {
	mkdir($logsDir, 0755, true);
}
ini_set('error_log', $logsDir . '/app-errors.log');
ini_set('log_errors', '1');

// Production-safe error display
if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', '0');
	ini_set('display_startup_errors', '0');
	error_reporting(0);
}

date_default_timezone_set('Africa/Johannesburg');

// Translation helper
if (!function_exists('trans')) {
	function trans($key) {
		static $translations = [];
		if (empty($translations)) {
			$lang = $_SESSION['lang'] ?? 'en';
			$file = __DIR__ . '/../lang/' . $lang . '.php';
			if (!file_exists($file)) $file = __DIR__ . '/../lang/en.php';
			$translations = include $file;
		}
		return $translations[$key] ?? $key;
	}
}