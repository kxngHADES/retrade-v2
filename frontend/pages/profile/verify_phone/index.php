<?php

require __DIR__ . '/../../../config/bootstrap.php';

use Lib\services\profile_services;

$profile_service = new profile_services();

if ($_SERVER['REQUEST_METHOD'] === "POST"){
	$otp = $_POST['otp'];
	$profile_service->verify_phone_number($_SESSION['uid'], $otp, $_SESSION['phoneNumber']);
}

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(trans('Verify phone')) ?> - ReTrade</title>
    <link rel="stylesheet" href="../../../assets/css/variables.css">
    <link rel="stylesheet" href="../../../assets/css/global.css">
    <link rel="stylesheet" href="../../../assets/css/forms.css">
</head>
<body class="auth-layout">
    <main class="auth-card">
        <div class="auth-center-header">
            <div class="auth-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92V19a2 2 0 0 1-2 2 19.86 19.86 0 0 1-8.63-2.16 19.5 19.5 0 0 1-6-5.11 19.86 19.86 0 0 1-2.16-8.63A2 2 0 0 1 5 3h2.08a2 2 0 0 1 2 1.72 13 13 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 10.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 13 13 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
            </div>
            <h1 class="auth-title"><?= htmlspecialchars(trans('Verify your phone')) ?></h1>
            <p class="text-body auth-subtitle"><?= htmlspecialchars(trans('We sent a 6-digit code')) ?></p>
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