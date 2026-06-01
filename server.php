<?php

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Serve static files directly from public directory
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri) && !is_dir(__DIR__ . '/public' . $uri)) {
    $filePath = __DIR__ . '/public' . $uri;
    
    // Set appropriate content type headers
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'webp' => 'image/webp'
    ];
    
    if (isset($mimes[$ext])) {
        header("Content-Type: " . $mimes[$ext]);
    } else if (function_exists('mime_content_type')) {
        header("Content-Type: " . mime_content_type($filePath));
    }
    
    readfile($filePath);
    exit;
}

// Route all requests through public/index.php
$_GET['url'] = ltrim($uri, '/');

// Change working directory to public so relative paths work
chdir(__DIR__ . '/public');
require_once __DIR__ . '/public/index.php';
