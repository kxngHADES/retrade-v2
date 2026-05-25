<?php

require_once __DIR__ . '/../../../config/bootstrap.php';

use Lib\services\profile_services;

$profile_service = new profile_services();
$error = "";
$email = $_SESSION['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $otp = $_POST['otp'] ?? '';
    $result = $profile_service->validate_email_otp($email, $otp, $_SESSION['uid']);
    if (is_string($result) && $result !== '') {
        $error = $result;
    }
}

?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(trans('Verify email')) ?> - ReTrade</title>
    <link rel="stylesheet" href="../../../assets/css/variables.css">
    <link rel="stylesheet" href="../../../assets/css/global.css">
    <link rel="stylesheet" href="../../../assets/css/forms.css">
</head>
<body class="auth-layout">
    <main class="auth-card">
        <div class="auth-center-header">
            <div class="auth-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="7" width="18" height="14" rx="2" ry="2"></rect>
                    <path d="M3 7l9 6 9-6"></path>
                </svg>
            </div>
            <h1 class="auth-title"><?= htmlspecialchars(trans('Verify your email')) ?></h1>
            <p class="text-body auth-subtitle"><?= htmlspecialchars(trans('We sent a 6-digit code to')) ?> <?= htmlspecialchars($email) ?></p>
        </div>

        <?php if (!empty($error)): ?>
            <span class="form-error"><?= htmlspecialchars($error) ?></span>
        <?php endif; ?>

        <form action="" method="post" novalidate>
            <div class="otp-group">
                <input type="number" class="otp-input" inputmode="numeric" maxlength="1" placeholder="·" required>
                <input type="number" class="otp-input" inputmode="numeric" maxlength="1" placeholder="·" required>
                <input type="number" class="otp-input" inputmode="numeric" maxlength="1" placeholder="·" required>
                <input type="number" class="otp-input" inputmode="numeric" maxlength="1" placeholder="·" required>
                <input type="number" class="otp-input" inputmode="numeric" maxlength="1" placeholder="·" required>
                <input type="number" class="otp-input" inputmode="numeric" maxlength="1" placeholder="·" required>
            </div>

            <input type="hidden" name="otp" id="hiddenOtp">

            <div class="auth-actions">
                <button type="submit" class="btn btn-full"><?= htmlspecialchars(trans('Verify')) ?></button>
            </div>
        </form>
    </main>

    <script>
        const form = document.querySelector('form');
        const submitBtn = document.querySelector('button[type="submit"]');
        const otpInputs = document.querySelectorAll('.otp-input');
        const hiddenOtp = document.getElementById('hiddenOtp');

        form.addEventListener('submit', function() {
            setTimeout(() => {
                submitBtn.disabled = true;
            }, 0);
        });

        function updateHiddenOtp() {
            let val = '';
            otpInputs.forEach(input => {
                val += input.value.trim();
            });
            hiddenOtp.value = val;
        }

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length > 1) {
                    this.value = this.value.slice(0, 1);
                }
                updateHiddenOtp();
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
                setTimeout(updateHiddenOtp, 10);
            });
        });
    </script>
</body>
</html>