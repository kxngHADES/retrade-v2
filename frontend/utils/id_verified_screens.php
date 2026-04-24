<?php

require_once __DIR__ . '/../config/bootstrap.php';

$uid = $_SESSION['uid'];

use Lib\db\Database;


$db = Database::getConnection();

# check if id is verified
$sql = "SELECT * FROM users WHERE is_id_verified = 1 AND uid = UUID_TO_BIN(:uid) LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute(['uid'=>$uid]);
$is_verified = (bool) $stmt->fetch(PDO::FETCH_ASSOC);

if (!$is_verified){
    $_SESSION['flash_verify'] = true;
}

?>