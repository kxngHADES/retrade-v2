<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';

// Additional check: Only superAdmins can register new admins
if (!is_super_admin()) {
    die("Access Denied: You do not have permission to register new admins.");
}

use Lib\services\Auth_flow;

$error = "";
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth_flow();
    $result = $auth->register($_POST);

    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin - ReTrade</title>
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/forms.css">
</head>
<body class="auth-layout">
    <div class="auth-card">
        <div class="auth-header">
            <h1 class="auth-title">Create Admin</h1>
            <p class="auth-subtitle">Add a new administrative user to the platform.</p>
        </div>

        <form action="" method="post" class="auth-form">
            <?php if ($error): ?>
                <div style="background: var(--error-dim); color: var(--error); padding: var(--space-3); border-radius: 8px; border: 1px solid var(--error-border); margin-bottom: var(--space-4); font-size: var(--font-body-small-size); text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div style="background: rgba(46, 125, 50, 0.12); color: var(--success); padding: var(--space-3); border-radius: 8px; border: 1px solid rgba(46, 125, 50, 0.3); margin-bottom: var(--space-4); font-size: var(--font-body-small-size); text-align: center;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" name="firstName" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lastName" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="admin@retrade.com" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="form-label">Access Level</label>
                <select name="role" class="form-input">
                    <option value="2">Admin</option>
                    <option value="3">Super Admin</option>
                </select>
            </div>

            <button type="submit" class="auth-btn">Register Admin</button>
        </form>

        <div class="auth-footer">
            <p><a href="/dashboard" style="color: var(--text-secondary); font-size: var(--font-body-small-size);">← Back to System Dashboard</a></p>
        </div>
    </div>
</body>
</html>