<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../lib/services/report_service.php';

use Lib\services\Report_service;

if (!isset($_SESSION['uid'])) {
    header("Location: /pages/login/");
    exit;
}

$reportService = new Report_service();
$message = "";
$error = "";

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $description = $_POST['description'] ?? '';
    $paymentRef = $_POST['payment_reference'] ?? null;

    if (empty($orderId) || empty($reason) || empty($description)) {
        $error = "Please fill in all required fields.";
    } else {
        $success = $reportService->log_dispute($_SESSION['uid'], $orderId, $reason, $description, $paymentRef);
        if ($success) {
            $message = "Dispute logged successfully. Our team will review it shortly.";
        } else {
            $error = "Failed to log dispute. Please try again later.";
        }
    }
}

$myDisputes = $reportService->get_user_disputes($_SESSION['uid']);
?>

<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disputes - ReTrade</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
</head>
<body class="theme-<?= $_SESSION['theme'] ?? 'light' ?>">
    <main class="auth-layout">
        <div class="auth-container">
            <header class="auth-header">
                <h1 class="auth-title">Log a Dispute</h1>
                <p class="auth-subtitle">Something went wrong with your order? Let us know.</p>
            </header>

            <?php if ($message): ?>
                <div class="form-success" style="color: var(--success); text-align: center; margin-bottom: 20px;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="form-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="auth-form">
                <div class="form-group">
                    <label class="form-label">Order ID / Reference</label>
                    <input type="text" name="order_id" class="form-input" placeholder="Enter Order ID" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Reference (Optional)</label>
                    <input type="text" name="payment_reference" class="form-input" placeholder="e.g. TRN-123456">
                </div>

                <div class="form-group">
                    <label class="form-label">Reason for Dispute</label>
                    <select name="reason" class="form-input" required>
                        <option value="">Select a reason...</option>
                        <option value="item_not_received">Item Not Received</option>
                        <option value="item_not_as_described">Item Not as Described</option>
                        <option value="falsified_delivery">Falsified Delivery Confirmation</option>
                        <option value="fraudulent_activity">Fraudulent Activity</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Detailed Description</label>
                    <textarea name="description" class="form-input" style="height: 100px; padding: 10px;" placeholder="Explain what happened..." required></textarea>
                </div>

                <button type="submit" class="auth-btn">Submit Dispute</button>
            </form>

            <hr style="margin: 30px 0; border: 0; border-top: 1px solid var(--border);">

            <section class="dispute-history">
                <h2 style="font-size: var(--font-h2-size); margin-bottom: 20px;">My Disputes</h2>
                <?php if (empty($myDisputes)): ?>
                    <p class="text-muted">You haven't logged any disputes yet.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach ($myDisputes as $dispute): ?>
                            <div class="profile-card" style="padding: 15px; border: 1px solid var(--border); border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div>
                                        <strong><?= htmlspecialchars($dispute['dispute_reason']) ?></strong>
                                        <p class="auth-subtitle" style="margin: 5px 0;">Order: <?= htmlspecialchars($dispute['order_id']) ?></p>
                                    </div>
                                    <span class="status-pill <?= $dispute['status'] === 'open' ? 'status-pill--warning' : 'status-pill--success' ?>" style="font-size: 10px; padding: 2px 8px; border-radius: 10px; border: 1px solid;">
                                        <?= strtoupper($dispute['status']) ?>
                                    </span>
                                </div>
                                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 10px;">
                                    <?= htmlspecialchars($dispute['description']) ?>
                                </p>
                                <small class="text-muted"><?= $dispute['created_at'] ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <footer class="auth-footer" style="margin-top: 40px;">
                <a href="/" class="btn-link">← Back to Home</a>
            </footer>
        </div>
    </main>
</body>
</html>