<?php
/**
 * CRON JOB: Auto-Isolate Pelanggan Yang Menunggak
 * ================================================
 * Jalankan script ini dengan cron job setiap hari:
 *
 *   0 1 * * * php /path/to/billing-main/cron/auto_isolate.php >> /path/to/billing-main/cron/logs/isolate.log 2>&1
 *
 * Script ini akan:
 * 1. Query semua pelanggan dengan tagihan overdue (belum bayar & sudah lewat due date)
 * 2. Disable akun PPPoE di MikroTik
 * 3. Update status customer menjadi 'isolated' di database
 * 4. Update status pppoe_secrets menjadi 'disabled' di database
 * 5. Log semua aksi
 */

// ---- Bootstrap ----
define('APPROOT', dirname(__DIR__));
require_once APPROOT . '/app/config/config.php';
require_once APPROOT . '/app/libraries/Database.php';
require_once APPROOT . '/app/libraries/RouterosAPI.php';
require_once APPROOT . '/app/libraries/MikrotikService.php';
require_once APPROOT . '/app/models/CustomerModel.php';
require_once APPROOT . '/app/models/MikrotikRouterModel.php';

// Ensure log directory exists
$logDir = APPROOT . '/cron/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = $logDir . '/isolate_' . date('Y-m') . '.log';

function cronLog($message, $logFile) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}

cronLog('===== Auto-Isolate Cron Dimulai =====', $logFile);

// ---- Cek Konfigurasi Isolir Otomatis ----
$db = new Database();
$db->query("SELECT auto_isolate FROM settings WHERE id = 1");
$settings = $db->single();
if (!$settings || !$settings->auto_isolate) {
    cronLog('INFO: Fitur Isolir Otomatis dinonaktifkan di Pengaturan Sistem. Cron dibatalkan.', $logFile);
    exit;
}

// ---- Query Overdue Customers ----
$customerModel = new CustomerModel();
$overdueList   = $customerModel->getOverdueCustomers();

cronLog('Ditemukan ' . count($overdueList) . ' pelanggan menunggak.', $logFile);

if (empty($overdueList)) {
    cronLog('Tidak ada tindakan diperlukan. Cron selesai.', $logFile);
    exit;
}

// ---- Kelompokkan berdasarkan router agar koneksi efisien ----
$db = new Database();
$byRouter = [];

foreach ($overdueList as $customer) {
    $routerId = $customer->pppoe_router_id ?? null;
    if (!$routerId || empty($customer->pppoe_username)) {
        cronLog('SKIP [' . $customer->name . '] - Tidak ada data PPPoE/router.', $logFile);
        continue;
    }
    $byRouter[$routerId][] = $customer;
}

// Jika tidak ada router di DB tapi MIKROTIK_ENABLED di config, gunakan config langsung
if (empty($byRouter) && defined('MIKROTIK_ENABLED') && MIKROTIK_ENABLED) {
    cronLog('Tidak ada router di database. Mencoba koneksi langsung dari config.php...', $logFile);
    $mikrotikService = new MikrotikService();
    if ($mikrotikService->connectFromConfig()) {
        cronLog('Terhubung via config.php (' . MIKROTIK_HOST . ':' . MIKROTIK_PORT . ')', $logFile);
        foreach ($overdueList as $customer) {
            if (empty($customer->pppoe_username)) continue;
            $ok = $mikrotikService->disablePppoeSecret($customer->pppoe_username);
            if ($ok) {
                $customerModel->updateStatus($customer->id, 'isolated');
                $db->query('UPDATE pppoe_secrets SET status = :s WHERE username = :u');
                $db->bind(':s', 'disabled'); $db->bind(':u', $customer->pppoe_username); $db->execute();
                cronLog('  OK   [' . $customer->name . '] PPPoE: ' . $customer->pppoe_username . ' -> DISABLED', $logFile);
                $totalSuccess++;
            } else {
                cronLog('  FAIL [' . $customer->name . '] - ' . $mikrotikService->getLastError(), $logFile);
                $totalFailed++;
            }
        }
        $mikrotikService->disconnect();
    } else {
        cronLog('ERROR: ' . $mikrotikService->getLastError(), $logFile);
    }
}

$totalSuccess = 0;
$totalFailed  = 0;
$totalSkipped = 0;

// ---- Proses per router ----
foreach ($byRouter as $routerId => $customers) {
    $mikrotikService = new MikrotikService();
    $connected       = $mikrotikService->connect($routerId);

    if (!$connected) {
        cronLog('ERROR: Gagal koneksi ke Router ID=' . $routerId . '. Skip ' . count($customers) . ' pelanggan.', $logFile);
        foreach ($customers as $c) {
            cronLog('  FAILED [' . $c->name . '] - Router tidak bisa dijangkau.', $logFile);
            $totalFailed++;
        }
        continue;
    }

    cronLog('Terhubung ke Router ID=' . $routerId . '. Memproses ' . count($customers) . ' pelanggan...', $logFile);

    foreach ($customers as $customer) {
        // Disable di MikroTik
        $ok = $mikrotikService->disablePppoeSecret($customer->pppoe_username);

        if ($ok) {
            // Update status di DB
            $customerModel->updateStatus($customer->id, 'isolated');

            $db->query('UPDATE pppoe_secrets SET status = :status WHERE username = :username');
            $db->bind(':status', 'disabled');
            $db->bind(':username', $customer->pppoe_username);
            $db->execute();

            // Log ke customer_logs
            $db->query('INSERT INTO customer_logs (customer_id, action, description) VALUES (:cid, :action, :desc)');
            $db->bind(':cid', $customer->id);
            $db->bind(':action', 'auto_isolate');
            $db->bind(':desc', 'Akun PPPoE ' . $customer->pppoe_username . ' dinonaktifkan otomatis. Invoice: ' . ($customer->invoice_number ?? '-') . ' overdue sejak ' . ($customer->invoice_due_date ?? '-'));
            $db->execute();

            cronLog('  OK   [' . $customer->name . '] PPPoE: ' . $customer->pppoe_username . ' -> DISABLED', $logFile);
            $totalSuccess++;
        } else {
            cronLog('  FAIL [' . $customer->name . '] PPPoE: ' . $customer->pppoe_username . ' -> Gagal disable: ' . $mikrotikService->getLastError(), $logFile);
            $totalFailed++;
        }
    }

    $mikrotikService->disconnect();
}

cronLog('===== Ringkasan =====', $logFile);
cronLog('Berhasil  : ' . $totalSuccess, $logFile);
cronLog('Gagal     : ' . $totalFailed, $logFile);
cronLog('Dilewati  : ' . $totalSkipped, $logFile);
cronLog('===== Cron Selesai =====', $logFile);
