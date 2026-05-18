<?php
/**
 * CRON JOB: Sinkronisasi Harian PPPoE Status
 * ===========================================
 * Jalankan setiap hari untuk mensinkronisasi status pelanggan
 * antara database dan MikroTik.
 *
 * Contoh jadwal cron (setiap hari jam 06:00):
 *   0 6 * * * php /path/to/billing-main/cron/sync_pppoe.php >> /path/to/billing-main/cron/logs/sync.log 2>&1
 *
 * Script ini akan:
 * 1. Ambil semua pelanggan aktif dari database
 * 2. Cek status PPPoE secret di MikroTik (enabled/disabled)
 * 3. Sinkronisasi status di database sesuai kondisi MikroTik
 * 4. Pastikan profile PPPoE ada di MikroTik sesuai paket
 */

// ---- Bootstrap ----
define('APPROOT', dirname(__DIR__));
require_once APPROOT . '/app/config/config.php';
require_once APPROOT . '/app/libraries/Database.php';
require_once APPROOT . '/app/libraries/RouterosAPI.php';
require_once APPROOT . '/app/libraries/MikrotikService.php';
require_once APPROOT . '/app/models/CustomerModel.php';
require_once APPROOT . '/app/models/MikrotikRouterModel.php';
require_once APPROOT . '/app/models/PackageModel.php';

// Ensure log directory exists
$logDir = APPROOT . '/cron/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/sync_' . date('Y-m') . '.log';

function cronLog($msg, $file) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents($file, $line, FILE_APPEND);
    echo $line;
}

cronLog('===== Sync PPPoE Cron Dimulai =====', $logFile);

$routerModel   = new MikrotikRouterModel();
$customerModel = new CustomerModel();
$packageModel  = new PackageModel();
$db            = new Database();

// Ambil semua router aktif
$routers = $routerModel->getAll();
cronLog('Ditemukan ' . count($routers) . ' router.', $logFile);

foreach ($routers as $router) {
    if (!$router->is_active) {
        cronLog('SKIP Router [' . $router->name . '] — nonaktif.', $logFile);
        continue;
    }

    cronLog('>> Proses Router: ' . $router->name . ' (' . $router->host_ip . ')', $logFile);

    $mikrotikService = new MikrotikService();
    if (!$mikrotikService->connect($router->id)) {
        cronLog('   ERROR: Gagal koneksi ke router.', $logFile);
        continue;
    }

    // Sinkronisasi status
    $mtStatus = $mikrotikService->syncAllStatus();
    cronLog('   Ditemukan ' . count($mtStatus) . ' PPPoE secret di MikroTik.', $logFile);

    // ---- Pastikan Profile Paket Ada ----
    $packages = $packageModel->getAll();
    foreach ($packages as $pkg) {
        if (!empty($pkg->mikrotik_profile)) {
            $rateLimit = ($pkg->speed_upload > 0 && $pkg->speed_download > 0)
                ? $pkg->speed_upload . 'M/' . $pkg->speed_download . 'M'
                : '';
            $created = $mikrotikService->ensureProfileExists($pkg->mikrotik_profile, $pkg->speed_download, $pkg->speed_upload);
            if ($created) {
                cronLog('   Profile [' . $pkg->mikrotik_profile . '] sudah ada/dibuat.', $logFile);
            }
        }
    }

    // ---- Sinkronisasi status pelanggan di DB ----
    $customers = $customerModel->getCustomersWithPppoe($router->id);
    foreach ($customers as $c) {
        if (empty($c->pppoe_username)) continue;

        $username = $c->pppoe_username;
        if (!isset($mtStatus[$username])) {
            cronLog('   INFO: PPPoE [' . $username . '] tidak ditemukan di MikroTik — mungkin belum dibuat.', $logFile);
            continue;
        }

        $mt = $mtStatus[$username];

        // Sinkronisasi status pppoe_secrets
        $dbPppoeStatus = $mt['disabled'] ? 'disabled' : 'enabled';
        $db->query('UPDATE pppoe_secrets SET status = :s WHERE username = :u');
        $db->bind(':s', $dbPppoeStatus);
        $db->bind(':u', $username);
        $db->execute();

        // Sinkronisasi status customers jika customer masih active tapi PPPoE disabled
        if ($mt['disabled'] && $c->status === 'active') {
            $customerModel->updateStatus($c->id, 'isolated');
            cronLog('   SYNC: [' . $c->name . '] PPPoE disabled di MT → status DB diubah ke isolated.', $logFile);
        } elseif (!$mt['disabled'] && $c->status === 'isolated') {
            $customerModel->updateStatus($c->id, 'active');
            cronLog('   SYNC: [' . $c->name . '] PPPoE enabled di MT → status DB diubah ke active.', $logFile);
        }
    }

    $mikrotikService->disconnect();
    cronLog('   Selesai memproses router ' . $router->name . '.', $logFile);
}

cronLog('===== Sync PPPoE Cron Selesai =====', $logFile);
