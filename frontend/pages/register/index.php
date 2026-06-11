<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use Lib\services\Auth_flow;

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	if ($_POST['password'] !== $_POST['confirmPassword']){
		$error = "Passwords do not match";
	} else {
		$auth = new Auth_flow();
		$result = $auth->start_registration_flow($_POST);
		if ($result === true) {
			$_SESSION['phoneNumber'] = $_POST['phoneNumber'];
			header('Location: /pages/register/verify');
			exit;
		} elseif (is_string($result)) {
			$error = $result;
		} else {
			$error = "Registration temporarily unavailable. Please try again later.";
		}
	}
}
?>
<!DOCTYPE html>
<html lang=<?= $_SESSION['lang'] ?? 'en'; ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= trans('Register'); ?></title>
    <link rel="stylesheet" href="../../assets/css/variables.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/forms.css">
</head>
    <!-- Cloudflare Web Analytics -->
    <script defer src='https://static.cloudflareinsights.com/beacon.min.js' data-cf-beacon='{"token": "4edfd97585aa43b3b3f063e82176e9fb"}'></script>
    <!-- End Cloudflare Web Analytics -->
<body>
    <main class="auth-layout">
        <div class="auth-card">
            <header class="auth-header">
                <button type="button" class="auth-back-btn" aria-label="Go back" onclick="history.back()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </button>
                <h1 class="auth-title"><?= trans('Register'); ?></h1>
            </header>

            <?php if (!empty($error)): ?>
                <span class="form-error"><?= htmlspecialchars($error) ?></span>
            <?php endif; ?>

            <form action="" method="post" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" id="firstName" name="firstName" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" id="lastName" name="lastName" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="phoneNumber" class="form-label">Phone number</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" class="form-input" placeholder="e.g. +27712345689" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-input password-input" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword" class="form-label">Confirm password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-input password-input" required>
                        <button type="button" class="password-toggle" aria-label="Toggle confirm password visibility">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <p class="text-micro auth-terms">
                    By creating an account, you agree to ReTrade's <a href="/../Terms-of-service.php">Terms of Service</a> and <a href="/../privacy-policy.php">Privacy Policy</a>.
                </p>

                <button type="submit" class="btn btn-full">Register</button>
            </form>
        </div>
    </main>

    <script src="../../assets/js/global.js"></script>
</body>
</html>