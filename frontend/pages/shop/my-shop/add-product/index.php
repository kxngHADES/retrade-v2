<?php

require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../utils/protected_route.php';
require_once __DIR__ . '/../../../../utils/id_verified_screens.php';

use Lib\services\shop_service;

$uid = $_SESSION['uid'];

$shop_service = new shop_service();

$shop = $shop_service->getShop($uid);

$shop_id = $shop['shop_id'];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    $shop_service->addShopProduct($shop_id, $name, $description, $price, $quantity);
    
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div>
        <form action="" method="post">
            <fieldset>
                <legend>Add Product</legend>
            </fieldset>
            <label>Product Name:</label>
            <input type="text" placeholder="Product name" required name="name">
            <br/><br/>

            <label>Description:</label>
            <br/>
            <textarea name="description" rows="4" cols="30"></textarea>
            <br/><br/>

            <label>Price: R</label>
            <input type="number" step="0.01" min="0" name="price" placeholder="0.00">
            <br/><br/>

            <label>Quantity</label>
            <input type="number" step="1" min="0" name="quantity" value="1">
            <br/><br/>

            <input type="submit" value="Submit product">
        </form>
    </div>
</body>
</html>
