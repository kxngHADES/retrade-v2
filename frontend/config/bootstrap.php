<?php

error_log("bootstrap.php loaded from: " . (debug_backtrace()[1]['file'] ?? 'direct call'));

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (session_status() === PHP_SESSION_NONE){
	session_start();
}

$logsDir =  __DIR__ . '/../logs';

if (!is_dir($logsDir)) {
	mkdir($logsDir, 0755, true);
}

ini_set('error_log', $logsDir . '/app-errors.log');
ini_set('log_errors', 'On');

ini_set('display_errors', 'On'); // set to off in production
ini_set('display_startup_errors', 'On'); // set to off in production

error_reporting(E_ALL);


date_default_timezone_set('Africa/Johannesburg');



// Load env vars
if (file_exists(__DIR__ . '/../.env')) {
	$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
	$dotenv->load();
}


//Languages
if (!function_exists('trans')) {
	function trans($key) {
			static $translations = [];

			if (empty($translations)) {
					$lang = $_SESSION['lang'] ?? 'en';


					$file_path = __DIR__ . '/../lang/' . $lang . '.php';

					if (!file_exists($file_path)) {
							$file_path = __DIR__ . '/../lang/en.php';
					}

					$translations = include $file_path;
			}

			return $translations[$key] ?? $key;
	}
}