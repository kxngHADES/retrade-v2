<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function trans($key) {
    static $translations = [];

    if (empty($translations)) {
        $lang = $_SESSION['lang'] ?? 'en';


        $file_path = __DIR__ . '/lang/' . $lang . '.php';

        if (!file_exists($file_path)) {
            $file_path = __DIR__ . '/lang/en.php';
        }

        $translations = include $file_path;
    }

    return $translations[$key] ?? $key;
}