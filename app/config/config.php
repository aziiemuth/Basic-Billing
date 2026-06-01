<?php
// ============================================================
// Load Environment Variables from .env file
// ============================================================
$envFilePath = dirname(dirname(__DIR__)) . '/.env';
if (file_exists($envFilePath)) {
    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and empty lines
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        
        // Parse name and value
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove surrounding quotes if any
            if (preg_match('/^"([^"]*)"$/', $value, $matches) || preg_match('/^\'([^\']*)\'$/', $value, $matches)) {
                $value = $matches[1];
            }
            
            // Set environment variable
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// ============================================================
// Env Helper Function
// ============================================================
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        
        // Convert string representation of boolean / null to actual type
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        return $value;
    }
}

// ============================================================
// Database Configuration
// ============================================================
define('DB_HOST',     env('DB_HOST', ''));
define('DB_PORT',     env('DB_PORT', ''));
define('DB_USERNAME', env('DB_USERNAME', ''));
define('DB_PASSWORD', env('DB_PASSWORD', ''));
define('DB_DATABASE', env('DB_DATABASE', ''));

// ============================================================
// Midtrans Payment Gateway Configuration
// ============================================================
define('MIDTRANS_SERVER_KEY',    env('MIDTRANS_SERVER_KEY', ''));
define('MIDTRANS_CLIENT_KEY',    env('MIDTRANS_CLIENT_KEY', ''));
define('MIDTRANS_IS_PRODUCTION', env('MIDTRANS_IS_PRODUCTION', false));

// ============================================================
// WhatsApp Gateway (Fonnte) Configuration
// ============================================================
define('WA_GATEWAY', env('WA_GATEWAY', 'fonnte'));
define('WA_TOKEN',   env('WA_TOKEN', ''));
define('WA_ENABLED', env('WA_ENABLED', false));

// ============================================================
// MikroTik RouterOS API — Konfigurasi Router Utama
// ============================================================
define('MIKROTIK_HOST',      env('MIKROTIK_HOST', ''));
define('MIKROTIK_USERNAME',  env('MIKROTIK_USERNAME', ''));
define('MIKROTIK_PASSWORD',  env('MIKROTIK_PASSWORD', ''));
define('MIKROTIK_PORT',      env('MIKROTIK_PORT', ''));
define('MIKROTIK_INTERFACE', env('MIKROTIK_INTERFACE', ''));
define('MIKROTIK_PROFILE',   env('MIKROTIK_PROFILE', ''));
define('MIKROTIK_TIMEOUT',   env('MIKROTIK_TIMEOUT', 3));
define('MIKROTIK_ENABLED',   env('MIKROTIK_ENABLED', false));


// ============================================================
// Application Path Configuration
// ============================================================
// App Root (directory of BILLING project root)
define('APPROOT', dirname(dirname(dirname(__FILE__))));

// URL Root (Dibuat dinamis agar bisa diakses via localhost maupun IP lokal seperti 192.168.x.x di HP)
$envUrl = env('URLROOT', '');
if (empty($envUrl)) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    
    // Deteksi subfolder secara dinamis dari URL akses (misal: /billing atau /billingv1)
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    $subfolder = '';
    if (!empty($scriptName)) {
        $dir = str_replace('\\', '/', dirname($scriptName));
        $subfolder = ($dir === '/' || $dir === '\\') ? '' : $dir;
    } else {
        $subfolder = '/billingv1'; // Default fallback jika diakses via CLI
    }
    
    $envUrl = $scheme . '://' . $host . $subfolder;
}
define('URLROOT', $envUrl);

// Site Name
define('SITENAME', env('SITENAME', 'Billing App'));

// ============================================================
// Cron Job Security
// ============================================================
define('CRON_SECRET', env('CRON_SECRET', 'GantiDenganSecretKuatAcak2026!'));
