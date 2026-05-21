<?php

namespace Lib\db;

require_once __DIR__ . '/../../config/bootstrap.php';

use GuzzleHttp\Client;
use Dotenv\Dotenv;

class Supabase {
	private static ?Client $instance = null;

	public static function getInstance(): Client{
		if (self::$instance === null){
			$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
			$dotenv->load();
		

		self::$instance = new Client([
				'base_uri' => rtrim($_ENV['SUPABASE_URL'], '/') . '/rest/v1/',
				'headers' => [
					'apikey' => $_ENV['SUPABASE_SERVICE_ROLE_KEY'],
					'Authorization' => 'Bearer ' . $_ENV['SUPABASE_SERVICE_ROLE_KEY'],
					'Content-Type' => 'application/json',
					'Prefer' => 'return=representation'
				],
				'http_errors' => false
			]);
		}

		return self::$instance;
	}
}