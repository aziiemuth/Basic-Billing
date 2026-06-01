<?php
// PHP Built-in Server Router
// Mengemulasi fungsi .htaccess mod_rewrite untuk folder /public

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// =============================================================
// Serve static files dari folder public/
// Ini penting agar CSS, JS, gambar, dsb. bisa diakses via /assets/...
// =============================================================
$publicPath = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($publicPath) && !is_dir($publicPath)) {
    // Tentukan MIME type secara manual agar file statis diserve dengan benar
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'json'  => 'application/json',
        'webp'  => 'image/webp',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    readfile($publicPath);
    exit;
}

// =============================================================
// Semua request lainnya diarahkan ke public/index.php (dynamic routing)
// =============================================================
$url = ltrim($uri, '/');
if (!empty($url)) {
    $_GET['url'] = $url;
    $_SERVER['QUERY_STRING'] = 'url=' . $url;
}

require_once __DIR__ . '/public/index.php';
