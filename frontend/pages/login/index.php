<?php
session_start();
$_SESSION['lang'] = $_GET['lang'] ?? $_SESSION['lang'] ?? 'en';

require_once __DIR__ . '/../../config/bootstrap.php';
use Lib\services\Auth_flow;

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$auth = new Auth_flow;

	$result = $auth->login($_POST);

	if (!$result['success']) {
		$error = $result['error'];
	}
}

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang']); ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= trans('title'); ?> - Login</title>
	<link rel="stylesheet" href="/assets/css/global.css">
	<link rel="stylesheet" href="/assets/css/forms.css">
</head>
<body class="auth-layout">
	<main class="auth-container">
		<header class="auth-header">
			<h1 class="auth-title font-display text-display"><?= trans('title'); ?></h1>
			<p class="auth-slogan text-small"><?= trans('slogan'); ?></p>
		</header>

		<form action="" method="post" class="auth-form">
			<?php if ($error): ?>
				<span class="form-error"><?= htmlspecialchars($error); ?></span>
			<?php endif; ?>

			<div class="form-group">
				<label class="form-label label" for="email"><?= trans('email-address'); ?></label>
				<input class="form-input" id="email" type="email" name="email" placeholder="name@example.com" required>
			</div>

			<div class="form-group">
				<label class="form-label label" for="password"><?= trans('password'); ?></label>
				<div class="password-wrapper">
					<input class="form-input password-input" id="password" type="password" name="password" placeholder="••••••••" required>
				</div>
			</div>

			<button class="btn auth-btn" type="submit"><?= trans('sign in'); ?></button>
		</form>

		<div class="auth-footer">
			<a class="text-small" href="/pages/register/"><?= trans("Don't have an account? Register"); ?></a>
		</div>
	</main>
</body>
</html>