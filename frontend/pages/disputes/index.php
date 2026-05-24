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
    $orderRef = $_POST['order_reference'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($orderRef) || empty($reason) || empty($description)) {
        $error = "Please fill in all required fields.";
    } else {
        // Resolve Order ID from Reference
        $orderId = $reportService->find_order_id_by_payment_reference($orderRef);
        
        if (!$orderId) {
            $error = "Invalid Order Reference. Please check your payment confirmation.";
        } else {
            $success = $reportService->log_dispute($_SESSION['uid'], $orderId, $reason, $description, null);
            if ($success) {
                $message = "Dispute logged successfully. Our team will review it shortly.";
            } else {
                $error = "Failed to log dispute. Please try again later.";
            }
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
    <script>
        (function() {
            var t = localStorage.getItem('theme') || '<?= $_SESSION['theme'] ?? 'light' ?>';
            if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
            else document.documentElement.removeAttribute('data-theme');
        })();
    </script>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <link rel="stylesheet" href="/assets/css/disputes.css">
</head>
<body>
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
                    <label class="form-label">Order Reference</label>
                    <input type="text" name="order_reference" class="form-input" placeholder="e.g. PAY-ORD-..." required>
                    <p class="text-micro" style="margin-top: 4px; color: var(--text-secondary);">Found in your payment confirmation email.</p>
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
                    <textarea name="description" class="form-input content-editor" placeholder="Explain what happened..." required></textarea>
                </div>

                <button type="submit" class="auth-btn">Submit Dispute</button>
            </form>

            <hr class="dispute-divider">

            <section class="dispute-history">
                <h2 class="h2">My Disputes</h2>
                <?php if (empty($myDisputes)): ?>
                    <p class="text-muted">You haven't logged any disputes yet.</p>
                <?php else: ?>
                    <div class="dispute-list">
                        <?php foreach ($myDisputes as $dispute): ?>
                            <div class="dispute-item">
                                <div class="dispute-item-header">
                                    <div>
                                        <h3 class="dispute-item-title"><?= htmlspecialchars($dispute['dispute_reason'] === 'other' ? 'Other Issue' : ucwords(str_replace('_', ' ', $dispute['dispute_reason']))) ?></h3>
                                        <p class="dispute-item-id">Order ID: <?= htmlspecialchars($dispute['order_id']) ?></p>
                                    </div>
                                    <span class="status-pill <?= $dispute['status'] === 'open' ? 'status-pill--warning' : 'status-pill--success' ?>">
                                        <?= strtoupper($dispute['status']) ?>
                                    </span>
                                </div>
                                <p class="dispute-item-desc">
                                    <?= htmlspecialchars($dispute['description']) ?>
                                </p>
                                <div class="dispute-item-date"><?= date('M j, Y • H:i', strtotime($dispute['created_at'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <footer class="dispute-back-footer">
                <a href="/" class="btn-link">← Back to Home</a>
            </footer>
        </div>
    </main>
</body>
</html>