<?php


define('APP_ROOT', __DIR__);
session_start();

if (!define('BASE_URL')){
	$script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    
    $current_dir = dirname(__FILE__);
    $current_dir = str_replace('\\', '/', $current_dir);
    
    $base_path = str_replace($doc_root, '', $current_dir);
    
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    
    define('BASE_URL', $protocol . "://" . $host . rtrim($base_path, '/') . '/');
}