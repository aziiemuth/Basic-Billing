<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Serve static files directly
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Route all requests through public/index.php
$_GET['url'] = ltrim($uri, '/');

// Change working directory to public so relative paths work
chdir(__DIR__ . '/public');
require_once __DIR__ . '/public/index.php';
