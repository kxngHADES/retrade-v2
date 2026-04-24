<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

register_shutdown_function(function() {
	$error = error_get_last();
	if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
		http_response_code(500);
		echo json_encode(["error" => "Internal server error", "details" => getenv('APP_DEBUG') ? $error['message'] : null]);
	}
});

require_once __DIR__ . '/../../config/bootstrap.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Validate required env vars early
$requiredEnv = ['AWS_DEFAULT_REGION', 'MINIO_ENDPOINT', 'AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_BUCKET', 'AWS_URL'];
foreach ($requiredEnv as $key) {
	if (empty($_ENV[$key])) {
		http_response_code(500);
		echo json_encode(["error" => "Configuration error", "missing" => $key]);
		exit;
	}
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
	http_response_code(400);
	echo json_encode(["error" => "File upload failed", "code" => $_FILES['file']['error'] ?? 'unknown']);
	exit;
}

$path = $_GET['path'] ?? null;
if (!$path) {
	http_response_code(400);
	echo json_encode(["error" => "Missing 'path' parameter"]);
	exit;
}

// Sanitize path to prevent directory traversal
$path = str_replace('\\', '/', $path);
// Remove any ".." segments
$path = preg_replace('#\.*//+#', '/', $path); // handle ....//
$segments = explode('/', $path);
$safeSegments = array_filter($segments, function($segment) {
	return $segment !== '.' && $segment !== '..';
});
$path = implode('/', $safeSegments);
$path = ltrim($path, '/');

if (empty($path)) {
	http_response_code(400);
	echo json_encode(["error" => "Invalid path"]);
	exit;
}

$file = $_FILES['file']['tmp_name'];
if (!is_readable($file)) {
	http_response_code(400);
	echo json_encode(["error" => "Uploaded file not readable"]);
	exit;
}

try {
	$s3 = new S3Client([
		'version' => 'latest',
		'region' => $_ENV['AWS_DEFAULT_REGION'],
		'endpoint' => $_ENV['MINIO_ENDPOINT'],
		'use_path_style_endpoint' => true,
		'credentials' => [
			'key' => $_ENV['AWS_ACCESS_KEY_ID'],
			'secret' => $_ENV['AWS_SECRET_ACCESS_KEY']
		]
	]);

	$bucket = $_ENV['AWS_BUCKET'];

	$s3->putObject([
		'Bucket' => $bucket,
		'Key' => $path,
		'SourceFile' => $file,
		'ACL' => 'public-read',
		'ContentType' => mime_content_type($file) ?: 'application/octet-stream'
	]);

	echo json_encode([
		"url" => rtrim($_ENV['AWS_URL'], '/') . '/' . ltrim($path, '/')
	]);

} catch (AwsException $e) {
	error_log("MinIO/S3 Error: " . $e->getMessage());
	http_response_code(502);
	echo json_encode(["error" => "Storage service unavailable"]);
	exit;
} catch (Exception $e) {
	error_log("Upload Exception: " . $e->getMessage());
	http_response_code(500);
	echo json_encode(["error" => "Upload failed", "details" => (getenv('APP_DEBUG') ? $e->getMessage() : null)]);
	exit;
}