<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$isLoggedIn = isset($_SESSION['uid']);
$firstName = $isLoggedIn ? $_SESSION['firstName'] : null;
$email = $isLoggedIn ? $_SESSION['email'] : null;
$avatar = $isLoggedIn ? $_SESSION['profile_image_url'] : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>

