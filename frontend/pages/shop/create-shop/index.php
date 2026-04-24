<?php

require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';
require_once __DIR__ . '/../../../utils/id_verified_screens.php';

use Lib\services\shop_service;

if ($_SERVER['REQUEST_METHOD'] === "POST") {
	$shop_name = $_POST['shop_name'];
	$uid = $_SESSION['uid'];

	$shop_service = new shop_service();

	$shop_service->createShop($uid, $shop_name);
}

# TODO add error display

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>

<?php if (!empty($_SESSION['flash_verify'])): ?>
	<?php unset($_SESSION['flash_verify']); ?>
	<script>
		window.addEventListener('DOMContentLoaded', () => {
			alert('You need to verify your ID before accessing this page.');
			window.location.href = '/pages/profile/verify_id';
		});
	</script>
<?php endif; ?>

<body>
	<div>
		<form action="" method="post">
			<fieldset>
				<legend>Create Shop</legend>
				<label>Shop name:</label>
				<input type="text" name="shop_name" required placeholder="Sfiso's spaza"><br/><br/>
				<input type="submit" value="Reister Shop">
			</fieldset>
		</form>
	</div>
	
</body>
</html>