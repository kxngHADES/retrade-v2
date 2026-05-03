<?php
$file = 'frontend/lib/services/payment_gateways_services.php';
$content = file_get_contents($file);
$content = str_replace('getPaymentSession(string $sessionToken)', 'getPaymentSession(int $sessionId)', $content);
$content = str_replace('lockSessionForProcessing(string $sessionToken)', 'lockSessionForProcessing(int $sessionId)', $content);
$content = str_replace('processFakeBankPayment(string $uid, string $sessionToken', 'processFakeBankPayment(string $uid, int $sessionId', $content);
$content = str_replace('updateSessionStatus(string $sessionToken', 'updateSessionStatus(int $sessionId', $content);
$content = str_replace('fireWebhook(string $sessionToken', 'fireWebhook(int $sessionId', $content);

$content = str_replace('paymentSession_id = :token', 'paymentSession_id = :id', $content);
$content = str_replace("execute([':token' => \$sessionToken])", "execute([':id' => \$sessionId])", $content);

$content = str_replace("INSERT INTO payment_sessions (paymentSession_id", "INSERT INTO payment_sessions (", $content);
$content = str_replace("VALUES (:token, :email, :amount, 'pending', DATE_ADD(NOW(), INTERVAL 15 MINUTE))", "VALUES (:email, :amount, 'pending', DATE_ADD(NOW(), INTERVAL 15 MINUTE))", $content);
$content = preg_replace("/\\\$sessionToken = bin2hex\\(random_bytes\\(32\\)\\);\\n\\s*\\\$sql = \"INSERT INTO payment_sessions \\(user_email/", "\$sql = \"INSERT INTO payment_sessions (user_email", $content);
$content = str_replace("':token' => \$sessionToken,\n            ':email' => \$email,\n            ':amount' => \$amount", "':email' => \$email,\n            ':amount' => \$amount", $content);
$content = str_replace("return \$sessionToken;", "return (string)\$this->db->lastInsertId();", $content);

file_put_contents($file, $content);
