<?php


require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';
use Lib\services\listing_service;

$uid = $_SESSION['uid'];
$listing_service = new listing_service();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $des = $_POST['description'];

    $listing_service->createListing($uid, $name, $des);
}


?>
<!DOCTYPE html>
<html lang= <?= $_SESSION['lang'] ?? 'en' ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method="post">
        <fieldset>
            <legend>Create listing</legend>
            <label>Name: </label><input type="text" name="name" required placeholder="Product Name">
            <br/><br/>
            <label>Description:</label><br/>
            <textarea name="description" rows="4" cols="30" placeholder="Write a description of the product"></textarea>
            <br/><br/>
            <input type="submit" value="Create Listing">
        </fieldset>
    </form>
</body>
</html>