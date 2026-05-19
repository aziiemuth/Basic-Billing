<?php
/**
 * CRON JOB: Pengingat Jatuh Tempo WhatsApp Otomatis
 * ====================================================
 * Jalankan script ini dengan cron job setiap hari:
 *
 *   0 8 * * * php /path/to/billing/cron/wa_reminders.php >> /path/to/billing/cron/logs/reminders.log 2>&1
 */

// ---- Bootstrap ----
define('APPROOT', dirname(__DIR__));
require_once APPROOT . '/app/config/config.php';
require_once APPROOT . '/app/libraries/Database.php';
require_once APPROOT . '/app/libraries/WhatsappService.php';
require_once APPROOT . '/app/models/SettingsModel.php';

// Ensure log directory exists
$logDir = APPROOT . '/cron/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/reminders_' . date('Y-m') . '.log';

function cronLog($message, $logFile) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}

cronLog('===== WhatsApp Reminders Cron Dimulai =====', $logFile);

$settingsModel = new SettingsModel();
$settings = $settingsModel->getSettings();

if (!$settings) {
    cronLog('ERROR: Gagal memuat pengaturan sistem.', $logFile);
    exit;
}

$daysThreshold = (int)$settings->wa_reminder_days;
cronLog("Threshold Pengingat: H-{$daysThreshold} Hari sebelum jatuh tempo.", $logFile);

if ($daysThreshold <= 0) {
    cronLog('INFO: Pengingat dinonaktifkan (hari = 0). Cron selesai.', $logFile);
    exit;
}

$db = new Database();
// Cari invoice unpaid yang jatuh tempo tepat X hari lagi
$query = "SELECT i.*, c.name as customer_name, c.whatsapp 
          FROM invoices i 
          JOIN customers c ON i.customer_id = c.id 
          WHERE i.status = 'unpaid' 
          AND c.status = 'active'
          AND DATEDIFF(i.due_date, CURDATE()) = :days";

$db->query($query);
$db->bind(':days', $daysThreshold);
$invoices = $db->resultSet();

cronLog('Ditemukan ' . count($invoices) . ' tagihan belum lunas untuk diingatkan.', $logFile);

$sent_count = 0;
$failed_count = 0;

foreach ($invoices as $inv) {
    if (empty($inv->whatsapp)) {
        cronLog("  SKIP ['{$inv->customer_name}'] - Tidak ada nomor WhatsApp.", $logFile);
        continue;
    }

    $ok = WhatsappService::sendPaymentReminder(
        $inv->customer_id, 
        $inv->whatsapp, 
        $inv->customer_name, 
        $inv->total_amount, 
        $inv->due_date
    );

    if ($ok) {
        cronLog("  OK   ['{$inv->customer_name}'] - Pengingat tagihan #{$inv->invoice_number} terkirim.", $logFile);
        $sent_count++;
    } else {
        cronLog("  FAIL ['{$inv->customer_name}'] - Gagal mengirim pengingat tagihan #{$inv->invoice_number}.", $logFile);
        $failed_count++;
    }
}

cronLog('===== Ringkasan =====', $logFile);
cronLog("Berhasil dikirim : {$sent_count}", $logFile);
cronLog("Gagal            : {$failed_count}", $logFile);
cronLog('===== Cron Selesai =====', $logFile);
