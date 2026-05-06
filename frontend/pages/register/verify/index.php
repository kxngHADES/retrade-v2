<?php

require __DIR__ . '/../../../config/bootstrap.php';

use Lib\services\Auth_flow;

$phoneNumber = $_SESSION['phoneNumber'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
	$auth = new Auth_flow();
	if (isset($_POST['resend'])) {
		$result = $auth->resend_registration_otp($phoneNumber);
		if ($result === true) {
			$error = "A new OTP has been sent.";
		} else {
			$error = $result;
		}
	} else if (isset($_POST['verify'])) {
		$otp = $_POST['otp'];
		$result = $auth->finish_registration($phoneNumber, $otp);
		$error = $result['error'] ?? "An error occurred";
	}
}

?>
<!DOCTYPE html>
<html lang=<?= $_SESSION['lang'] ?? 'en'; ?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Verify OTP - ReTrade</title> <!--Lang verify-->
    <link rel="stylesheet" href="../../../assets/css/variables.css">
    <link rel="stylesheet" href="../../../assets/css/global.css">
    <link rel="stylesheet" href="../../../assets/css/forms.css">
</head>
<body>
    <main class="auth-layout">
        <div class="auth-card">
            <div class="auth-center-header">
                <div class="auth-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h1 class="auth-title">Verify your phone</h1>
                <p class="text-body auth-subtitle">We sent a 6-digit code to <?= htmlspecialchars($phoneNumber ?? '') ?></p>
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
                    <button type="submit" name="verify" class="btn btn-full">Verify</button>
                    
                    <button type="submit" name="resend" formnovalidate class="btn-link">Resend code</button>
                    <span class="text-micro text-muted">Resend in 45s</span>
                </div>
            </form>
        </div>
    </main>

    <script src="../../../assets/js/global.js"></script>
    <script>
        // Simple script to auto-focus next OTP input and sync to hidden input for backend
        const otpInputs = document.querySelectorAll('.otp-input');
        const hiddenOtp = document.getElementById('hiddenOtp');

        function updateHiddenOtp() {
            let val = '';
            otpInputs.forEach(input => {
                val += input.value;
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