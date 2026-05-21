<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/roles.php';

if (!is_admin()) {
    // Redirect to home page if not an admin (matches frontend behavior)
    header('Location: /');
    exit();
}
