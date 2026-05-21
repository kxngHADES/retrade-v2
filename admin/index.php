<?php

require_once __DIR__ . '/config/bootstrap.php';
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ReTrade</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/forms.css">
</head>
<body class="auth-layout">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">ReTrade Admin</h1>
            <p class="auth-subtitle">Welcome back! Please enter your details.</p>
        </div>

        <form action="" method="post" class="auth-form">
            <?php if ($error): ?>
                <div style="background: var(--error-dim); color: var(--error); padding: var(--space-3); border-radius: 8px; border: 1px solid var(--error-border); margin-bottom: var(--space-4); font-size: var(--font-body-small-size); text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="admin@retrade.com" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="auth-btn">Sign In</button>
        </form>

        <div class="auth-footer">
            <p class="auth-slogan">Secure and reliable trade management.</p>
        </div>
    </div>
</body>
</html>