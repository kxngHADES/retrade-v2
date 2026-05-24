<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';
require_once __DIR__ . '/../../lib/services/users_interaction.php';

use Lib\services\users_interaction;

$userInteraction = new users_interaction();

// Handle Filters
$filter = $_GET['filter'] ?? 'all'; // 'all', 'reported', 'fraud'

$users = [];
$title = "User Management";

if ($filter === 'reported') {
    $users = $userInteraction->getReportedUsers();
    $title = "Reported Users";
} elseif ($filter === 'fraud') {
    $users = $userInteraction->getFraudSuspects();
    $title = "Fraud Suspects";
} else {
    $users = $userInteraction->getAllUsers();
    $title = "All Registered Users";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Admin Panel</title>
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/user-management.css">
</head>
<body>
    <?php require_once __DIR__ . '/../../templates/navbar.php'; ?>

    <div class="user-management-container">
        <div class="page-header">
            <h1 class="page-title"><?= $title ?></h1>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success" style="background: rgba(46, 125, 50, 0.1); color: var(--success); padding: var(--space-3); border-radius: 4px; margin-bottom: var(--space-4); border: 1px solid var(--success);">
                <?= $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error" style="background: var(--error-dim); color: var(--error); padding: var(--space-3); border-radius: 4px; margin-bottom: var(--space-4); border: 1px solid var(--error);">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="filter-card">
            <form method="GET" action="" class="filter-group">
                <span class="filter-label">Filter View:</span>
                <select name="filter" class="ban-select" onchange="this.form.submit()">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Users</option>
                    <option value="reported" <?= $filter === 'reported' ? 'selected' : '' ?>>Reported Users</option>
                    <option value="fraud" <?= $filter === 'fraud' ? 'selected' : '' ?>>Fraud Related Reports</option>
                </select>
            </form>
        </div>

        <?php if (empty($users)): ?>
            <div class="empty-state">
                <p>No users found for this category.</p>
            </div>
        <?php else: ?>
            <div class="user-table-wrapper">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User Details</th>
                            <?php if ($filter === 'all'): ?>
                                <th>Phone</th>
                                <th>Joined</th>
                            <?php endif; ?>
                            <?php if ($filter === 'reported'): ?>
                                <th>Reports</th>
                            <?php endif; ?>
                            <?php if ($filter === 'fraud'): ?>
                                <th>Reason</th>
                                <th>Description</th>
                                <th>Reported Date</th>
                            <?php endif; ?>
                            <th>Status</th>
                            <th>Quick Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <span class="user-name"><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></span>
                                        <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                                    </div>
                                </td>
                                
                                <?php if ($filter === 'all'): ?>
                                    <td><?= htmlspecialchars($user['phoneNumber'] ?? 'N/A') ?></td>
                                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <?php endif; ?>

                                <?php if ($filter === 'reported'): ?>
                                    <td><span class="report-count"><?= $user['report_count'] ?></span></td>
                                <?php endif; ?>

                                <?php if ($filter === 'fraud'): ?>
                                    <td><?= htmlspecialchars($user['reason']) ?></td>
                                    <td style="max-width: 250px; font-size: 13px;"><?= htmlspecialchars($user['description']) ?></td>
                                    <td><?= date('M j, H:i', strtotime($user['reported_at'])) ?></td>
                                <?php endif; ?>

                                <td>
                                    <?php if ($user['is_banned']): ?>
                                        <div class="status-badge banned">BANNED</div>
                                        <?php if (!empty($user['ban_expires_at'])): ?>
                                            <div style="font-size: 10px; margin-top: 4px;">Until: <?= date('Y-m-d', strtotime($user['ban_expires_at'])) ?></div>
                                        <?php else: ?>
                                            <div style="font-size: 10px; margin-top: 4px;">(Permanent)</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="status-badge active">Active</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" action="ban_handler.php" class="ban-form">
                                        <input type="hidden" name="user_id" value="<?= $user['uid'] ?>">
                                        <select name="reason" class="ban-select" required style="max-width: 150px;">
                                            <option value="">Reason...</option>
                                            <option value="scamming">Scamming (Perma)</option>
                                            <option value="fraud">Fraud (Perma)</option>
                                            <option value="harassment">Harassment (30d)</option>
                                            <option value="spamming">Spamming (7d)</option>
                                            <option value="minor_infraction">Minor (1d)</option>
                                        </select>
                                        <button type="submit" class="btn-ban" onclick="return confirm('Are you sure you want to ban this user?')">Ban</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
