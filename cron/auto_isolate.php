<?php
/**
 * CRON JOB: Auto-Isolate Pelanggan Yang Menunggak
 * ================================================
 * Jalankan script ini dengan cron job setiap hari:
 *
 *   0 1 * * * php /path/to/billingv1/cron/auto_isolate.php >> /path/to/billingv1/cron/logs/isolate.log 2>&1
 *
 * Script ini akan:
 * 1. Query semua pelanggan dengan tagihan overdue (belum bayar & sudah lewat due date)
 * 2. Disable akun PPPoE di MikroTik
 * 3. Update status customer menjadi 'isolated' di database
 * 4. Update status pppoe_secrets menjadi 'disabled' di database
 * 5. Kirim notifikasi WhatsApp ke pelanggan yang baru diisolir
 * 6. Log semua aksi ke file log bulanan
 */

// ---- Bootstrap ----
define('APPROOT', dirname(__DIR__));
require_once APPROOT . '/app/config/config.php';
require_once APPROOT . '/app/libraries/Database.php';
require_once APPROOT . '/app/libraries/RouterosAPI.php';
require_once APPROOT . '/app/libraries/MikrotikService.php';
require_once APPROOT . '/app/libraries/WhatsappService.php';
require_once APPROOT . '/app/models/CustomerModel.php';
require_once APPROOT . '/app/models/MikrotikRouterModel.php';

// ---- Setup log ----
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

// ---- Cek konfigurasi Isolir Otomatis di Pengaturan Sistem ----
$db = new Database();
$db->query("SELECT auto_isolate FROM settings WHERE id = 1");
$settings = $db->single();

if (!$settings || !$settings->auto_isolate) {
    cronLog('INFO: Fitur Isolir Otomatis dinonaktifkan di Pengaturan Sistem. Cron dibatalkan.', $logFile);
    exit;
}

// ---- Query pelanggan menunggak ----
$customerModel = new CustomerModel();
$overdueList   = $customerModel->getOverdueCustomers();

cronLog('Ditemukan ' . count($overdueList) . ' pelanggan menunggak (termasuk yang sudah terisolir sebelumnya).', $logFile);

if (empty($overdueList)) {
    cronLog('Tidak ada tindakan diperlukan. Cron selesai.', $logFile);
    exit;
}

// ---- Inisialisasi counter (HARUS sebelum digunakan) ----
$totalSuccess = 0;
$totalFailed  = 0;
$totalSkipped = 0;

// ---- Kelompokkan per router, lewati yang sudah terisolir ----
$byRouter = [];
foreach ($overdueList as $customer) {
    // Lewati pelanggan yang sudah berstatus isolated agar tidak kirim WA duplikat
    if ($customer->status === 'isolated') {
        cronLog('SKIP [' . $customer->name . '] - Sudah terisolir sebelumnya.', $logFile);
        $totalSkipped++;
        continue;
    }

    $routerId = $customer->pppoe_router_id ?? null;
    if (!$routerId || empty($customer->pppoe_username)) {
        cronLog('SKIP [' . $customer->name . '] - Tidak ada data PPPoE/router.', $logFile);
        $totalSkipped++;
        continue;
    }

    $byRouter[$routerId][] = $customer;
}

// ---- Fallback: jika tidak ada router di DB, coba koneksi via config.php ----
if (empty($byRouter) && defined('MIKROTIK_ENABLED') && MIKROTIK_ENABLED) {
    // Filter: hanya yang belum isolated dan punya username PPPoE
    $pendingCustomers = array_values(array_filter($overdueList, function ($c) {
        return $c->status !== 'isolated' && !empty($c->pppoe_username);
    }));

    if (!empty($pendingCustomers)) {
        cronLog('Tidak ada router di database. Mencoba koneksi langsung dari config.php...', $logFile);

        $mikrotikService = new MikrotikService();
        if ($mikrotikService->connectFromConfig()) {
            cronLog('Terhubung via config.php (' . MIKROTIK_HOST . ':' . MIKROTIK_PORT . ')', $logFile);

            foreach ($pendingCustomers as $customer) {
                $ok = $mikrotikService->disablePppoeSecret($customer->pppoe_username);

                if ($ok) {
                    // Update status di database
                    $customerModel->updateStatus($customer->id, 'isolated');

                    $db->query('UPDATE pppoe_secrets SET status = :s WHERE username = :u');
                    $db->bind(':s', 'disabled');
                    $db->bind(':u', $customer->pppoe_username);
                    $db->execute();

                    // Kirim notifikasi WhatsApp ke pelanggan yang baru diisolir
                    if (!empty($customer->whatsapp)) {
                        WhatsappService::sendIsolated($customer->id, $customer->whatsapp, $customer->name);
                    }

                    cronLog('  OK   [' . $customer->name . '] PPPoE: ' . $customer->pppoe_username . ' -> DISABLED', $logFile);
                    $totalSuccess++;
                } else {
                    cronLog('  FAIL [' . $customer->name . '] - ' . $mikrotikService->getLastError(), $logFile);
                    $totalFailed++;
                }
            }

            $mikrotikService->disconnect();
        } else {
            cronLog('ERROR: Gagal koneksi via config.php — ' . $mikrotikService->getLastError(), $logFile);
        }
    }
}

// ---- Proses per router (dari database) ----
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
        // Disable PPPoE di MikroTik
        $ok = $mikrotikService->disablePppoeSecret($customer->pppoe_username);

        if ($ok) {
            // Update status customer di DB
            $customerModel->updateStatus($customer->id, 'isolated');

            // Update status PPPoE secret di DB
            $db->query('UPDATE pppoe_secrets SET status = :status WHERE username = :username');
            $db->bind(':status', 'disabled');
            $db->bind(':username', $customer->pppoe_username);
            $db->execute();

            // Log tindakan ke customer_logs
            $db->query('INSERT INTO customer_logs (customer_id, action, description) VALUES (:cid, :action, :desc)');
            $db->bind(':cid',    $customer->id);
            $db->bind(':action', 'auto_isolate');
            $db->bind(':desc',   'Akun PPPoE ' . $customer->pppoe_username . ' dinonaktifkan otomatis. Invoice: ' . ($customer->invoice_number ?? '-') . ' overdue sejak ' . ($customer->invoice_due_date ?? '-'));
            $db->execute();

            // Kirim notifikasi WhatsApp ke pelanggan yang baru diisolir
            if (!empty($customer->whatsapp)) {
                WhatsappService::sendIsolated($customer->id, $customer->whatsapp, $customer->name);
            }

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
