<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';

use Lib\cache\Redis;

// Disable session expiration/closure issues
session_write_close();

header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header("Connection: keep-alive");
header("X-Accel-Buffering: no");

if (empty($_SESSION['uid'])) {
    echo "data: {\"error\": \"Unauthorized\"}\n\n";
    exit;
}

$uid = $_SESSION['uid'];
$channel = "chat_user_" . $uid;

$redis = Redis::getInstance()->getClient();
$pubsub = $redis->pubSubLoop();
$pubsub->subscribe($channel);

foreach ($pubsub as $message) {
    if ($message->kind === 'message') {
        echo "data: " . $message->payload . "\n\n";
        ob_flush();
        flush();
    }
}
