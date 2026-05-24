<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';
require_once __DIR__ . '/../../lib/services/finance_service.php';

use Lib\services\finance_service;

$finance = new finance_service();
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['dispute_id'] ?? '';
    $action = $_POST['action'] ?? ''; // 'investigating', 'payment_reversed', 'rejected'
    $notes = $_POST['notes'] ?? '';

    if ($finance->resolveDispute($id, $action, $notes)) {
        $message = "Dispute updated to " . htmlspecialchars($action) . ".";
    } else {
        $error = "Failed to update dispute.";
    }
}

$disputes = $finance->getAllDisputes();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispute Management - Admin Panel</title>
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/disputes.css">
</head>
<body>
    <?php require_once __DIR__ . '/../../templates/navbar.php'; ?>

    <div class="disputes-container">
        <div class="page-header">
            <h1 class="page-title">Payment Disputes</h1>
            <a href="../../index.php" class="link-secondary">← Back to Dashboard</a>
        </div>
        
        <?php if($message): ?>
            <div class="alert alert-success" style="background: rgba(46, 125, 50, 0.1); color: var(--success); padding: var(--space-3); border-radius: 4px; margin-bottom: var(--space-4); border: 1px solid var(--success);">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-error" style="background: var(--error-dim); color: var(--error); padding: var(--space-3); border-radius: 4px; margin-bottom: var(--space-4); border: 1px solid var(--error);">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="dispute-table-wrapper">
            <table class="dispute-table">
                <thead>
                    <tr>
                        <th>Reporter</th>
                        <th>Issue Details</th>
                        <th>Status</th>
                        <th>Logged Date</th>
                        <th style="width: 250px;">Resolution Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($disputes)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 40px;">No disputes logged.</td></tr>
                <?php else: ?>
                    <?php foreach($disputes as $row): ?>
                        <tr>
                            <td>
                                <div class="reporter-info">
                                    <span class="reporter-name"><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></span>
                                    <span class="reporter-email"><?= htmlspecialchars($row['email']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="dispute-reason"><?= htmlspecialchars($row['dispute_reason']) ?></span>
                                <span class="dispute-desc"><?= htmlspecialchars($row['description']) ?></span>
                            </td>
                            <td>
                                <?php 
                                    $statusClass = strtolower($row['status']);
                                    if ($statusClass === 'payment_reversed') $statusClass = 'resolved';
                                ?>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= strtoupper($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 13px; color: var(--text-secondary);">
                                    <?= date('M j, Y', strtotime($row['created_at'])) ?><br>
                                    <small><?= date('H:i', strtotime($row['created_at'])) ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="action-card">
                                    <form method="POST">
                                        <input type="hidden" name="dispute_id" value="<?= $row['dispute_id'] ?>">
                                        <textarea name="notes" class="action-textarea" placeholder="Internal resolution notes..." required></textarea>
                                        <select name="action" class="action-select" required>
                                            <option value="">Choose Resolution...</option>
                                            <option value="investigating" <?= $row['status'] === 'investigating' ? 'selected' : '' ?>>Mark Investigating</option>
                                            <option value="payment_reversed">Reverse Payment (Refund)</option>
                                            <option value="rejected">Reject Dispute</option>
                                        </select>
                                        <button type="submit" class="btn-update">Update Dispute</button>
                                    </form>
                                    <a href="../escrow-control/index.php" class="link-secondary">→ View Escrow Details</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>