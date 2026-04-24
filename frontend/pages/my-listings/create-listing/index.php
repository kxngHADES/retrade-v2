<?php


require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';
require_once __DIR__ . '/../../../utils/id_verified_screens.php';

use Lib\services\listing_service;

$uid = $_SESSION['uid'];
$listing_service = new listing_service();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listing_service->createListing($uid, $_POST);
}


?>
<!DOCTYPE html>
<html lang= <?= $_SESSION['lang'] ?? 'en' ?>>
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
	<form action="" method="post" enctype="multipart/form-data">
		<fieldset>
			<legend>Create listing</legend>

			<label>Name:</label>
			<input type="text" name="name" required>
			<br/><br/>

			<label>Description:</label><br/>
			<textarea name="description" rows="4" cols="30"></textarea>
			<br/><br/>

			<label>Price:</label>
			<input type="number" name="price" step="0.01" required>
			<br/><br/>

			<label>Stock:</label>
			<input type="number" name="stock" required>
			<br/><br/>

			<label>Condition:</label>
			<input type="text" name="condition">
			<br/><br/>

			<label>Category:</label>
			<input type="text" name="category">
			<br/><br/>

			<label>Location:</label>
			<input type="text" name="location">
			<br/><br/>

			<label>Delivery Method:</label>
			<input type="text" name="delivery_method">
			<br/><br/>

			<label>Tags (comma separated):</label>
			<input type="text" name="tags">
			<br/><br/>

			<label>Thumbnail:</label>
			<input type="file" id="thumbnail" accept="image/*" required>
			<br/><br/>

			<label>Images:</label>
			<input type="file" id="images" accept="image/*" multiple>
			<br/><br/>

			<input type="hidden" name="thumbnail_url" id="thumbnail_url">
			<input type="hidden" name="list_of_image_url" id="list_of_image_url">

			<input type="submit" value="Create Listing">
		</fieldset>
	</form>

	<script>
		window.UID = "<?= $_SESSION['uid'] ?>";
	</script>

	<script src="/assets/js/upload.js" defer></script>
</body>
</html>