<?php

namespace Lib\services;
require_once __DIR__ . '/../../config/bootstrap.php';

use Dotenv\Dotenv;

class sms_services {

	public function __construct()
	{
		if (empty($_ENV['BACKEND_INTERNAL_URL'])) {
			$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
			$dotenv->load();
	 	}
	}

	public function send_otp(string $phone, int $otp){
		$normal_phone_number = $this->normalizePhoneNumber($phone);

		$apiUrl = $_ENV['BACKEND_INTERNAL_URL'] . '/auth/send-otp';

		$payload = [
    	'phone' => $normal_phone_number,
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

		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		curl_close($ch);


		if ($curlError) {
			error_log("SMS API cURL error: $curlError");
			return false;
		}

		if ($httpCode === 200) {
			$decoded = json_decode($response, true);
			return isset($decoded['success']) && $decoded['success'] === true;
		}

		error_log("SMS API failed with HTTP $httpCode: $response");
		return false;
		
	}

	function normalizePhoneNumber(string $number): string {

		$number = preg_replace('/[\s\-]/', '', $number);

		if (str_starts_with($number, '+27')) {
			return $number;
		}

		if (str_starts_with($number, '0')) {
			return '+27' . substr($number, 1);
		}
		
		if (preg_match('/^[1-9]/', $number)) {
			return '+27' . $number;
		}

		return $number;
	}

}