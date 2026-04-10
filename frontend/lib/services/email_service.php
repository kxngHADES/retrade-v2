<?php

namespace Lib\services;

require_once __DIR__ . '/../../config/bootstrap.php';

use Dotenv\Dotenv;

class email_service {
	public function __construct()
	{
		if (empty($_ENV['BACKEND_INTERNAL_URL'])) {
			$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
			$dotenv->load();
	 	}
	}

	public function send_otp(string $email, int $otp){
		$apiUrl = $_ENV['BACKEND_INTERNAL_URL'] . '/auth/validate-email';

		$payload = [
			'email' => $email,
			'otp' => $otp
		];

		$ch = curl_init($apiUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json'
		]);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);

		if ($curlError) {
			error_log("Email API Error: $curlError");
			return false;
		}

		if ($httpCode === 200) {
			$decoded = json_decode($response, true);
			return isset($decoded['success']) && $decoded['success'] === true;
		}

		error_log("Email API failed with HTTP $httpCode: $response");
		return false;
	}
}