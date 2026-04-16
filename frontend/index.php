<?php

require_once __DIR__ . '/config/bootstrap.php';

echo "<pre>";
echo "Session ID: " . session_id() . "\n\n";
echo "All session data:\n";
print_r($_SESSION);
echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <!--<a href="/pages/login">Login</a><br/>
    <a href="/pages/register">Register</a>-->
</body>
</html>