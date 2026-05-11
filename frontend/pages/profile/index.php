<?php

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';

use Lib\services\profile_services;

$profile_service = new profile_services();

$error = "";

$firstName = $_SESSION['firstName'] ?? null;
$lastName = $_SESSION['lastName'] ?? null;
$email = $_SESSION['email'] ?? null;
$phoneNumber = $_SESSION['phoneNumber'] ?? null;
$avatar = $_SESSION['profile_image_url'] ?? null;
$fullName = trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: trans('Profile');

$email_verification = $profile_service->is_email_verified($_SESSION['uid']) ? trans('Verified') : trans('Unverified');
$phone_verification = $profile_service->is_phone_verified($_SESSION['uid']) ? trans('Verified') : trans('Unverified');
$id_verified = $profile_service->is_id_verified($_SESSION['uid']) ? trans('Verified') : trans('Unverified');

if ($id_verified === trans('Unverified')) {
    $id_verified = $profile_service->is_id_pending($_SESSION['uid']) ? trans('Pending') : trans('Unverified');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_info'])) {
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        try {
            $profile_service->change_user_info($firstName, $lastName, $_SESSION['uid']);
        } catch (Exception $e) {
            $error = trans('Failed to change First/Last name');
        }
    }

    if (isset($_POST['phone_form'])) {
        $phoneNumber = $_POST['phoneNumber'];
        try {
            $profile_service->change_phone_number($phoneNumber, $_SESSION['uid']);
        } catch (Exception $e) {
            $error = trans('Failed to send OTP');
        }
    }

    if (isset($_POST['profile_image_form']) && !empty($_POST['profile_image_url'])) {
        $profileImageUrl = $_POST['profile_image_url'];
        try {
            $profile_service->change_profile_image($profileImageUrl, $_SESSION['uid']);
        } catch (Exception $e) {
            $error = trans('Failed to update profile picture');
        }
    }

    if (isset($_POST['email_form'])) {
        $email = $_POST['email'];
        try {
            $profile_service->send_verification_email($email);
        } catch (Exception $e) {
            $error = trans('Failed to send email');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(trans('Profile')) ?> - ReTrade</title>
    <script>
        (function() {
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
        })();
    </script>
    <script>
        window.USER_UID = <?= json_encode($_SESSION['uid']); ?>;
    </script>
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/forms.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
</head>
<body class="antialiased min-h-screen flex" style="background-color: var(--bg);">
    <?php include __DIR__ . '/../../templates/partial/navbar.php'; ?>

    <div id="main-content" class="main-content min-h-screen relative overflow-hidden transition-all duration-300">
        <main class="profile-main w-full h-full overflow-y-auto pt-[40px] pb-[64px] md:pt-0 md:pb-0 flex-grow">
            <section class="profile-shell">
                <div class="profile-head">
                    <div class="profile-overview">
                        <button type="button" id="profileAvatar" class="profile-avatar profile-avatar--interactive" aria-label="View profile image">
                            <?php if ($avatar): ?>
                                <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($fullName) ?>">
                            <?php else: ?>
                                <span><?= htmlspecialchars(strtoupper(substr($firstName ?? '', 0, 1) . substr($lastName ?? '', 0, 1))) ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="profile-avatar-details">
                            <p class="profile-eyebrow text-micro text-muted"><?= htmlspecialchars(trans('Profile')) ?></p>
                            <h1 class="profile-title"><?= htmlspecialchars($fullName) ?></h1>
                            <p class="profile-meta text-secondary"><?= htmlspecialchars($email) ?></p>
                        </div>
                        <div class="profile-avatar-actions">
                            <button type="button" id="editAvatarBtn" class="btn btn-secondary btn-full profile-avatar-edit"><?= htmlspecialchars(trans('Edit')) ?></button>
                        </div>
                    </div>
                </div>

                <div id="avatarPreviewModal" class="avatar-modal hidden" aria-hidden="true">
                    <div class="avatar-modal-backdrop" id="closeAvatarModal"></div>
                    <div class="avatar-modal-card" role="dialog" aria-modal="true" aria-labelledby="avatarModalTitle">
                        <div class="avatar-modal-header">
                            <h2 id="avatarModalTitle" class="profile-card-header__title"><?= htmlspecialchars(trans('Profile picture')) ?></h2>
                            <button type="button" id="avatarModalClose" class="avatar-modal-close" aria-label="Close preview">×</button>
                        </div>
                        <div class="avatar-modal-body">
                            <div class="avatar-modal-image">
                                <?php if ($avatar): ?>
                                    <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($fullName) ?>">
                                <?php else: ?>
                                    <span><?= htmlspecialchars(strtoupper(substr($firstName ?? '', 0, 1) . substr($lastName ?? '', 0, 1))) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="profile-actions">
                            <button type="button" id="modalEditAvatarBtn" class="btn btn-primary btn-full"><?= htmlspecialchars(trans('Update profile picture')) ?></button>
                            <p class="text-xs text-secondary mt-2"><?= htmlspecialchars(trans('Max upload size: 5MB')) ?></p>
                        </div>
                    </div>
                </div>

                <form id="profile-image-form" action="" method="post" class="sr-only">
                    <input type="hidden" name="profile_image_form" value="1">
                    <input type="hidden" name="profile_image_url" id="profile_image_url" value="">
                    <input type="file" id="profileImage" accept="image/png,image/jpeg,image/webp,image/gif" name="file">
                </form>

                <?php if ($error): ?>
                    <div class="profile-error" role="alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <div id="uploadNotification" class="profile-upload-notification hidden" role="alert"></div>

                <div class="profile-grid">
                    <section class="profile-card">
                        <div class="profile-card-header">
                            <h2><?= htmlspecialchars(trans('Personal Info')) ?></h2>
                        </div>
                        <form class="profile-form" action="" method="post">
                            <div class="profile-field">
                                <label for="firstName"><?= htmlspecialchars(trans('First Name')) ?></label>
                                <input id="firstName" name="firstName" type="text" class="profile-input" value="<?= htmlspecialchars($firstName) ?>" data-original="<?= htmlspecialchars($firstName) ?>">
                            </div>
                            <div class="profile-field">
                                <label for="lastName"><?= htmlspecialchars(trans('Last Name')) ?></label>
                                <input id="lastName" name="lastName" type="text" class="profile-input" value="<?= htmlspecialchars($lastName) ?>" data-original="<?= htmlspecialchars($lastName) ?>">
                            </div>
                            <div class="profile-actions">
                                <button id="save-info-btn" type="submit" name="user_info" class="btn btn-primary btn-full" disabled><?= htmlspecialchars(trans('Save changes')) ?></button>
                            </div>
                        </form>
                    </section>

                    <section class="profile-card">
                        <div class="profile-card-header profile-card-header--with-status">
                            <h2><?= htmlspecialchars(trans('Phone Number')) ?></h2>
                            <span class="status-pill <?= $phone_verification === trans('Verified') ? 'status-pill--success' : 'status-pill--warning' ?>">
                                <?= htmlspecialchars($phone_verification) ?>
                            </span>
                        </div>
                        <form class="profile-form" action="" method="post">
                            <div class="profile-field">
                                <label for="phoneNumber"><?= htmlspecialchars(trans('Mobile Number')) ?></label>
                                <input id="phoneNumber" name="phoneNumber" type="tel" class="profile-input" value="<?= htmlspecialchars($phoneNumber) ?>" data-original="<?= htmlspecialchars($phoneNumber) ?>">
                            </div>
                            <div class="profile-actions">
                                <button id="save-phone-btn" type="submit" name="phone_form" class="btn btn-secondary btn-full" disabled><?= htmlspecialchars(trans('Save Change')) ?></button>
                            </div>
                        </form>
                    </section>

                    <section class="profile-card">
                        <div class="profile-card-header profile-card-header--with-status">
                            <h2><?= htmlspecialchars(trans('Email Address')) ?></h2>
                            <span class="status-pill <?= $email_verification === trans('Verified') ? 'status-pill--success' : 'status-pill--warning' ?>">
                                <?= htmlspecialchars($email_verification) ?>
                            </span>
                        </div>
                        <form class="profile-form" action="" method="post">
                            <div class="profile-field">
                                <label for="email"><?= htmlspecialchars(trans('Primary Email')) ?></label>
                                <input id="email" name="email" type="email" class="profile-input" value="<?= htmlspecialchars($email) ?>" data-original="<?= htmlspecialchars($email) ?>">
                            </div>
                            <div class="profile-actions profile-actions--wrap">
                                <?php if ($email_verification !== trans('Verified')): ?>
                                    <button id="verify-email-btn" type="submit" name="email_form" class="btn btn-primary btn-full" data-verified="false"><?= htmlspecialchars(trans('Verify email')) ?></button>
                                <?php endif; ?>
                                <button id="update-email-btn" type="submit" name="email_form" class="btn btn-ghost btn-full" disabled><?= htmlspecialchars(trans('Update Email')) ?></button>
                            </div>
                        </form>
                    </section>

                    <section class="profile-card profile-card--notice">
                        <div class="profile-card-header">
                            <h2><?= htmlspecialchars(trans('ID Verification')) ?></h2>
                        </div>
                        <div class="profile-card-note">
                            <p><?= htmlspecialchars(trans('Verify your identity to unlock all features and build trust with other users.')) ?></p>
                            <div class="profile-status-row">
                                <span class="status-pill <?= $id_verified === trans('Verified') ? 'status-pill--success' : 'status-pill--warning' ?>">
                                    <?= htmlspecialchars($id_verified) ?>
                                </span>
                                <?php if ($id_verified === trans('Unverified')): ?>
                                    <a href="/pages/profile/verify_id/" class="btn btn-primary btn-full profile-verify-btn"><?= htmlspecialchars(trans('Verify ID Now')) ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        </main>
    </div>
    <script src="/assets/js/profile.js" defer></script>
</body>
</html>
