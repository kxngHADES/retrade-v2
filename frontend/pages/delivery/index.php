<?php

require_once __DIR__ . '/../../config/bootstrap.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method="post">
        <label>Enter Reference: </label>
        <input type="text" name="ref" placeholder="REF-#">
        <br/><br/>
        <label>Enter Pin: </label>
        <input type="number" min="000000" max="999999" name="pin" placeholder="123456">
        <br/><br/>
        <button type="submit">Enter</button>
    </form>
</body>
</html>