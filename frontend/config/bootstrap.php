<?php

require __DIR__ . '/../vendor/autoload.php';

define('PROJECT_ROOT', dirname(__DIR__));

$logsDir = PROJECT_ROOT . '/logs';

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