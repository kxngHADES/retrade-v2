<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';
require_once __DIR__ . '/../../lib/services/finance_service.php';

use Lib\services\finance_service;

$finance = new finance_service();
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['escrow_id'] ?? '';
    $action = $_POST['action'] ?? '';

    if ($finance->updateEscrowStatus($id, $action)) {
        $message = "Escrow " . htmlspecialchars($action) . " successfully.";
    } else {
        $error = "Failed to update escrow.";
    }
}

$records = $finance->getAllEscrowRecords();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escrow Control - Admin Panel</title>
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/escrow.css">
</head>
<body>
    <?php require_once __DIR__ . '/../../templates/navbar.php'; ?>

    <div class="escrow-container">
        <div class="page-header">
            <h1 class="page-title">Escrow Management</h1>
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

        <div class="escrow-table-wrapper">
            <table class="escrow-table">
                <thead>
                    <tr>
                        <th>Escrow ID</th>
                        <th>User Account</th>
                        <th>Amount (ZAR)</th>
                        <th>Status</th>
                        <th>Held Since</th>
                        <th>Management Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(empty($records)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 40px;">No escrow records found.</td></tr>
                <?php else: ?>
                    <?php foreach($records as $row): ?>
                        <tr>
                            <td><span class="id-badge"><?= $row['escrow_id'] ?></span></td>
                            <td>
                                <div class="user-info">
                                    <span class="user-name"><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></span>
                                    <span class="user-email"><?= htmlspecialchars($row['email']) ?></span>
                                </div>
                            </td>
                            <td><span class="amount-text">R<?= number_format($row['amount'], 2) ?></span></td>
                            <td>
                                <span class="status-badge <?= strtolower($row['status']) ?>">
                                    <?= strtoupper($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 13px; color: var(--text-secondary);">
                                    <?= date('M j, Y', strtotime($row['escrow_date'])) ?>
                                </div>
                            </td>
                            <td>
                                <?php if($row['status'] === 'held'): ?>
                                    <div class="action-buttons">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="escrow_id" value="<?= $row['escrow_id'] ?>">
                                            <button type="submit" name="action" value="released" class="btn-release" onclick="return confirm('Release funds to seller?')">Release</button>
                                            <button type="submit" name="action" value="refunded" class="btn-refund" onclick="return confirm('Refund funds to buyer?')">Refund</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="payout-date">Completed on: <?= date('M j, Y', strtotime($row['paid_out_date'])) ?></span>
                                <?php endif; ?>
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