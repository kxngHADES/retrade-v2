<?php

require_once __DIR__ . '/../config/bootstrap.php';

if (empty($_SESSION['uid'])) {
	header('Location: /');
    exit();
}